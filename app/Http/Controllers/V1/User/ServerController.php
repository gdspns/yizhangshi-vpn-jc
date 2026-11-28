<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\NodeResource;
use App\Models\User;
use App\Services\ServerService;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    public function fetch(Request $request)
    {
        $user = User::findOrFail($request->user()->id);

        // 多订阅模式下，套餐都挂在子账号（owner_user_id）上，主账号自身的
        // 到期时间 / 流量往往还是很久以前第一条订阅留下的旧值。删掉那条订阅
        // 或它过期之后，主账号会被判为不可用，结果明明还有其它有效订阅，
        // 节点页却显示「没有可用节点」。这里回落到当前订阅（没有指针就取
        // 第一条仍然有效的订阅），按它的权限组展示节点。
        $target = $this->isUsable($user) ? $user : ($this->activeSubscription($user) ?: $user);

        $servers = $this->isUsable($target) ? ServerService::getAvailableServers($target) : [];
        $eTag = sha1(json_encode(array_column($servers, 'cache_key')));
        if (strpos($request->header('If-None-Match', ''), $eTag) !== false) {
            return response(null, 304);
        }
        $data = NodeResource::collection($servers);
        return response([
            'data' => $data
        ])->header('ETag', "\"{$eTag}\"");
    }

    /** 与 UserService::isAvailable 等价，但把 expired_at=0 也当作长期有效。 */
    private function isUsable(User $user): bool
    {
        if ($user->banned || !$user->transfer_enable) {
            return false;
        }
        $expiredAt = $user->expired_at;
        return $expiredAt === null || (int) $expiredAt === 0 || (int) $expiredAt > time();
    }

    /** 名下仍然有效的订阅：优先当前订阅，否则按 id 取第一条。 */
    private function activeSubscription(User $owner): ?User
    {
        $query = User::where('owner_user_id', $owner->id)
            ->where('banned', 0)
            ->where('transfer_enable', '>', 0)
            ->where(function ($query) {
                $query->whereNull('expired_at')
                    ->orWhere('expired_at', 0)
                    ->orWhere('expired_at', '>', time());
            });

        if ($owner->current_subscription_id) {
            $current = (clone $query)
                ->where('id', $owner->current_subscription_id)
                ->first();
            if ($current) {
                return $current;
            }
        }

        return $query->orderBy('id')->first();
    }
}
