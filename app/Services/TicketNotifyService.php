<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * 工单邮件通知：用户提交新工单时，给后台配置的邮箱发提醒。
 *
 * 收件地址存在 ticket_notify_emails（逗号分隔），发信直接用站点已有的
 * SMTP 配置（系统设置 → 邮件），不需要另建邮箱。
 */
class TicketNotifyService
{
    public const SETTING_KEY = 'ticket_notify_emails';

    /** 解析并去重邮箱列表。 */
    public static function parse(string $raw): array
    {
        $parts = preg_split('/[\s,;，；、]+/u', $raw) ?: [];
        $emails = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '' || !filter_var($part, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            if (!in_array($part, $emails, true)) {
                $emails[] = $part;
            }
        }
        return $emails;
    }

    public static function emails(): array
    {
        return self::parse((string) admin_setting(self::SETTING_KEY, ''));
    }

    /** 站点 SMTP 是否真的能发信（没配好就别发，免得刷一堆失败日志）。 */
    public static function smtpReady(): bool
    {
        if (admin_setting('email_host')) {
            return true;
        }
        // 没在后台填 SMTP 时，只能看 .env 里的默认配置。
        // 项目自带的 smtp.mailgun.org 是占位值，账号密码为空，发不出去。
        $driver = strtolower((string) config('mail.default'));
        if (!in_array($driver, ['smtp', 'ses', 'postmark', 'mailgun', 'resend', 'sendmail'], true)) {
            return false;
        }
        $host = strtolower((string) config('mail.host'));
        if ($host === '' || in_array($host, ['smtp.mailgun.org', 'localhost', '127.0.0.1'], true)) {
            return false;
        }
        return (string) config('mail.username') !== '' || in_array($driver, ['ses', 'postmark', 'mailgun', 'resend', 'sendmail'], true);
    }

    /**
     * 新工单提醒。放在事务提交之后调用，异常一律吞掉，
     * 通知失败绝不能影响用户提交工单。
     */
    public static function notifyNewTicket(Ticket $ticket): void
    {
        try {
            $emails = self::emails();
            if (!$emails || !self::smtpReady()) {
                return;
            }
            $user = User::find($ticket->user_id);
            $message = (string) optional(
                TicketMessage::where('ticket_id', $ticket->id)->orderBy('id')->first()
            )->message;

            $vars = [
                'name' => (string) admin_setting('app_name', 'VPN'),
                'ticket_id' => (string) $ticket->id,
                'subject' => (string) $ticket->subject,
                'level' => self::levelText($ticket->level),
                'email' => (string) optional($user)->email,
                'content' => $message,
                'created_at' => (string) $ticket->created_at,
                'url' => rtrim((string) admin_setting('app_url', config('app.url')), '/')
                    . '/' . trim((string) admin_setting('secure_path', ''), '/'),
            ];

            foreach ($emails as $email) {
                SendEmailJob::dispatch([
                    'email' => $email,
                    'subject' => '新工单 #' . $ticket->id . '：' . $ticket->subject,
                    'template_name' => 'ticketNotify',
                    'template_value' => $vars,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error($e);
        }
    }

    /** 测试邮件，管理员点按钮时同步发送，直接把结果回给页面。 */
    public static function sendTest(string $email): array
    {
        return MailService::sendEmail([
            'email' => $email,
            'subject' => '工单通知测试邮件',
            'template_name' => 'ticketNotify',
            'template_value' => [
                'name' => (string) admin_setting('app_name', 'VPN'),
                'ticket_id' => '测试',
                'subject' => '这是一封测试邮件',
                'level' => '中优先',
                'email' => $email,
                'content' => "如果你收到这封邮件，说明工单通知已经可以正常发送。\n\n正式使用时，用户提交新工单会立刻发到这个邮箱。",
                'created_at' => (string) now(),
                'url' => rtrim((string) admin_setting('app_url', config('app.url')), '/'),
            ],
        ]);
    }

    private static function levelText($level): string
    {
        return match ((int) $level) {
            0 => '低优先',
            2 => '高优先',
            default => '中优先',
        };
    }
}
