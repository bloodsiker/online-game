@php($verificationExpiryMinutes = (int) config('auth.verification.expire', 1440))
@php($verificationExpiry = $verificationExpiryMinutes % 60 === 0 ? ((int) ($verificationExpiryMinutes / 60)).' ч' : $verificationExpiryMinutes.' мин')
@php($supportEmail = (string) config('game.support_email'))
@php($logoPath = public_path('img/logo/logo-email.png'))
@php($logoSrc = isset($message) && is_file($logoPath) ? $message->embed($logoPath) : rtrim((string) config('app.url'), '/').'/img/logo/logo-email.png')
<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light only">
    <title>{{ __('auth.email_verification.subject') }}</title>
</head>
<body style="margin:0;padding:0;background:#1c140e;font-family:Georgia,'Times New Roman',serif;color:#49321e;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;">{{ __('auth.email_verification.mail_preheader') }}</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#1c140e" style="background:#1c140e;">
    <tr>
        <td align="center" style="padding:28px 12px;">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:600px;">
                {{-- Логотип --}}
                <tr>
                    <td align="center" style="padding:0 0 18px;">
                        <a href="{{ url('/') }}" style="text-decoration:none;">
                            <img src="{{ $logoSrc }}" width="330" height="110" alt="{{ __('auth.email_verification.game_title') }}" style="display:block;width:100%;max-width:330px;height:auto;border:0;color:#e8c27a;font-size:28px;font-weight:bold;">
                        </a>
                    </td>
                </tr>
                {{-- Карточка --}}
                <tr>
                    <td bgcolor="#ead8ba" style="background:#ead8ba url('{{ asset('main/images/theme_old/news_bg2.gif') }}') repeat;border:2px solid #815b37;border-radius:6px;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td bgcolor="#6f1d0b" style="background:#6f1d0b;height:4px;line-height:4px;font-size:0;border-radius:4px 4px 0 0;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td align="center" style="padding:30px 32px 8px;">
                                    <h1 style="margin:0;color:#6f1d0b;font-size:24px;font-weight:bold;letter-spacing:.5px;">{{ __('auth.email_verification.title') }}</h1>
                                    <div style="margin:14px auto 0;width:120px;height:1px;background:#a47d55;line-height:1px;font-size:0;">&nbsp;</div>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="padding:18px 32px 0;font-family:Arial,Helvetica,sans-serif;">
                                    <p style="margin:0 0 12px;font-size:17px;font-weight:bold;color:#49321e;">{{ __('auth.email_verification.greeting', ['name' => $playerName]) }}</p>
                                    <p style="margin:0 0 10px;font-size:15px;line-height:1.6;color:#49321e;">{{ __('auth.email_verification.mail_intro') }}</p>
                                    <p style="margin:0 0 24px;font-size:14px;line-height:1.6;color:#69513b;">{{ __('auth.email_verification.mail_welcome') }}</p>
                                </td>
                            </tr>
                            {{-- Кнопка --}}
                            <tr>
                                <td align="center" style="padding:0 32px;">
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                        <tr>
                                            <td bgcolor="#8e2a12" style="background:#8e2a12;border:1px solid #c7964c;border-radius:4px;">
                                                <a href="{{ $verificationUrl }}" style="display:inline-block;padding:14px 34px;font-family:Arial,Helvetica,sans-serif;font-size:16px;font-weight:bold;color:#ffe6a5;text-decoration:none;border-radius:4px;">{{ __('auth.email_verification.mail_button') }}</a>
                                            </td>
                                        </tr>
                                    </table>
                                    <p style="margin:14px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#69513b;">{{ __('auth.email_verification.mail_expiry', ['duration' => $verificationExpiry]) }}</p>
                                </td>
                            </tr>
                            {{-- Запасная ссылка --}}
                            <tr>
                                <td style="padding:24px 32px 0;font-family:Arial,Helvetica,sans-serif;">
                                    <div style="height:1px;background:#a47d55;line-height:1px;font-size:0;">&nbsp;</div>
                                    <p style="margin:16px 0 6px;font-size:12px;color:#69513b;text-align:center;">{{ __('auth.email_verification.mail_fallback') }}</p>
                                    <p style="margin:0;font-size:12px;line-height:1.5;word-break:break-all;text-align:center;"><a href="{{ $verificationUrl }}" style="color:#7f2410;">{{ $verificationUrl }}</a></p>
                                    <p style="margin:18px 0 0;font-size:12px;color:#69513b;text-align:center;">{{ __('auth.email_verification.mail_ignore') }}</p>
                                </td>
                            </tr>
                            <tr><td style="height:28px;line-height:28px;font-size:0;">&nbsp;</td></tr>
                        </table>
                    </td>
                </tr>
                {{-- Футер --}}
                <tr>
                    <td align="center" style="padding:18px 20px 0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#a08a70;">
                        @if ($supportEmail !== '')
                            {{ __('auth.email_verification.mail_support') }}
                            <a href="mailto:{{ $supportEmail }}" style="color:#e8c27a;text-decoration:none;">{{ $supportEmail }}</a><br>
                        @endif
                        {{ __('auth.email_verification.mail_footer') }}<br>
                        <a href="{{ url('/') }}" style="color:#e8c27a;text-decoration:none;">{{ parse_url(url('/'), PHP_URL_HOST) }}</a> &middot; &copy; 2026
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
