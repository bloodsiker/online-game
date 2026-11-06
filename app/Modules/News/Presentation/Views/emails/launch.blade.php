<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head><meta charset="utf-8"><title>{{ __('prelaunch.mail_subject') }}</title></head>
<body style="margin:0;padding:24px;background:#241a13;color:#49321e;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
    <tr><td align="center">
        <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:600px;border:2px solid #815b37;background:#ead8ba url('{{ asset('main/images/theme_old/news_bg2.gif') }}') repeat;">
            <tr><td align="center" style="padding:24px;color:#6f1d0b;font-size:26px;font-weight:bold;">{{ __('homepage.game_name') }}</td></tr>
            <tr><td style="padding:0 28px 28px;text-align:center;">
                <h1 style="margin:0 0 18px;color:#6f1d0b;font-size:22px;">{{ __('prelaunch.mail_title') }}</h1>
                <p style="font-size:15px;line-height:1.6;">{{ __('prelaunch.mail_intro') }}</p>
                <a href="{{ $registrationUrl }}" style="display:inline-block;margin:12px 0;padding:13px 24px;border:1px solid #c7964c;border-radius:4px;background:#742b19;color:#ffe6a5;font-size:16px;font-weight:bold;text-decoration:none;">{{ __('prelaunch.mail_action') }}</a>
                <p style="font-size:11px;line-height:1.5;color:#876c50;">{{ __('prelaunch.mail_reason') }}</p>
            </td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
