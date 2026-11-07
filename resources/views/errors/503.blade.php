{{--
    Страница режима обслуживания (php artisan down) и любых 503.
    Самодостаточная: без Vite и без БД — во время деплоя фронт может пересобираться.
--}}
@php
    $retryAfter = isset($exception) && method_exists($exception, 'getHeaders')
        ? (int) ($exception->getHeaders()['Retry-After'] ?? 0)
        : 0;
    $refresh = $retryAfter > 0 ? min(max($retryAfter, 15), 300) : 60;
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta http-equiv="refresh" content="{{ $refresh }}">
    <title>{{ __('homepage.game_name') }} — технические работы</title>
    <link rel="icon" type="image/png" href="/favicon.ico">
    <style>
        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; }
        body {
            display: flex; align-items: center; justify-content: center;
            padding: 24px 16px;
            color: #e8d6b0;
            font: 14px/1.6 Tahoma, Arial, sans-serif;
            text-align: center;
            background:
                radial-gradient(ellipse at center, rgba(42, 29, 18, .55) 0%, rgba(10, 7, 4, .95) 75%),
                #120c07 url('/main/img/b7904c63e2ccf951.jpg');
        }
        .box {
            width: 100%; max-width: 520px;
            padding: 36px 28px 30px;
            background: rgba(33, 27, 21, .92);
            border: 1px solid #554537;
            border-radius: 6px;
            box-shadow: 0 0 0 4px rgba(18, 12, 7, .8), 0 20px 60px rgba(0, 0, 0, .7);
        }
        .logo { width: 96px; height: 96px; filter: drop-shadow(0 0 14px rgba(237, 203, 134, .35)); }
        h1 { margin: 20px 0 10px; color: #edcb86; font: bold 20px/1.4 Cambria, Georgia, serif; text-shadow: 1px 1px #281b10; }
        p { margin: 0 0 10px; }
        .muted { color: #b59b7a; font-size: 12px; }
        .divider { height: 1px; margin: 22px 0 18px; background: linear-gradient(90deg, transparent, #7a6046, transparent); }
        .btn {
            display: inline-block; padding: 9px 26px;
            color: #ffe5ab; text-decoration: none; font-weight: bold;
            background: linear-gradient(#5a3f22, #3a2814);
            border: 1px solid #b8893d; border-radius: 4px;
        }
        .btn:hover { background: linear-gradient(#6d4d2a, #47321a); color: #fff; }
        .progress {
            height: 10px; margin: 4px 0 8px; overflow: hidden;
            background: #140e09; border: 1px solid #554537; border-radius: 5px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, .6);
        }
        .progress__bar {
            width: 0; height: 100%;
            background: linear-gradient(90deg, #8a5a22, #e69934 60%, #ffe5ab);
            box-shadow: 0 0 8px rgba(230, 153, 52, .6);
            animation: fill {{ $refresh }}s linear forwards;
        }
        @keyframes fill { to { width: 100%; } }
        .timer { margin-bottom: 16px; }
        .timer b { color: #edcb86; }
    </style>
</head>
<body>
    <main class="box">
        <img class="logo" src="/main/img/logo.png" alt="{{ __('homepage.game_name') }}">

        <h1>Идут технические работы</h1>
        <p>Мир {{ __('homepage.game_name') }} ненадолго закрыт: мы обновляем игру.</p>
        <p class="muted">Прогресс персонажей сохранён. Обычно это занимает несколько минут.</p>

        <div class="divider"></div>

        <div class="progress" role="progressbar" aria-label="До обновления страницы"><div class="progress__bar"></div></div>
        <p class="muted timer">Страница обновится через <b id="countdown">{{ $refresh }}</b> сек.</p>
        <a class="btn" href="javascript:location.reload()">Обновить сейчас</a>
    </main>
    <script>
        (function () {
            var left = {{ $refresh }};
            var el = document.getElementById('countdown');
            var timer = setInterval(function () {
                left -= 1;
                if (left <= 0) {
                    clearInterval(timer);
                    el.textContent = '0';
                    location.reload();
                    return;
                }
                el.textContent = left;
            }, 1000);
        })();
    </script>
</body>
</html>
