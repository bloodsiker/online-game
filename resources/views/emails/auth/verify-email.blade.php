<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('auth.email_verification.subject') }}</title>
</head>
<body style="margin:0;padding:24px;background:#241a13;color:#49321e;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:600px;border:2px solid #815b37;background:#ead8ba url('{{ asset('main/images/theme_old/news_bg2.gif') }}') repeat;">
                <tr>
                    <td align="center" style="padding:24px 20px 12px;color:#6f1d0b;font-size:25px;font-weight:bold;text-shadow:1px 1px #f7e8c9;">
                        {{ __('auth.email_verification.game_title') }}
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 28px 28px;text-align:center;">
                        <div style="height:1px;background:#a47d55;margin:8px 0 22px;"></div>
                        <h1 style="margin:0 0 18px;color:#6f1d0b;font-size:22px;">{{ __('auth.email_verification.title') }}</h1>
                        <p style="margin:0 0 12px;font-size:16px;font-weight:bold;">{{ __('auth.email_verification.greeting', ['name' => $playerName]) }}</p>
                        <p style="margin:0 0 22px;font-size:15px;line-height:1.6;">{{ __('auth.email_verification.mail_intro') }}</p>
                        <a href="{{ $verificationUrl }}" style="display:inline-block;padding:13px 26px;border:1px solid #6f1d0b;background:#8e2a12;color:#fff3d1;font-size:15px;font-weight:bold;text-decoration:none;">{{ __('auth.email_verification.mail_button') }}</a>
                        @php($verificationExpiryMinutes = (int) config('auth.verification.expire', 1440))
                        @php($verificationExpiry = $verificationExpiryMinutes % 60 === 0 ? ((int) ($verificationExpiryMinutes / 60)).' ч.' : $verificationExpiryMinutes.' мин.')
                        <p style="margin:20px 0 8px;font-size:13px;color:#69513b;">{{ __('auth.email_verification.mail_expiry', ['duration' => $verificationExpiry]) }}</p>
                        <p style="margin:14px 0 6px;font-size:12px;color:#69513b;">{{ __('auth.email_verification.mail_fallback') }}</p>
                        <p style="margin:0;font-size:12px;word-break:break-all;"><a href="{{ $verificationUrl }}" style="color:#7f2410;">{{ $verificationUrl }}</a></p>
                        <p style="margin:22px 0 0;font-size:12px;color:#69513b;">{{ __('auth.email_verification.mail_ignore') }}</p>
                        <div style="height:1px;background:#a47d55;margin:22px 0 12px;"></div>
                        <p style="margin:0;font-size:11px;color:#806c57;">{{ __('auth.email_verification.mail_footer') }}</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
