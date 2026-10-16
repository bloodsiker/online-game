<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <title>{{ __('auth.email_verification.title') }}</title>
    <link rel="stylesheet" href="{{ asset('main/css/main_register.css') }}">
    <link rel="stylesheet" href="{{ asset('main/css/register.css') }}">
    <link rel="stylesheet" href="{{ asset('main/css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('main/css/css.css') }}">
    <link rel="stylesheet" href="{{ asset('main/css/mainnew.css') }}">
    <style>
        .b-main-wrapper { min-width: 1000px; min-height: 100vh; overflow: visible; }
        .b-main { top: 0; padding-top: 100px; }
        .email-verification-content { max-width: 680px; margin: 0 auto; padding: 18px 22px 24px; color: #49321e; text-align: center; }
        .email-verification-content p { margin: 10px 0; line-height: 1.6; }
        .email-verification-content__email { display: inline-block; max-width: 100%; margin: 8px 0 12px; padding: 7px 14px; border: 1px solid #b99a7c; background: rgba(255, 248, 225, .75); font-weight: bold; overflow-wrap: anywhere; }
        .email-verification-content__status { margin: 14px auto; padding: 9px 12px; border: 1px solid #9a7654; background: rgba(255, 248, 225, .7); }
        .email-verification-content form { margin-top: 20px; }
        .email-verification-content__links { margin-top: 16px !important; font-size: 12px; }
        .email-verification-content__links a { color: #7b2d13; }
        @media (max-width: 700px) {
            .b-column-wrapper { width: calc(100% - 20px) !important; top: 0 !important; }
            .email-verification-content { padding: 12px 8px 18px; }
        }
    </style>
</head>
<body style="background-image: url('{{ asset('main/images/theme_old/bg.gif') }}');">
<div class="b-main-wrapper">
    <div class="b-main">
        <div class="b-page__header" style="top: 0; z-index: 5; position: fixed;">
            <header class="b-nav-lvl-1" style="width: 990px;">
                <div class="b-nav-lvl-1__decor-border"></div>
                <div class="b-nav-lvl-1__decor-menu-side"></div>
                <div class="b-nav-lvl-1__decor-menu-top"></div>
                <div class="b-nav-lvl-1__auth">
                    <div id="userHeadBlock">
                        <div class="b-nav-lvl-1__create">
                            <a class="b-auth-button" href="{{ route('register') }}">
                                <span class="b-auth-button__content"><span class="b-aside__impo-link" style="font-size:15px;font-weight:bold;position:relative;color:#d49b2c;left:1px;top:-3px;">Регистрация</span></span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="b-nav-lvl-1__logo">
                    <span class="b-auth-button" style="position:relative;left:24px;top:7px;">
                        <span class="b-auth-button__content"><span class="b-aside__impo-link" style="font-size:15px;font-weight:bold;position:relative;color:#d49b2c;left:1px;top:-3px;">В игре сейчас: <span data-online-count>{{ $onlineCount }}</span></span></span>
                    </span>
                </div>
                <div class="b-nav-lvl-1__menu">
                    <ul class="b-nav-lvl-1__menu-list">
                        <li class="b-nav-lvl-1__menu-item" style="list-style-type:none;"><a class="b-aside__impo-link" href="{{ route('index') }}" style="font-size:14px;font-weight:bold;position:relative;color:#d49b2c;left:23px;top:8px;"><span>Главная</span></a></li>
                        <li class="b-nav-lvl-1__menu-item" style="list-style-type:none;"><a class="b-aside__impo-link" href="{{ route('library.index') }}" style="font-size:14px;font-weight:bold;position:relative;color:#d49b2c;left:23px;top:8px;"><span>Библиотека</span></a></li>
                        <li class="b-nav-lvl-1__menu-item" style="list-style-type:none;"><a class="b-aside__impo-link" href="#" style="font-size:14px;font-weight:bold;position:relative;color:#d49b2c;left:31px;top:8px;"><span>Телеграм</span></a></li>
                        <li class="b-nav-lvl-1__menu-item" style="list-style-type:none;"><a class="b-aside__impo-link" href="#" style="font-size:14px;font-weight:bold;position:relative;color:#d49b2c;left:24px;top:8px;"><span>Контакты</span></a></li>
                    </ul>
                </div>
            </header>
        </div>

        <div class="b-column-wrapper" style="width:860px;position:relative;top:-40px;">
            <div class="b-main-frame">
                <span class="b-main-frame__l"></span>
                <span class="b-main-frame__r"></span>
                <span class="b-main-frame__t"></span>
                <span class="b-main-frame__b"></span>
                <span class="b-main-frame__decor-tl"></span>
                <span class="b-main-frame__decor-tl-2"></span>
                <span class="b-main-frame__decor-tr"></span>
                <span class="b-main-frame__decor-tr-2"></span>
                <span class="b-main-frame__decor-bl"></span>
                <span class="b-main-frame__decor-br"></span>

                <div class="b-main-frame__cont">
                    <div class="b-common-block">
                        <span class="b-common-block__l"></span>
                        <span class="b-common-block__r"></span>
                        <span class="b-common-block__t b-common-block__t_2"></span>
                        <span class="b-common-block__b"></span>
                        <span class="b-common-block__bl"></span>
                        <span class="b-common-block__br"></span>
                        <span class="b-common-block__header-decor-l"></span>
                        <span class="b-common-block__header-decor-r"></span>
                        <span class="b-common-block__header">
                            <span class="b-common-block__header-inner"><span data-font="PTSans">EMAIL</span></span>
                        </span>

                        <div class="b-common-block__cont">
                            <div class="b-common-block__bgl">
                                <div class="b-common-block__bgr clearfix">
                                    <div class="email-verification-content">
                                        <div class="b-divider-5">
                                            <div class="b-divider-5__l"><div class="b-divider-5__l-inner"></div></div>
                                            <div class="b-divider-5__r"><div class="b-divider-5__r-inner"></div></div>
                                            <div class="b-divider-5__c"></div>
                                        </div>
                                        <h1 class="b-common-header">{{ __('auth.email_verification.title') }}</h1>
                                        <p>{{ __('auth.email_verification.notice_text') }}</p>
                                        <div class="email-verification-content__email">{{ $email }}</div>
                                        <p>{{ __('auth.email_verification.notice_hint') }}</p>

                                        @if(session('status') === 'verification-link-sent')
                                            <div class="email-verification-content__status">{{ __('auth.email_verification.sent') }}</div>
                                        @elseif(session('status') === 'verification-email-failed')
                                            <div class="email-verification-content__status">{{ __('auth.email_verification.send_failed') }}</div>
                                        @endif

                                        <form method="post" action="{{ route('verification.resend') }}">
                                            @csrf
                                            <button type="submit" class="b-button-red-5"><span class="b-button-red-5__inner">{{ __('auth.email_verification.resend') }}</span></button>
                                        </form>
                                        <p class="email-verification-content__links"><a href="{{ route('game') }}">{{ __('auth.email_verification.open_game') }}</a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
