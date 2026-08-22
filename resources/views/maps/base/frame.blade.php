{{-- Базовый шаблон сетки для карт. Конкретные карты передают параметры через @include. --}}
@php
    $mapFirstColumn = $mapFirstColumn ?? 1;
    $mapRenderedColumns = $mapColumns - $mapFirstColumn;
    $mapEntryLinks = $mapEntryLinks ?? [];
    $mapLocations = require resource_path('data/maps/'.$mapDataFile);
@endphp

<style type="text/css">
    body {
        scrollbar-face-color: #FAE8D3;
        scrollbar-highlight-color: white;
        scrollbar-shadow-color: #D1A77F;
        scrollbar-3dlight-color: #FBDCCF;
        scrollbar-arrow-color: #AA968A;
        scrollbar-track-color: #EDD9C8;
        scrollbar-darkshadow-color: #AA968A;
    }

    html {
        scrollbar-color: #CBB8A7 #E6D6C5;
        scrollbar-width: thin;
    }

    body::-webkit-scrollbar {
        width: 7px;
        height: 7px;
    }

    body::-webkit-scrollbar-thumb {
        background: #CBB8A7;
        border-radius: 5px;
    }

    body::-webkit-scrollbar-thumb:hover {
        background: #AA968A;
    }

    body::-webkit-scrollbar-track,
    body::-webkit-scrollbar-corner {
        background: #E6D6C5;
        border-radius: 5px;
    }

    a:link,
    a:visited {
        color: black;
    }

    a:hover {
        color: #FF8000;
    }

    a:active {
        color: yellow;
        background-color: black;
    }

    .br {
        border-right: 1px solid #AD998C;
    }

    .bt {
        border-top: 1px solid #AD998C;
    }

    .bl {
        border-left: 1px solid #AD998C;
    }

    .bb {
        border-bottom: 1px solid #AD998C;
    }

    .s2box {
        border-spacing: 0;
        box-sizing: border-box;
        display: table-cell;
        font-size: 9px;
        height: 48px;
        margin: 0;
        padding: 0;
        position: relative;
        text-align: center;
        vertical-align: middle;
        width: 48px;
    }

    .s2box s {
        display: none;
    }

    .ulocation {
        animation: ula 1s linear infinite;
        background-color: rgba(181, 147, 135, 0.3);
        border-radius: 50%;
        box-shadow: inset 0 0 0 4px #B59387;
        display: inline-block;
    }

    .alvl {
        z-index: 2;
    }

    .awest {
        font-size: 18px;
        left: -12px;
        position: absolute;
        text-decoration: none;
        top: 10px;
        z-index: 1;
    }

    .anorth {
        font-size: 18px;
        left: 19px;
        position: absolute;
        text-decoration: none;
        top: -15px;
        z-index: 1;
    }

    .asouth {
        bottom: -12px;
        font-size: 18px;
        left: 19px;
        position: absolute;
        text-decoration: none;
        z-index: 1;
    }

    .aeast {
        font-size: 18px;
        position: absolute;
        right: -12px;
        text-decoration: none;
        top: 10px;
        z-index: 1;
    }

    .anorth:hover,
    .asouth:hover,
    .aeast:hover,
    .awest:hover {
        text-decoration: none;
    }

    .an {
        animation: borderAnimation 1s linear infinite;
    }

    .maptable th {
        font-size: 14px;
        padding: 5px;
    }

    .maptable a {
        text-decoration: none;
    }

    .maptable {
        background-color: rgba(250, 233, 218, 0.5);
        border: 1px solid #CEBBAA;
        border-collapse: separate;
        border-spacing: 1px;
        box-sizing: padding-box;
        empty-cells: hide;
        margin: 0;
        padding: 0;
    }

    .a{{ $mapAreaId }} {
        background-color: rgba(210, 240, 185, 0.6);
        border-spacing: 0;
        margin: 0;
        padding: 0;
    }
    .listloc {
        border: 1px solid black;
        width: 32px;
        vertical-align: middle;
        display: inline-block;
        text-align: center;
        margin: 0px;
        font-size: 10px;
        cursor: pointer;
        cursor: hand;
    }
</style>

<table width="{{ ($mapRenderedColumns + 1) * 48 }}" cellspacing="1" cellpadding="0" id="m0" class="maptable">
    <tbody>
    <tr style="@if(request()->has('hide')) display: none; @endif">
        <th colspan="{{ $mapRenderedColumns }}" class="t0" align="left"></th>
    </tr>
    @for($row = 0; $row < $mapRows; $row++)
        <tr>
            @for($column = $mapFirstColumn; $column < $mapColumns; $column++)
                @php
                    $cell = $mapLocations[$row][$column] ?? null;
                @endphp
                <td width="48" height="48">
                    @if($cell)
                        @php
                            [$locationId, $borderClasses] = $cell;
                        @endphp
                        <div class="a{{ $mapAreaId }}">
                            <div id="u{{ $locationId }}">
                                <div id="l{{ $locationId }}" class="s2box {{ $borderClasses }}">
                                    <s id="z{{ $locationId }}">0</s>
                                    @foreach($mapEntryLinks as $mapEntryLink)
                                        @if($locationId === (int) $mapEntryLink['locationId'])
                                            <a class="{{ $mapEntryLink['class'] }}" href="{{ route('on_map', array_merge(['s' => $mapEntryLink['targetSlug']], request()->except(['s']))) }}#{{ $mapEntryLink['targetLocationId'] }}">{{ $mapEntryLink['arrow'] }}</a>
                                        @endif
                                    @endforeach
                                    {{ $locationId }}
                                </div>
                            </div>
                        </div>
                    @endif
                </td>
            @endfor
        </tr>
    @endfor
    <tr>
        <th colspan="{{ $mapRenderedColumns }}" class="t0" align="left"></th>
    </tr>
    </tbody>
</table>

@include('maps.partials.frame-script')
