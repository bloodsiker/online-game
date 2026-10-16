<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('auth.email_verification.verified_title') }}</title>
    <link rel="stylesheet" href="{{ asset('main/css/main_register.css') }}">
    <link rel="stylesheet" href="{{ asset('main/css/register.css') }}">
    <link rel="stylesheet" href="{{ asset('main/css/css.css') }}">
    <style>
        body { min-height:100vh; margin:0; display:flex; align-items:center; justify-content:center; padding:20px; box-sizing:border-box; background:#21170f url('{{ asset('main/images/theme_old/bg.gif') }}') repeat; }
        .email-state { width:min(620px,100%); padding:26px 30px; border:2px solid #815b37; color:#49321e; text-align:center; background:#ead8ba url('{{ asset('main/images/theme_old/news_bg2.gif') }}') repeat; box-shadow:0 8px 28px rgba(0,0,0,.55); }
        .email-state h1 { margin:0 0 18px; color:#6f1d0b; font-size:25px; }
        .email-state p { margin:10px 0 22px; line-height:1.6; }
    </style>
</head>
<body>
<main class="email-state">
    <h1>{{ __('auth.email_verification.verified_title') }}</h1>
    <p>{{ __('auth.email_verification.verified_text') }}</p>
    <a href="{{ route('game') }}" class="b-button-red-5"><span class="b-button-red-5__inner">{{ __('auth.email_verification.open_game') }}</span></a>
</main>
</body>
</html>
