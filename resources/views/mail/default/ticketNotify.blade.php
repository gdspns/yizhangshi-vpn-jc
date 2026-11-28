<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>新工单提醒</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5;padding:40px 20px;">
<tr><td align="center">
<table width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;">
    <tr><td style="padding-bottom:24px;text-align:center;">
        <span style="font-size:20px;font-weight:700;color:#18181b;">{{$name}}</span>
    </td></tr>
    <tr><td style="background:#ffffff;border-radius:12px;border:1px solid #e4e4e7;padding:40px;">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr><td style="font-size:22px;font-weight:700;color:#18181b;padding-bottom:6px;">收到新工单 #{{$ticket_id}}</td></tr>
            <tr><td style="font-size:13px;color:#a1a1aa;padding-bottom:24px;">{{$created_at}}</td></tr>
            <tr><td style="font-size:15px;color:#18181b;font-weight:600;padding-bottom:6px;">{{$subject}}</td></tr>
            <tr><td style="font-size:13px;color:#71717a;padding-bottom:20px;">提交用户：{{$email}} · 优先级：{{$level}}</td></tr>
            <tr><td style="background:#fafafa;border:1px solid #f0f0f1;border-radius:10px;padding:18px;font-size:14px;color:#3f3f46;line-height:1.75;">{!! nl2br(e($content)) !!}</td></tr>
            <tr><td align="center" style="padding-top:28px;">
                <a href="{{$url}}" style="display:inline-block;background:#18181b;color:#ffffff;font-size:14px;font-weight:600;text-decoration:none;padding:12px 28px;border-radius:8px;">去后台回复工单</a>
            </td></tr>
        </table>
    </td></tr>
    <tr><td style="padding-top:24px;text-align:center;">
        <a href="{{$url}}" style="font-size:13px;color:#a1a1aa;text-decoration:none;">{{$url}}</a>
        <p style="font-size:12px;color:#d4d4d8;margin:8px 0 0;">此邮件由系统自动发送，请勿直接回复。</p>
    </td></tr>
</table>
</td></tr>
</table>
</body>
</html>
