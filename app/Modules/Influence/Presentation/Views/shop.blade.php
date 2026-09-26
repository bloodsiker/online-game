<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{ $shop->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <style>
        body { margin: 0; color: #49382d; font: 11px Tahoma, sans-serif; background: #000 url('/img/bg/bg.gif') fixed; }
        .influence-shop { max-width: 920px; margin: 12px auto; padding: 14px; background: url('/img/bg/tbl-usi_bg.gif') repeat; border: 1px solid #a66e45; }
        .influence-shop h1 { margin: 0 0 12px; color: #7d2415; font-size: 16px; text-align: center; }
        .influence-section { margin: 12px 0; border: 1px solid #c69862; background: rgba(255,245,213,.5); }
        .influence-section__title { padding: 7px 10px; color: #ffe9ba; font-weight: bold; background: #75402a; }
        .influence-section__title span { float: right; }
        .influence-section__items { display: flex; flex-wrap: wrap; gap: 8px; padding: 10px; }
        .influence-shop-item { display: grid; grid-template-columns: 73px 1fr; width: 280px; min-height: 88px; padding: 6px; border: 1px solid #db9f73; border-radius: 4px; box-sizing: border-box; }
        .influence-shop-item.is-locked { opacity: .55; }
        .influence-shop-item__image { width: 71px; height: 72px; background: url('/main/images/user-reward-frame.png') center/71px 72px no-repeat; }
        .influence-shop-item__image img { width: 60px; height: 60px; margin: 6px; object-fit: contain; }
        .influence-shop-item__body { padding: 4px 6px; }
        .influence-shop-item__name { color: #8c1d12; font-weight: bold; }
        .influence-shop-item form { margin-top: 7px; }
        .influence-shop-item input { width: 38px; }
        .shop-message { margin: 8px 0; padding: 6px; border: 1px solid #c69862; text-align: center; }
    </style>
</head>
<body>
<main class="influence-shop">
    <h1>{{ $shop->name }}</h1>
    @if(session('message'))<div class="shop-message">{{ session('message') }}</div>@endif
    @forelse($sections as $section)
        @php($sectionUnlocked = $section->player_influence >= $section->required_influence)
        <section class="influence-section">
            <div class="influence-section__title">{{ $section->name }} <span>Ваше влияние: {{ number_format($section->player_influence, 0, '', ' ') }}</span></div>
            <div class="influence-section__items">
                @foreach($section->items as $entry)
                    @php($unlocked = $sectionUnlocked && $section->player_influence >= $entry->required_influence)
                    <article class="influence-shop-item {{ $unlocked ? '' : 'is-locked' }}">
                        <div class="influence-shop-item__image"><img src="{{ $entry->item?->image }}" alt=""></div>
                        <div class="influence-shop-item__body">
                            <div class="influence-shop-item__name">{{ $entry->item?->name }}</div>
                            <div>Нужно влияния: {{ number_format(max($section->required_influence, $entry->required_influence), 0, '', ' ') }}</div>
                            <div>Цена: {{ number_format($entry->price, 0, '', ' ') }} монет @if($entry->diamond) + {{ $entry->diamond }} алм.@endif</div>
                            @if($unlocked)
                                <form method="post" action="{{ route('influence.shop.buy', [$shop->id, $entry->id]) }}">@csrf<input type="number" name="quantity" min="1" max="999" value="1"> <span class="butt1 pointer"><span><button type="submit" class="butt1 shop">Купить</button></span></span></form>
                            @else
                                <div><b>Товар пока недоступен</b></div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @empty
        <p>В магазине пока нет разделов влияния.</p>
    @endforelse
    <p style="text-align:center"><a href="{{ route('location') }}">Вернуться на локацию</a></p>
</main>
</body>
</html>
