<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserGenerate;
use App\Http\Requests\Admin\UserSendMail;
use App\Http\Requests\Admin\UserUpdate;
use App\Jobs\SendEmailJob;
use App\Models\Plan;
use App\Models\User;
use App\Services\AuthService;
use App\Services\NodeSyncService;
use App\Services\Plugin\HookManager;
use App\Services\UserService;
use App\Traits\QueryOperators;
use App\Utils\Helper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    use QueryOperators;

    public function resetSecret(Request $request)
    {
        $user = User::find($request->input('id'));
        if (!$user)
            return $this->fail([400202, '用户不存在']);
        $user->token = Helper::guid();
        $user->uuid = Helper::guid(true);
        $result = $user->save();

        if ($result) {
            HookManager::call('admin.user.secret.reset', [
                'user' => $user,
                'request' => $request,
            ]);
        }

        return $this->success($result);
    }

    /**
     * 编辑弹窗里改某条订阅的流量 / 到期时间。
     * 只动这两个字段，不碰邮箱、套餐、权限组，避免误改整个账号。
     */
    public function updateSubscription(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'transfer_enable' => 'nullable|numeric|min:0',
            'expired_at' => 'nullable|integer',
        ]);

        $user = User::find((int) $request->input('id'));
        if (!$user) {
            return $this->fail([400202, '订阅不存在']);
        }

        $payload = $request->all();
        // 流量按 GB 传，入库换成字节；0 是合法值，不能用 has() 判断。
        if (array_key_exists('transfer_enable', $payload) && $payload['transfer_enable'] !== null) {
            $user->transfer_enable = (int) round(((float) $payload['transfer_enable']) * 1073741824);
        }
        if (array_key_exists('expired_at', $payload)) {
            $value = $payload['expired_at'];
            $user->expired_at = ($value === null || $value === '') ? null : (int) $value;
        }

        $user->save();

        return $this->success([
            'id' => $user->id,
            'transfer_enable' => $user->transfer_enable,
            'expired_at' => $user->expired_at,
            'u' => $user->u,
            'd' => $user->d,
        ]);
    }

    /**
     * 把名下某条订阅设为「当前订阅」。
     *
     * 列表里的订阅 / 已用流量 / 总流量 / 到期时间都读当前订阅，
     * 所以「切换订阅」只是把父账号上的指针改掉，返回后下一次拉列表
     * 自然就会显示这一条的数据。
     */
    public function switchSubscription(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ], [
            'id.required' => '缺少订阅ID',
            'id.integer' => '订阅ID格式不正确',
        ]);

        $target = User::find((int) $request->input('id'));
        if (!$target) {
            return $this->fail([400202, '订阅不存在']);
        }

        $ownerId = (int) ($target->owner_user_id ?: $target->id);
        $owner = $ownerId === (int) $target->id ? $target : User::find($ownerId);
        if (!$owner) {
            return $this->fail([400202, '订阅所属账号不存在']);
        }
        if ($owner->owner_user_id) {
            return $this->fail([400403, '订阅记录不能作为账号使用']);
        }

        $expiredAt = (int) $target->expired_at;
        if ($expiredAt !== 0 && $expiredAt <= time()) {
            return $this->fail([400422, '这条订阅已过期，不能设为当前订阅']);
        }

        $owner->current_subscription_id = (int) $target->id;
        $owner->save();

        return $this->success([
            'id' => (int) $target->id,
            'current_subscription_id' => (int) $owner->current_subscription_id,
        ]);
    }

    /** 删除一条名下订阅（主账号不允许在这里删，防止把用户本身删掉）。 */
    public function destroySubscription(Request $request)
    {
        $user = User::find((int) $request->input('id'));
        if (!$user) {
            return $this->fail([400202, '订阅不存在']);
        }
        if (!$user->owner_user_id) {
            return $this->fail([400403, '这是主账号，不能从订阅面板删除']);
        }

        // 删掉的正好是「当前订阅」，指针要一起清掉，
        // 否则列表会去读一条已经不存在的订阅。
        $owner = User::find((int) $user->owner_user_id);
        if ($owner && (int) $owner->current_subscription_id === (int) $user->id) {
            $owner->current_subscription_id = null;
            $owner->save();
        }

        // 历史订单还引用着这条订阅，先解除引用再删，免得留下悬空 id。
        DB::table('v2_order')
            ->where('subscription_user_id', $user->id)
            ->update(['subscription_user_id' => null]);

        $user->delete();

        return $this->success(true);
    }

    // Apply filters and sorts to the query builder.
    private function applyFiltersAndSorts(Request $request, Builder|QueryBuilder $builder): void
    {
        $this->applyFilters($request, $builder);
        $this->applySorting($request, $builder);
    }

    // Apply filters to the query builder.
    private function applyFilters(Request $request, Builder|QueryBuilder $builder): void
    {
        if (!$request->has('filter')) {
            return;
        }

        collect($request->input('filter'))->each(function ($filter) use ($builder) {
            $field = $filter['id'];
            $value = $filter['value'];
            $logic = strtolower($filter['logic'] ?? 'and');

            if ($logic === 'or') {
                $builder->orWhere(function ($query) use ($field, $value) {
                    $this->buildFilterQuery($query, $field, $value);
                });
            } else {
                $builder->where(function ($query) use ($field, $value) {
                    $this->buildFilterQuery($query, $field, $value);
                });
            }
        });
    }

    // Build one filter query condition.
    private function buildFilterQuery(Builder|QueryBuilder $query, string $field, mixed $value): void
    {
        // 处理关联查询
        if (str_contains($field, '.')) {
            if (!method_exists($query, 'whereHas')) {
                return;
            }
            [$relation, $relationField] = explode('.', $field);
            $query->whereHas($relation, function ($q) use ($relationField, $value) {
                if (is_array($value)) {
                    $values = $relationField === 'email'
                        ? array_map(static fn ($value) => User::normalizeEmail((string) $value), $value)
                        : $value;
                    $q->whereIn($relationField, $values);
                } elseif (is_string($value) && str_contains($value, ':')) {
                    [$operator, $filterValue] = explode(':', $value, 2);
                    if ($relationField === 'email') {
                        $filterValue = User::normalizeEmail($filterValue);
                    }
                    $this->applyQueryCondition($q, $relationField, $operator, $filterValue);
                } else {
                    $filterValue = $relationField === 'email' ? User::normalizeEmail((string) $value) : $value;
                    $q->where($relationField, 'like', "%{$filterValue}%");
                }
            });
            return;
        }

        // 处理数组值的 'in' 操作
        if (is_array($value)) {
            $values = $field === 'email'
                ? array_map(static fn ($value) => User::normalizeEmail((string) $value), $value)
                : $value;
            $query->whereIn($field === 'group_ids' ? 'group_id' : $field, $values);
            return;
        }

        // 处理基于运算符的过滤
        if (!is_string($value) || !str_contains($value, ':')) {
            $filterValue = $field === 'email' ? User::normalizeEmail((string) $value) : $value;
            $query->where($field, 'like', "%{$filterValue}%");
            return;
        }

        [$operator, $filterValue] = explode(':', $value, 2);
        if ($field === 'email') {
            $filterValue = User::normalizeEmail($filterValue);
        }

        // 转换数字字符串为适当的类型
        if (is_numeric($filterValue)) {
            $filterValue = strpos($filterValue, '.') !== false
                ? (float) $filterValue
                : (int) $filterValue;
        }

        // 处理计算字段
        $queryField = match ($field) {
            'total_used' => DB::raw('(u + d)'),
            default => $field
        };

        $this->applyQueryCondition($query, $queryField, $operator, $filterValue);
    }

    // Apply sorting rules to the query builder.
    private function applySorting(Request $request, Builder|QueryBuilder $builder): void
    {
        if (!$request->has('sort')) {
            return;
        }

        collect($request->input('sort'))->each(function ($sort) use ($builder) {
            $field = $sort['id'];
            $direction = $sort['desc'] ? 'DESC' : 'ASC';
            $builder->orderBy($field, $direction);
        });
    }

    // Resolve bulk operation scope and normalize user_ids.
    private function resolveScope(Request $request): array
    {
        $scope = $request->input('scope');
        $userIds = $request->input('user_ids');

        $hasSelection = is_array($userIds) && count(array_filter($userIds, static fn($v) => is_numeric($v))) > 0;
        $hasFilter = $request->has('filter') && !empty($request->input('filter'));

        if (!in_array($scope, ['selected', 'filtered', 'all'], true)) {
            if ($hasSelection) {
                $scope = 'selected';
            } elseif ($hasFilter) {
                $scope = 'filtered';
            } else {
                $scope = 'all';
            }
        }

        $normalizedIds = [];
        if ($scope === 'selected') {
            $normalizedIds = is_array($userIds) ? $userIds : [];
            $normalizedIds = array_values(array_unique(array_map(static function ($v) {
                return is_numeric($v) ? (int) $v : null;
            }, $normalizedIds)));
            $normalizedIds = array_values(array_filter($normalizedIds, static fn($v) => is_int($v)));
        }

        return [
            'scope' => $scope,
            'user_ids' => $normalizedIds,
        ];
    }

    // Fetch paginated user list (filters + sorting).
    public function fetch(Request $request)
    {
        $current = $request->input('current', 1);
        $pageSize = $request->input('pageSize', 10);

        $userModel = User::query()
            ->with(['plan:id,name', 'invite_user:id,email', 'group:id,name', 'subscriptions.plan:id,name'])
            ->withCount('subscriptions')
            ->whereNull('owner_user_id')
            ->select((new User())->getTable() . '.*')
            ->selectRaw('(u + d) as total_used');

        $userModel = HookManager::filter('admin.user.fetch.query', $userModel, $request);

        $this->applyFiltersAndSorts($request, $userModel);

        $users = $userModel->orderBy('id', 'desc')
            ->paginate($pageSize, ['*'], 'page', $current);

        $users->getCollection()->transform(function ($user): array {
            return self::transformUserData($user);
        });

        return $this->paginate($users);
    }

    // Transform user fields for API response.
    public static function transformUserData(User $user): array
    {
        $model = $user;
        $user = $user->toArray();
        $user['balance'] = $user['balance'] / 100;
        $user['commission_balance'] = $user['commission_balance'] / 100;
        $user['subscribe_url'] = Helper::getSubscribeUrl($user['token']);
        $profiles = self::subscriptionProfiles($model);
        // 过期订阅已经不再返回，计数也按有效的算，列表和选择框才对得上。
        $user['subscription_count'] = count($profiles);
        unset($user['subscriptions'], $user['subscriptions_count']);
        $user['subscription_profiles'] = $profiles;
        return HookManager::filter('admin.user.transform', $user, $model);
    }

    /**
     * 订阅档案（含备注号 seq）。
     *
     * seq 只给仍然有效的订阅编号（1、2、3…），和用户端「我的订阅」完全一致，
     * 名字相同的订阅也能一眼分清是哪一条；已过期的订阅不再返回，到期即从
     * 后台选择框和用户端一起消失。
     */
    private static function subscriptionProfiles(User $model): array
    {
        $all = collect($model->plan_id === null ? [] : [$model])
            ->concat($model->subscriptions->sortBy('id'))
            ->values();

        $now = time();
        $seq = 0;
        $profiles = [];
        foreach ($all as $profile) {
            $expiredAt = (int) $profile->expired_at;
            if ($expiredAt !== 0 && $expiredAt <= $now) {
                continue;
            }
            $seq += 1;
            $profiles[] = [
                'id' => $profile->id,
                'seq' => $seq,
                'name' => $profile->subscription_name ?: optional($profile->plan)->name,
                'plan' => $profile->plan,
                'subscribe_url' => Helper::getSubscribeUrl(trim($profile->token)),
                'expired_at' => $profile->expired_at,
                'u' => $profile->u,
                'd' => $profile->d,
                'transfer_enable' => $profile->transfer_enable,
                'is_current' => false,
            ];
        }
        return self::orderByCurrent($profiles, (int) $model->current_subscription_id);
    }

    /**
     * 把「当前订阅」排到最前面，并打上 is_current 标记。
     *
     * 后台列表行、编辑抽屉的订阅选择框都读第一条，切换订阅只改父账号上的
     * 指针即可；seq 在排序之前就按 id 定好了，所以切换不会让「订阅2」
     * 突然变成「订阅1」。指针指向的订阅被删或过期时，回落到第一条有效订阅。
     */
    private static function orderByCurrent(array $profiles, int $currentId): array
    {
        if (!$profiles) {
            return $profiles;
        }
        $picked = null;
        $rest = [];
        if ($currentId) {
            foreach ($profiles as $profile) {
                if ($picked === null && (int) $profile['id'] === $currentId) {
                    $picked = $profile;
                    continue;
                }
                $rest[] = $profile;
            }
        }
        if ($picked === null) {
            $picked = array_shift($profiles);
            $rest = $profiles;
        }
        $picked['is_current'] = true;
        array_unshift($rest, $picked);
        return $rest;
    }

    public function getUserInfoById(Request $request)
    {
        $request->validate([
            'id' => 'required|numeric'
        ], [
            'id.required' => '用户ID不能为空'
        ]);
        $user = User::whereNull('owner_user_id')->findOrFail($request->input('id'))
            ->load(['invite_user', 'plan', 'subscriptions.plan']);
        $user->setAttribute('subscription_profiles', self::subscriptionProfiles($user));
        $user = HookManager::filter('admin.user.detail', $user, $request);
        return $this->success($user);
    }

    public function setInviteUser(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|integer|exists:App\\Models\\User,id',
            'invite_user_id' => 'nullable|integer|different:id|exists:App\\Models\\User,id',
        ]);
        $parent = $data['invite_user_id'] ?? null;
        $visited = [(int) $data['id']];
        for ($cursor = $parent; $cursor; $cursor = User::find($cursor)?->invite_user_id) {
            if (in_array((int) $cursor, $visited, true)) {
                return $this->fail([422, '邀请关系不能形成循环']);
            }
            $visited[] = (int) $cursor;
        }
        User::findOrFail($data['id'])->update(['invite_user_id' => $parent]);
        return $this->success(true);
    }

    public function update(UserUpdate $request)
    {
        $params = $request->validated();

        $user = User::find($request->input('id'));
        if (!$user) {
            return $this->fail([400202, '用户不存在']);
        }
        if (isset($params['email'])) {
            $existingUser = User::byEmail($params['email'])->first();
            if ($existingUser && $existingUser->id !== $user->id) {
                return $this->fail([400201, '邮箱已被使用']);
            }
        }
        // 处理密码
        if (isset($params['password'])) {
            $params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);
            $params['password_algo'] = NULL;
        } else {
            unset($params['password']);
        }
        // 处理订阅计划
        if (isset($params['plan_id'])) {
            $plan = Plan::find($params['plan_id']);
            if (!$plan) {
                return $this->fail([400202, '订阅计划不存在']);
            }
            $params['group_id'] = $plan->group_id;
        }
        // 处理邀请用户
        if ($request->input('invite_user_email') && $inviteUser = User::byEmail($request->input('invite_user_email'))->first()) {
            $params['invite_user_id'] = $inviteUser->id;
        } else {
            $params['invite_user_id'] = null;
        }

        if (isset($params['banned']) && (int) $params['banned'] === 1) {
            $authService = new AuthService($user);
            $authService->removeAllSessions();
        }
        if (isset($params['balance'])) {
            $params['balance'] = $params['balance'] * 100;
        }
        if (isset($params['commission_balance'])) {
            $params['commission_balance'] = $params['commission_balance'] * 100;
        }

        $params = HookManager::filter('admin.user.update.params', $params, $request, $user);

        HookManager::call('admin.user.update.before', [
            'user' => $user,
            'params' => $params,
            'request' => $request,
        ]);

        try {
            $user->update($params);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail([500, '保存失败']);
        }

        HookManager::call('admin.user.update.after', [
            'user' => $user->refresh(),
            'params' => $params,
            'request' => $request,
        ]);

        return $this->success(true);
    }

    // Export users to CSV.
    public function dumpCSV(Request $request)
    {
        ini_set('memory_limit', '-1');
        gc_enable(); // 启用垃圾回收

        $scopeInfo = $this->resolveScope($request);
        $scope = $scopeInfo['scope'];
        $userIds = $scopeInfo['user_ids'];

        if ($scope === 'selected') {
            if (empty($userIds)) {
                return $this->fail([422, 'user_ids不能为空']);
            }
        }

        // 优化查询：使用with预加载plan关系，避免N+1问题
        $query = User::query()
            ->with('plan:id,name')
            ->orderBy('id', 'asc')
            ->select([
                'email',
                'balance',
                'commission_balance',
                'transfer_enable',
                'u',
                'd',
                'expired_at',
                'token',
                'plan_id'
            ]);

        if ($scope === 'selected') {
            $query->whereIn('id', $userIds);
        } elseif ($scope === 'filtered') {
            $this->applyFiltersAndSorts($request, $query);
        } // all: ignore filter/sort

        $filename = 'users_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            // 打开输出流
            $output = fopen('php://output', 'w');

            // 添加BOM标记，确保Excel正确显示中文
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // 写入CSV头部
            fputcsv($output, [
                '邮箱',
                '余额',
                '推广佣金',
                '总流量',
                '剩余流量',
                '套餐到期时间',
                '订阅计划',
                '订阅地址'
            ]);

            // 分批处理数据以减少内存使用
            $query->chunk(500, function ($users) use ($output) {
                foreach ($users as $user) {
                    try {
                        $row = [
                            $user->email,
                            number_format($user->balance / 100, 2),
                            number_format($user->commission_balance / 100, 2),
                            Helper::trafficConvert($user->transfer_enable),
                            Helper::trafficConvert($user->transfer_enable - ($user->u + $user->d)),
                            $user->expired_at ? date('Y-m-d H:i:s', $user->expired_at) : '长期有效',
                            $user->plan ? $user->plan->name : '无订阅',
                            Helper::getSubscribeUrl($user->token)
                        ];
                        fputcsv($output, $row);
                    } catch (\Exception $e) {
                        Log::error('CSV导出错误: ' . $e->getMessage(), [
                            'user_id' => $user->id,
                            'email' => $user->email
                        ]);
                        continue; // 继续处理下一条记录
                    }
                }

                // 清理内存
                gc_collect_cycles();
            });

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    public function generate(UserGenerate $request)
    {
        if ($request->input('email_prefix')) {
            // If generate_count is specified with email_prefix, generate multiple users with incremented emails
            if ($request->input('generate_count')) {
                return $this->multiGenerateWithPrefix($request);
            }
            
            // Single user generation with email_prefix
            $email = User::normalizeEmail($request->input('email_prefix') . '@' . $request->input('email_suffix'));

            if (User::byEmail($email)->exists()) {
                return $this->fail([400201, '邮箱已存在于系统中']);
            }

            $userService = app(UserService::class);
            $user = $userService->createUser([
                'email' => $email,
                'password' => $request->input('password') ?? $email,
                'plan_id' => $request->input('plan_id'),
                'expired_at' => $request->input('expired_at'),
            ]);

            if (!$user->save()) {
                return $this->fail([500, '生成失败']);
            }
            return $this->success(true);
        }

        if ($request->input('generate_count')) {
            return $this->multiGenerate($request);
        }
    }

    private function multiGenerate(Request $request)
    {
        $userService = app(UserService::class);
        $usersData = [];

        for ($i = 0; $i < $request->input('generate_count'); $i++) {
            $email = User::normalizeEmail(Helper::randomChar(6) . '@' . $request->input('email_suffix'));
            $usersData[] = [
                'email' => $email,
                'password' => $request->input('password') ?? $email,
                'plan_id' => $request->input('plan_id'),
                'expired_at' => $request->input('expired_at'),
            ];
        }



        try {
            DB::beginTransaction();
            $users = [];
            foreach ($usersData as $userData) {
                $user = $userService->createUser($userData);
                $user->save();
                $users[] = $user;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->fail([500, '生成失败']);
        }

        // 判断是否导出 CSV
        if ($request->input('download_csv')) {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="users.csv"',
            ];
            $callback = function () use ($users, $request) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['账号', '密码', '过期时间', 'UUID', '创建时间', '订阅地址']);
                foreach ($users as $user) {
                    $user = $user->refresh();
                    $expireDate = $user['expired_at'] === NULL ? '长期有效' : date('Y-m-d H:i:s', $user['expired_at']);
                    $createDate = date('Y-m-d H:i:s', $user['created_at']);
                    $password = $request->input('password') ?? $user['email'];
                    $subscribeUrl = Helper::getSubscribeUrl($user['token']);
                    fputcsv($handle, [$user['email'], $password, $expireDate, $user['uuid'], $createDate, $subscribeUrl]);
                }
                fclose($handle);
            };
            return response()->streamDownload($callback, 'users.csv', $headers);
        }

        // 默认返回 JSON
        $data = collect($users)->map(function ($user) use ($request) {
            return [
                'email' => $user['email'],
                'password' => $request->input('password') ?? $user['email'],
                'expired_at' => $user['expired_at'] === NULL ? '长期有效' : date('Y-m-d H:i:s', $user['expired_at']),
                'uuid' => $user['uuid'],
                'created_at' => date('Y-m-d H:i:s', $user['created_at']),
                'subscribe_url' => Helper::getSubscribeUrl($user['token']),
            ];
        });
        return response()->json([
            'code' => 0,
            'message' => '批量生成成功',
            'data' => $data,
        ]);
    }

    private function multiGenerateWithPrefix(Request $request)
    {
        $userService = app(UserService::class);
        $usersData = [];
        $emailPrefix = $request->input('email_prefix');
        $emailSuffix = $request->input('email_suffix');
        $generateCount = $request->input('generate_count');

        // Check if any of the emails with prefix already exist
        for ($i = 1; $i <= $generateCount; $i++) {
            $email = User::normalizeEmail($emailPrefix . '_' . $i . '@' . $emailSuffix);
            if (User::byEmail($email)->exists()) {
                return $this->fail([400201, '邮箱 ' . $email . ' 已存在于系统中']);
            }
        }

        // Generate user data for batch creation
        for ($i = 1; $i <= $generateCount; $i++) {
            $email = User::normalizeEmail($emailPrefix . '_' . $i . '@' . $emailSuffix);
            $usersData[] = [
                'email' => $email,
                'password' => $request->input('password') ?? $email,
                'plan_id' => $request->input('plan_id'),
                'expired_at' => $request->input('expired_at'),
            ];
        }

        try {
            DB::beginTransaction();
            $users = [];
            foreach ($usersData as $userData) {
                $user = $userService->createUser($userData);
                $user->save();
                $users[] = $user;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->fail([500, '生成失败']);
        }

        // 判断是否导出 CSV
        if ($request->input('download_csv')) {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="users.csv"',
            ];
            $callback = function () use ($users, $request) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['账号', '密码', '过期时间', 'UUID', '创建时间', '订阅地址']);
                foreach ($users as $user) {
                    $user = $user->refresh();
                    $expireDate = $user['expired_at'] === NULL ? '长期有效' : date('Y-m-d H:i:s', $user['expired_at']);
                    $createDate = date('Y-m-d H:i:s', $user['created_at']);
                    $password = $request->input('password') ?? $user['email'];
                    $subscribeUrl = Helper::getSubscribeUrl($user['token']);
                    fputcsv($handle, [$user['email'], $password, $expireDate, $user['uuid'], $createDate, $subscribeUrl]);
                }
                fclose($handle);
            };
            return response()->streamDownload($callback, 'users.csv', $headers);
        }

        // 默认返回 JSON
        $data = collect($users)->map(function ($user) use ($request) {
            return [
                'email' => $user['email'],
                'password' => $request->input('password') ?? $user['email'],
                'expired_at' => $user['expired_at'] === NULL ? '长期有效' : date('Y-m-d H:i:s', $user['expired_at']),
                'uuid' => $user['uuid'],
                'created_at' => date('Y-m-d H:i:s', $user['created_at']),
                'subscribe_url' => Helper::getSubscribeUrl($user['token']),
            ];
        });
        return response()->json([
            'code' => 0,
            'message' => '批量生成成功',
            'data' => $data,
        ]);
    }

    public function sendMail(UserSendMail $request)
    {
        ini_set('memory_limit', '-1');
        $scopeInfo = $this->resolveScope($request);
        $scope = $scopeInfo['scope'];
        $userIds = $scopeInfo['user_ids'];

        if ($scope === 'selected') {
            if (empty($userIds)) {
                return $this->fail([422, 'user_ids不能为空']);
            }
        }

        $sortType = in_array($request->input('sort_type'), ['ASC', 'DESC']) ? $request->input('sort_type') : 'DESC';
        $sort = $request->input('sort') ? $request->input('sort') : 'created_at';

        $builder = User::query()
            ->with('plan:id,name')
            ->orderBy('id', 'desc');

        if ($scope === 'filtered') {
            // filtered: apply filters/sort
            $builder->orderBy($sort, $sortType);
            $this->applyFiltersAndSorts($request, $builder);
        } elseif ($scope === 'selected') {
            $builder->whereIn('id', $userIds);
        } // all: ignore filter/sort

        $subject = $request->input('subject');
        $content = $request->input('content');
        $appName = admin_setting('app_name', 'VPSCS');
        $appUrl = admin_setting('app_url');

        $chunkSize = 1000;

        $builder->chunk($chunkSize, function ($users) use ($subject, $content, $appName, $appUrl) {
            foreach ($users as $user) {
                $vars = [
                    'app.name' => $appName,
                    'app.url' => $appUrl,
                    'now' => now()->format('Y-m-d H:i:s'),
                    'user.id' => $user->id,
                    'user.email' => $user->email,
                    'user.uuid' => $user->uuid,
                    'user.plan_name' => $user->plan?->name ?? '',
                    'user.expired_at' => $user->expired_at ? date('Y-m-d H:i:s', $user->expired_at) : '',
                    'user.transfer_enable' => (int) ($user->transfer_enable ?? 0),
                    'user.transfer_used' => (int) (($user->u ?? 0) + ($user->d ?? 0)),
                    'user.transfer_left' => (int) (($user->transfer_enable ?? 0) - (($user->u ?? 0) + ($user->d ?? 0))),
                ];

                $templateValue = [
                    'name' => $appName,
                    'url' => $appUrl,
                    'content' => $content,
                    'vars' => $vars,
                    'content_mode' => 'text',
                ];

                dispatch(new SendEmailJob([
                    'email' => $user->email,
                    'subject' => $subject,
                    'template_name' => 'notify',
                    'template_value' => $templateValue
                ], 'send_email_mass'));
            }
        });

        return $this->success(true);
    }

    public function ban(Request $request)
    {
        $scopeInfo = $this->resolveScope($request);
        $scope = $scopeInfo['scope'];
        $userIds = $scopeInfo['user_ids'];

        if ($scope === 'selected') {
            if (empty($userIds)) {
                return $this->fail([422, 'user_ids不能为空']);
            }
        }

        $sortType = in_array($request->input('sort_type'), ['ASC', 'DESC']) ? $request->input('sort_type') : 'DESC';
        $sort = $request->input('sort') ? $request->input('sort') : 'created_at';

        $builder = User::query()->orderBy('id', 'desc');

        if ($scope === 'filtered') {
            // filtered: keep current semantics
            $builder->orderBy($sort, $sortType);
            $this->applyFiltersAndSorts($request, $builder);
        } elseif ($scope === 'selected') {
            $builder->whereIn('id', $userIds);
        } // all: ignore filter/sort

        try {
            DB::transaction(function () use ($builder): void {
                $ids = (clone $builder)->reorder()->lockForUpdate()->pluck('id');
                $builder->update(['banned' => 1]);
                foreach ($ids->chunk(500) as $chunk) {
                    \Laravel\Sanctum\PersonalAccessToken::where('tokenable_type', (new User())->getMorphClass())
                        ->whereIn('tokenable_id', $chunk)->delete();
                }
            });
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail([500, '处理失败']);
        }
        // Full refresh not implemented.
        return $this->success(true);
    }

    // Delete user and related data.
    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:App\Models\User,id'
        ], [
            'id.required' => '用户ID不能为空',
            'id.exists' => '用户不存在'
        ]);
        $user = User::find($request->input('id'));
        HookManager::call('admin.user.destroy.before', [
            'user' => $user,
            'request' => $request,
        ]);

        try {
            DB::beginTransaction();
            $user->orders()->delete();
            $user->codes()->delete();
            $user->stat()->delete();
            $user->tickets()->delete();
            $user->delete();
            DB::commit();

            HookManager::call('admin.user.destroy.after', [
                'user' => $user,
                'request' => $request,
            ]);

            return $this->success(true);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return $this->fail([500, '删除失败']);
        }
    }
}
