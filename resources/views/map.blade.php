<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Map</title>
    <style>
        .map {
            display: grid;
            /*grid-template-columns: repeat(10, 50px); !* Измените количество колонок на нужное вам *!*/
            /*grid-auto-rows: 50px;*/
            gap: 0px;
            justify-content: center;
            margin-top: 20px;
        }

        .location {
            width: 45px;
            height: 45px;
            background-color: rgba(255,235,235,0.6);
            /*border: 1px solid #ad998c;*/
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            font-size: 12px;

            flex-flow: column;
        }

        /* Стрелки для направлений */
        .anorth::before {
            content: "↑";
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
        }

        .asouth::before {
            content: "↓";
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
        }

        .aeast::before {
            content: "→";
            position: absolute;
            right: -15px;
            top: 50%;
            transform: translateY(-50%);
        }

        .awest::before {
            content: "←";
            position: absolute;
            left: -15px;
            top: 50%;
            transform: translateY(-50%);
        }
    </style>
</head>
<body>

<div class="map">
    @foreach ($locations as $location)
        <div class="location" style="grid-column: {{ $location->coordinate_x }}; grid-row: {{ $location->coordinate_y }};
        @if(!$location->north)border-top: 1px solid #AD998C; @endif
        @if(!$location->south)border-bottom: 1px solid #AD998C; @endif
        @if(!$location->east)border-right: 1px solid #AD998C; @endif
        @if(!$location->west)border-left: 1px solid #AD998C; @endif
        ">
            <div>
                <span>{{ $location->id }}</span>
            </div>
            <div>
                <small>{{ $location->coordinate_x }} / {{ $location->coordinate_y }}</small>
            </div>

            <!-- Проверяем наличие переходов и отображаем стрелки -->
{{--            @if ($location->north)--}}
{{--                <a href="?location={{ $location->north }}" class="anorth"></a>--}}
{{--            @endif--}}
{{--            @if ($location->south)--}}
{{--                <a href="?location={{ $location->south }}" class="asouth"></a>--}}
{{--            @endif--}}
{{--            @if ($location->east)--}}
{{--                <a href="?location={{ $location->east }}" class="aeast"></a>--}}
{{--            @endif--}}
{{--            @if ($location->west)--}}
{{--                <a href="?location={{ $location->west }}" class="awest"></a>--}}
{{--            @endif--}}
        </div>
    @endforeach
</div>

</body>
</html>
