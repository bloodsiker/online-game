{{-- Контакты и поддержка (/contacts). Адрес — обычным текстом: его должны видеть модерация платёжных систем и игроки без JS. --}}
<style>
    .contacts { color: #3b2717; font: 11px/1.65 Tahoma, Arial, sans-serif; }
    .contacts h1 { margin: 4px 0 10px; color: #8b1a1a; font: bold 16px/22px Tahoma, Arial, sans-serif; text-align: center; }
    .contacts h2 { margin: 14px 0 6px; color: #6b2a12; font: bold 12px/18px Tahoma, Arial, sans-serif; }
    .contacts p { margin: 0 0 6px; }
    .contacts ul { margin: 0 0 6px; padding-left: 20px; }
    .contacts li { margin-bottom: 3px; }
</style>
<article class="b-post b-post--index">
    <div class="b-post__container">
        <div class="b-post__content contacts">
            <h1>Контакты и поддержка</h1>
            <p>Служба поддержки браузерной MMORPG «Вечность» (Eternum) отвечает на вопросы об игре, аккаунте и оплате.</p>

            <h2>Как связаться</h2>
            <ul>
                <li>Email: <a href="mailto:{{ config('game.support_email') }}"><b>{{ config('game.support_email') }}</b></a></li>
                @if(config('game.telegram_channel'))
                    <li>Telegram-канал новостей: <a href="{{ config('game.telegram_channel') }}" target="_blank" rel="noopener noreferrer">{{ config('game.telegram_channel') }}</a></li>
                @endif
                <li>Telegram-бот игры: <a href="https://t.me/eternum_rpg_bot" target="_blank" rel="noopener noreferrer">@eternum_rpg_bot</a></li>
                <li>Форум игры: <a href="{{ route('forum.index') }}">{{ route('forum.index') }}</a></li>
                <li>Сайт: <a href="{{ url('/') }}">{{ url('/') }}</a></li>
            </ul>

            <h2>Время ответа</h2>
            <p>Ежедневно. Обычно отвечаем в течение нескольких часов, максимум — 24 часа.</p>

            <h2>Вопросы по оплате</h2>
            <p>Напишите на email и укажите ник персонажа, номер ордера (его видно в игре: премиум-магазин → вкладка «Покупка» → «Мои ордера») и, если есть, хеш транзакции. Цены и порядок покупки описаны на странице <a href="{{ route('pricing') }}">«Магазин и цены»</a>, правила возврата — в разделе 5 <a href="{{ route('eula') }}">Лицензионного соглашения</a>.</p>
        </div>
    </div>
</article>
