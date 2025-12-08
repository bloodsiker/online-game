<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map with HTML Table</title>
    <style>
        table.maptable {
            border-collapse: collapse;
        }

        td {
            width: 45px;
            height: 45px;
            border: 1px dotted #cccccc;
            text-align: center;
            position: relative;
            vertical-align: middle;
            background: #f4fdee;
        }

        .anorth::after {
            content: "↑";
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
        }

        .asouth::after {
            content: "↓";
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }

        .aeast::after {
            content: "→";
            position: absolute;
            right: -10px;
            top: 50%;
            transform: translateY(-50%);
        }

        .awest::after {
            content: "←";
            position: absolute;
            left: -10px;
            top: 50%;
            transform: translateY(-50%);
        }
    </style>
</head>
<body>

<table class="maptable">
    @php
        $maxX = $locations->max('coordinate_x');
        $maxY = $locations->max('coordinate_y');
        $locationGrid = [];

        // Создаем двумерный массив для хранения локаций по координатам
        foreach ($locations as $location) {
            $locationGrid[$location->coordinate_y][$location->coordinate_x] = $location;
        }
    @endphp

    @for ($y = 0; $y <= $maxY; $y++)
        <tr>
            @for ($x = 0; $x <= $maxX; $x++)
                <td>
                    @if (isset($locationGrid[$y][$x]))
                        @php
                            $location = $locationGrid[$y][$x];
                        @endphp
                        <div style="height: 45px; width: 45px; background: rgba(255,235,235,0.6);
                        @if(!$location->north)border-top: 1px solid #AD998C; @endif
                        @if(!$location->south)border-bottom: 1px solid #AD998C; @endif
                        @if(!$location->east)border-right: 1px solid #AD998C; @endif
                        @if(!$location->west)border-left: 1px solid #AD998C; @endif
                        ">
                            {{ $location->id }}
                        </div>
                    @endif
                </td>
            @endfor
        </tr>
    @endfor
</table>

</body>
</html>
