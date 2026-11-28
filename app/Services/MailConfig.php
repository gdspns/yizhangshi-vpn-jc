<?php

namespace App\Services;

use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Config;

/**
 * 把后台「系统设置 → 邮件」里填的 SMTP 应用到运行时配置。
 *
 * 注意：项目里的 config/mail.php 还是 Laravel 8 时代的写法（mail.host /
 * mail.port …），而框架已经是 Laravel 12，MailManager 只认
 * mail.default + mail.mailers.smtp.*。只改老键的话，后台填了 SMTP 也照样
 * 走 .env 里的 log 驱动，邮件只会写进日志、永远发不出去。
 */
class MailConfig
{
    /** 是否已经在后台填了 SMTP 主机。 */
    public static function configured(): bool
    {
        return trim((string) admin_setting('email_host', '')) !== '';
    }

    /**
     * 应用后台 SMTP 设置。返回是否真的应用了（没填 host 就什么都不做）。
     */
    public static function apply(): bool
    {
        $host = trim((string) admin_setting('email_host', ''));
        if ($host === '') {
            return false;
        }

        $username = trim((string) admin_setting('email_username', ''));
        $password = (string) admin_setting('email_password', '');
        $from = trim((string) admin_setting('email_from_address', ''));
        $port = (int) (admin_setting('email_port', '') ?: 0);
        $encryption = strtolower(trim((string) admin_setting('email_encryption', '')));

        // 常见组合的兜底：SSL 一般 465，TLS/明文一般 587。
        if ($port <= 0) {
            $port = $encryption === 'ssl' ? 465 : 587;
        }
        if ($encryption === '') {
            $encryption = $port === 465 ? 'ssl' : 'tls';
        }

        Config::set('mail.default', 'smtp');

        // 老键：兼容 config/mail.php 里的老写法，以及项目里其它读 mail.host 的代码。
        Config::set('mail.driver', 'smtp');
        Config::set('mail.host', $host);
        Config::set('mail.port', $port);
        Config::set('mail.encryption', $encryption);
        Config::set('mail.username', $username);
        Config::set('mail.password', $password);

        // 新键：Laravel 12 真正读取的配置。
        Config::set('mail.mailers.smtp.transport', 'smtp');
        Config::set('mail.mailers.smtp.scheme', $encryption === 'ssl' ? 'smtps' : null);
        Config::set('mail.mailers.smtp.host', $host);
        Config::set('mail.mailers.smtp.port', $port);
        Config::set('mail.mailers.smtp.username', $username !== '' ? $username : null);
        Config::set('mail.mailers.smtp.password', $password !== '' ? $password : null);
        Config::set('mail.mailers.smtp.timeout', 8);
        Config::set('mail.mailers.smtp.local_domain', (string) parse_url((string) config('app.url'), PHP_URL_HOST));

        Config::set('mail.from.address', $from !== '' ? $from : ($username !== '' ? $username : config('mail.from.address')));
        Config::set('mail.from.name', (string) admin_setting('app_name', 'VPN'));

        // Octane / 队列常驻进程里 MailManager 是单例，配置改完必须清掉已经建好的
        // transport，否则新配置要等进程重启才生效。
        try {
            app(MailManager::class)->purge();
        } catch (\Throwable $e) {
            // 拿不到 manager 就算了，Mail 门面下次仍会重建。
        }

        return true;
    }
}
