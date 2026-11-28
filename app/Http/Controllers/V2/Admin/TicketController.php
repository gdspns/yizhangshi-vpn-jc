<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use App\Services\TicketNotifyService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    private function applyFiltersAndSorts(Request $request, $builder)
    {
        if ($request->has('filter')) {
            collect($request->input('filter'))->each(function ($filter) use ($builder) {
                $key = $filter['id'];
                $value = $filter['value'];
                $builder->where(function ($query) use ($key, $value) {
                    if (is_array($value)) {
                        $query->whereIn($key, $value);
                    } else {
                        $query->where($key, 'like', "%{$value}%");
                    }
                });
            });
        }

        if ($request->has('sort')) {
            collect($request->input('sort'))->each(function ($sort) use ($builder) {
                $key = $sort['id'];
                $value = $sort['desc'] ? 'DESC' : 'ASC';
                $builder->orderBy($key, $value);
            });
        }
    }
    public function fetch(Request $request)
    {
        if ($request->input('id')) {
            return $this->fetchTicketById($request);
        } else {
            return $this->fetchTickets($request);
        }
    }

    /**
     * Summary of fetchTicketById
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function fetchTicketById(Request $request)
    {
        $ticket = Ticket::with('messages', 'user')->find($request->input('id'));

        if (!$ticket) {
            return $this->fail([400202, '工单不存在']);
        }
        $ticket->messages->each(fn($msg) => $msg->setRelation('ticket', $ticket));
        $result = $ticket->toArray();
        $result['user'] = UserController::transformUserData($ticket->user);

        return $this->success($result);
    }

    /**
     * Summary of fetchTickets
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    private function fetchTickets(Request $request)
    {
        $ticketModel = Ticket::with('user')
            ->when($request->has('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->when($request->has('reply_status'), function ($query) use ($request) {
                $query->whereIn('reply_status', $request->input('reply_status'));
            })
            ->when($request->filled('email'), function ($query) use ($request) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('email', User::normalizeEmail($request->input('email')));
                });
            });

        $this->applyFiltersAndSorts($request, $ticketModel);
        $tickets = $ticketModel
            ->latest('updated_at')
            ->paginate(
                perPage: $request->integer('pageSize', 10),
                page: $request->integer('current', 1)
            );

        // 获取items然后映射转换
        $items = collect($tickets->items())->map(function ($ticket) {
            $ticketData = $ticket->toArray();
            $ticketData['user'] = UserController::transformUserData($ticket->user);
            return $ticketData;
        })->all();

        return response([
            'data' => $items,
            'total' => $tickets->total()
        ]);
    }

    public function reply(Request $request)
    {
        $request->validate([
            'id' => 'required|numeric',
            'message' => 'required|string'
        ], [
            'id.required' => '工单ID不能为空',
            'message.required' => '消息不能为空'
        ]);
        $ticketService = new TicketService();
        $ticketService->replyByAdmin(
            $request->input('id'),
            $request->input('message'),
            $request->user()->id
        );
        return $this->success(true);
    }

    public function close(Request $request)
    {
        $request->validate([
            'id' => 'required|numeric'
        ], [
            'id.required' => '工单ID不能为空'
        ]);
        try {
            $ticket = Ticket::findOrFail($request->input('id'));
            $ticket->status = Ticket::STATUS_CLOSED;
            $ticket->save();
            return $this->success(true);
        } catch (ModelNotFoundException $e) {
            return $this->fail([400202, '工单不存在']);
        } catch (\Exception $e) {
            return $this->fail([500101, '关闭失败']);
        }
    }

    public function show($ticketId)
    {
        $ticket = Ticket::with([
            'user',
            'messages' => function ($query) {
                $query->with(['user']);
            }
        ])->findOrFail($ticketId);

        $ticket->messages->each(fn($msg) => $msg->setRelation('ticket', $ticket));

        return response()->json([
            'data' => $ticket
        ]);
    }

    /** 工单通知邮箱配置：读（发信密码不回传，只回是否已设置）。 */
    public function notifyConfig()
    {
        return $this->success([
            'emails' => TicketNotifyService::emails(),
            'smtp_ready' => TicketNotifyService::smtpReady(),
            'from' => (string) (admin_setting('email_from_address') ?: config('mail.from.address')),
            'smtp' => [
                'host' => (string) admin_setting('email_host', ''),
                'port' => (string) admin_setting('email_port', ''),
                'encryption' => (string) admin_setting('email_encryption', ''),
                'username' => (string) admin_setting('email_username', ''),
                'from' => (string) admin_setting('email_from_address', ''),
                'has_password' => (bool) admin_setting('email_password'),
            ],
        ]);
    }

    /** 工单通知配置：存（收件邮箱 + 可选的发信 SMTP）。 */
    public function saveNotify(Request $request)
    {
        $raw = (string) $request->input('emails', '');
        $emails = TicketNotifyService::parse($raw);

        // 输入框里还剩没解析出来的内容，说明有写错的邮箱，直接退回去让管理员改。
        $leftover = trim(preg_replace('/[\s,;，；、]+/u', ' ', $raw));
        foreach ($emails as $email) {
            $leftover = trim(str_replace($email, '', $leftover));
        }
        if ($leftover !== '') {
            return $this->fail([422, '邮箱格式不正确：' . $leftover]);
        }
        admin_setting([TicketNotifyService::SETTING_KEY => implode(',', $emails)]);

        // 顺带保存发信服务器：这样管理员在一个页面里就能把通知配好。
        $smtp = $request->input('smtp');
        if (is_array($smtp)) {
            $host = trim((string) ($smtp['host'] ?? ''));
            $port = trim((string) ($smtp['port'] ?? ''));
            $encryption = strtolower(trim((string) ($smtp['encryption'] ?? '')));
            $username = trim((string) ($smtp['username'] ?? ''));
            $password = (string) ($smtp['password'] ?? '');
            $from = trim((string) ($smtp['from'] ?? ''));

            if ($host !== '' && !preg_match('/^[A-Za-z0-9.\-]+$/', $host)) {
                return $this->fail([422, 'SMTP 服务器地址格式不正确']);
            }
            if ($port !== '' && (!ctype_digit($port) || (int) $port < 1 || (int) $port > 65535)) {
                return $this->fail([422, 'SMTP 端口不正确']);
            }
            if ($encryption !== '' && !in_array($encryption, ['ssl', 'tls', 'starttls'], true)) {
                return $this->fail([422, '加密方式不正确']);
            }
            if ($from !== '' && !filter_var($from, FILTER_VALIDATE_EMAIL)) {
                return $this->fail([422, '发件邮箱格式不正确：' . $from]);
            }

            $payload = [
                'email_host' => $host,
                'email_port' => $port,
                'email_encryption' => $encryption,
                'email_username' => $username,
                'email_from_address' => $from,
            ];
            if ($password !== '') {
                $payload['email_password'] = $password;
            }
            admin_setting($payload);
        }

        return $this->success([
            'emails' => TicketNotifyService::emails(),
            'smtp_ready' => TicketNotifyService::smtpReady(),
            'from' => (string) (admin_setting('email_from_address') ?: config('mail.from.address')),
        ]);
    }

    /** 给配置的地址发一封测试邮件，直接把发送结果回给页面。 */
    public function testNotify()
    {
        $emails = TicketNotifyService::emails();
        if (!$emails) {
            return $this->fail([422, '请先添加接收通知的邮箱']);
        }
        $results = [];
        foreach ($emails as $email) {
            $log = TicketNotifyService::sendTest($email);
            $results[] = ['email' => $email, 'error' => $log['error'] ?? null];
        }
        $failed = array_values(array_filter($results, fn($item) => !empty($item['error'])));
        if (count($failed) === count($results)) {
            return $this->fail([500, '测试邮件发送失败：' . $failed[0]['error']]);
        }
        return $this->success(['results' => $results]);
    }
}
