<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $workshop->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; color: #49382d; font: 11px Tahoma, Arial, sans-serif; background: #000 url({{ asset('img/bg/bg.gif') }}) fixed; }
        .workshop { max-width: 980px; margin: 0 auto; padding: 12px; }
        .frame { border: 2px ridge #9c815b; background: #eee3ca; box-shadow: inset 0 0 0 2px #e8ddc4; }
        .head { display: flex; justify-content: space-between; padding: 7px 10px; border-bottom: 1px solid #826c4f; background: linear-gradient(#efe5cf, #cdbb98); }
        .head b { color: #6e160f; }
        .content { padding: 10px; }
        .message { margin-bottom: 8px; padding: 6px 8px; border: 1px solid #8eaf58; background: #e6f1d2; }
        .message.error { border-color: #b46d61; background: #f2d8d2; }
        .recipe { margin-bottom: 9px; border: 1px solid #b99c72; background: #f5ecd6; }
        .recipe-title { display: flex; align-items: center; gap: 8px; padding: 5px 8px; color: #6e160f; background: #dfcfaa; font-weight: bold; }
        .recipe-title img, .slot img { width: 42px; height: 42px; object-fit: contain; }
        .recipe-body { display: flex; align-items: center; gap: 12px; padding: 8px; flex-wrap: wrap; }
        .slots { display: flex; align-items: center; gap: 7px; flex: 1; flex-wrap: wrap; }
        .slot { width: 62px; min-height: 65px; padding: 4px; border: 1px solid #c5ae87; background: #fff8e8; text-align: center; }
        .slot.missing { filter: grayscale(1); opacity: .55; }
        .result { display: flex; align-items: center; gap: 8px; }
        .craft { padding: 4px 12px; color: #461c0b; border: 1px solid #826c4f; background: linear-gradient(#f6e8c8, #cdbb98); cursor: pointer; font-weight: bold; }
        .craft[disabled] { cursor: not-allowed; filter: grayscale(1); opacity: .55; }
        .empty { padding: 20px; color: #75644d; text-align: center; }
    </style>
</head>
<body>
<main class="workshop">
    <section class="frame">
        <header class="head"><b>{{ $workshop->name }} · мирные профессии</b><a href="{{ route('location') }}">Вернуться на локацию</a></header>
        <div class="content">
            @if(session('message'))<div class="message">{{ session('message') }}</div>@endif
            @if(session('error'))<div class="message error">{{ session('error') }}</div>@endif
            @forelse($recipes as $recipe)
                <article class="recipe">
                    <div class="recipe-title"><img src="{{ $recipe['image'] }}" alt=""><span>{{ $recipe['name'] }}<br><small>{{ $recipe['professionName'] }}: {{ $recipe['currentLevel'] }} / требуется {{ $recipe['requiredLevel'] }} ур.</small></span></div>
                    <div class="recipe-body">
                        <div class="slots">
                            @foreach($recipe['ingredients'] as $ingredient)
                                <div class="slot @if(!$ingredient['enough']) missing @endif" title="{{ $ingredient['name'] }}">
                                    <img src="{{ $ingredient['image'] }}" alt="{{ $ingredient['name'] }}"><br>
                                    <b>{{ $ingredient['available'] }}</b>/{{ $ingredient['required'] }}
                                </div>
                            @endforeach
                        </div>
                        <div class="result"><strong>=</strong><div class="slot"><img src="{{ $recipe['resultImage'] }}" alt="{{ $recipe['resultName'] }}"><br>×1</div>
                            <form action="{{ route('workshop.craft', ['id' => $workshop->id, 'recipe' => $recipe['id']]) }}" method="post">@csrf<button class="craft" @disabled(!$recipe['canCraft'])>Создать</button></form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="empty">Нет изученных рецептов. Используйте книгу рецепта из рюкзака.</div>
            @endforelse
        </div>
    </section>
</main>
</body>
</html>
