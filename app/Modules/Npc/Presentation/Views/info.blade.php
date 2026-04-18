<html>
<head>
    <title>{{ $page->npc->name }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <style>
        body { margin: 0; font-family: Tahoma, Geneva, sans-serif; font-size: 11px; color: #955c4a; background: #000 url({{ asset('img/bg/bg2.gif') }}); }
        .box { padding: 16px; text-align: center; }
        .name { font-size: 18px; font-weight: 700; color: #faf7b9; margin-bottom: 10px; }
        .image { margin-bottom: 10px; }
        .image img { max-width: 180px; max-height: 180px; border: 1px solid #6e4638; background: rgba(255,255,255,.05); }
        .description { line-height: 1.45; }
    </style>
</head>
<body>
    <div class="box">
        <div class="name">{{ $page->npc->name }}</div>
        @if($page->npc->image)
            <div class="image">
                <img src="{{ $page->npc->image }}" alt="{{ $page->npc->name }}">
            </div>
        @endif
        <div class="description">{!! nl2br(e((string) $page->npc->description)) !!}</div>
    </div>
</body>
</html>
