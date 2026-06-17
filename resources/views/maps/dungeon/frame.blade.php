@php
use App\Modules\Dungeon\Infrastructure\Persistence\Models\Dungeon;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonGate;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;

$user      = auth()->user();
$curLoc    = $user?->currentLocation;
$dungeonId = $curLoc?->dungeon_id;

$dungeon   = $dungeonId ? Dungeon::find($dungeonId) : null;
$locations = collect();
$grid      = [];
$positions = [];
$gated     = []; // "fromId-toId"
$maxCol    = 0;
$maxRow    = 0;
$visibleCols = 17;
$leftPad = 0;

if ($dungeon && $dungeon->first_location_id) {
    $locations = Location::where('dungeon_id', $dungeon->id)->get()->keyBy('id');

    foreach (DungeonGate::where('dungeon_id', $dungeon->id)->get() as $gate) {
        $gated[$gate->from_location_id . '-' . $gate->to_location_id] = true;
    }

    // BFS to assign (col, row) positions
    $positions = [];
    $visited   = [];
    $queue     = [[$dungeon->first_location_id, 0, 0]];
    $dirMap    = ['east' => [1, 0], 'west' => [-1, 0], 'north' => [0, -1], 'south' => [0, 1]];

    while (!empty($queue)) {
        [$id, $col, $row] = array_shift($queue);
        if (isset($visited[$id])) continue;
        $visited[$id]   = true;
        $positions[$id] = [$col, $row];

        $l = $locations[$id] ?? null;
        if (!$l) continue;

        foreach ($dirMap as $dir => [$dc, $dr]) {
            $nId = $l->$dir;
            if ($nId && !isset($visited[$nId]) && isset($locations[$nId])) {
                $queue[] = [$nId, $col + $dc, $row + $dr];
            }
        }
    }

    // Normalize to (0, 0)
    if (!empty($positions)) {
        $allCols = array_column($positions, 0);
        $allRows = array_column($positions, 1);
        $minCol  = min($allCols);
        $minRow  = min($allRows);
        $maxCol  = max($allCols) - $minCol;
        $maxRow  = max($allRows) - $minRow;

        foreach ($positions as $id => [$col, $row]) {
            $nc = $col - $minCol;
            $nr = $row - $minRow;
            $positions[$id] = [$nc, $nr];
            $grid[$nr][$nc] = $id;
        }

        $visibleCols = max(17, $maxCol + 1);
        $leftPad = intdiv($visibleCols - ($maxCol + 1), 2);
    }
}
@endphp

<style type="text/css">
    <!--
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

    body::-webkit-scrollbar { width: 7px; height: 7px; }
    body::-webkit-scrollbar-thumb { background: #CBB8A7; border-radius: 5px; }
    body::-webkit-scrollbar-thumb:hover { background: #AA968A; }
    body::-webkit-scrollbar-track,
    body::-webkit-scrollbar-corner { background: #E6D6C5; border-radius: 5px; }

    a:link, a:visited { color: black; }
    a:hover { color: #FF8000; }
    A.wt:link, A.wt:visited { color: white; }
    A.wt:hover { color: #aaaaaa; background-color: #666666; }
    A.b:link, A.b:visited { color: blue; }
    a.b:hover { color: #FF8000; }
    A.r:hover { color: #FF8000; }
    a:active { color: yellow; background-color: black; }

    .br { border-right: 1px solid #AD998C; }
    .bt { border-top: 1px solid #AD998C; }
    .bl { border-left: 1px solid #AD998C; }
    .bb { border-bottom: 1px solid #AD998C; }

    .s2box {
        border-spacing: 0;
        font-size: 9px;
        text-align: center;
        width: 48px;
        height: 48px;
        position: relative;
        display: table-cell;
        margin: 0;
        padding: 0;
        vertical-align: middle;
        box-sizing: border-box;
    }
    .s2box s { display: none; }

    .ulocation {
        animation: ula 1s linear infinite;
        border-radius: 50%;
        box-shadow: inset 0 0 0 4px #B59387;
        background-color: rgba(181, 147, 135, 0.3);
        display: inline-block;
    }

    .maplegend {
    }

    @keyframes ula {
        0%   { box-shadow: inset 0 0 0 4px #B59387; }
        50%  { box-shadow: inset 0 0 0 4px #D1A77F; }
        100% { box-shadow: inset 0 0 0 4px #B59387; }
    }

    .s2box cite {
        margin: 14px 0 0 -3px;
        font-style: normal;
        position: absolute;
        z-index: 10;
        display: none;
    }
    .s2box:hover cite {
        white-space: nowrap;
        font-weight: normal;
        font-size: 10px;
        color: #706258;
        display: inline;
        background-color: #FFF8EA;
        border: 1px outset #CEBBAA;
        padding: 10px;
    }
    .s2box em {
        position: relative;
        font-style: normal;
        font-weight: normal;
        opacity: 0.80;
        display: inline-block;
        margin: 0;
        min-width: 20px;
        border: 1px outset #AD998C;
        padding: 3px;
        color: black;
    }
    .s2box em::after {
        content: "?";
        color: #9C8D84;
        top: -4px;
        font-size: smaller;
        position: absolute;
    }
    .listloc {
        border: 1px solid black;
        width: 32px;
        vertical-align: middle;
        display: inline-block;
        text-align: center;
        margin: 0;
        font-size: 10px;
        cursor: pointer;
        cursor: hand;
    }
    .alvl { z-index: 2; }
    .anorth {
        position: absolute; left: 19px; top: -15px;
        font-size: 18px; text-decoration: none; z-index: 1;
    }
    .asouth {
        position: absolute; left: 19px; bottom: -12px;
        font-size: 18px; text-decoration: none; z-index: 1;
    }
    .awest {
        position: absolute; left: -12px; top: 10px;
        font-size: 18px; text-decoration: none; z-index: 1;
    }
    .aeast {
        position: absolute; right: -12px; top: 10px;
        font-size: 18px; text-decoration: none; z-index: 1;
    }
    .anorth:hover, .awest:hover, .asouth:hover, .aeast:hover { text-decoration: none; }
    .an { animation: borderAnimation 1s linear infinite; }

    .maptable th {
        padding: 5px;
        font-size: 14px;
    }

    .maptable a {
        text-decoration: none;
    }

    .maptable {
        box-sizing: padding-box;
        empty-cells: hide;
        border-collapse: separate;
        background-color: rgba(250, 233, 218, 0.5);
        padding: 0;
        margin: 0;
        border: 1px solid #CEBBAA;
    }
    -->
</style>

@if($dungeon)

<table width="{{ $visibleCols * 49 }}" cellspacing="1" cellpadding="0" id="m0" class="maptable">
<tbody>
<tr style=" @if(request()->has('hide')) display: none; @endif">
    <th colspan="{{ $visibleCols }}" class="t0" align="left" style="padding:5px;font-size:14px;"></th>
</tr>
@for($r = 0; $r <= $maxRow; $r++)
<tr>
@for($c = 0; $c < $visibleCols; $c++)
@php
    $gridCol = $c - $leftPad;
@endphp
@if(isset($grid[$r][$gridCol]))
@php
    $locId = $grid[$r][$gridCol];
    $l     = $locations[$locId] ?? null;

    $borders = [];
    if ($l && $l->north) $borders[] = 'bt';
    if ($l && $l->south) $borders[] = 'bb';
    if ($l && $l->west)  $borders[] = 'bl';
    if ($l && $l->east)  $borders[] = 'br';
    $borderClass = implode(' ', $borders);

    $isExit = $dungeon->exit_location_id === (int)$locId;
    $isBoss = $l && $l->count_monster >= 4 && !$isExit;

    $hasEastGate  = $l && $l->east  && isset($gated[$locId . '-' . $l->east]);
    $hasSouthGate = $l && $l->south && isset($gated[$locId . '-' . $l->south]);
    $hasNorthGate = $l && $l->north && isset($gated[$l->north . '-' . $locId]);
    $hasWestGate  = $l && $l->west  && isset($gated[$l->west  . '-' . $locId]);
@endphp
<td width="48" height="48">
    <div class="a1" style="">
        <div id="u{{ $locId }}">
            <div id="l{{ $locId }}" class="s2box {{ $borderClass }}">
                <s id="z{{ $locId }}">0</s>
                <em>{{ $locId }}</em>
                <cite>
                    {{ $l->name ?? '' }}
                    @if($hasEastGate || $hasSouthGate || $hasNorthGate || $hasWestGate) [Ворота] @endif
                    @if($isExit) [Выход] @endif
                    @if($isBoss) [Босс] @endif
                </cite>
                @if($l && $l->north)<a href="#{{ $l->north }}" class="anorth">↑</a>@endif
                @if($l && $l->south)<a href="#{{ $l->south }}" class="asouth">↓</a>@endif
                @if($l && $l->west)<a href="#{{ $l->west }}" class="awest">←</a>@endif
                @if($l && $l->east)<a href="#{{ $l->east }}" class="aeast">→</a>@endif
            </div>
        </div>
    </div>
</td>
@else
<td width="48" height="48"></td>
@endif
@endfor
</tr>
@endfor
</tbody>
</table>

@else
<p style="color:#706258; text-align:center; padding:20px;">Вы не в подземелье</p>
@endif

<script>
    var zbmax = 0;
    var zbmin = 0;
</script>

<script>
    @php $authUser = auth()->user(); @endphp

    var locInURL  = document.location.href.split('#', 2);
    var currLocId = locInURL[1] !== undefined ? locInURL[1] : {{ $authUser?->location_id ?? 0 }};

    var lc = [], ma = [], prevlid = 0, prevlocation = 0, zcurrent = 0;

    var zEl = document.getElementById('z' + currLocId);
    if (zEl) zcurrent = parseInt(zEl.innerHTML) || 0;

    function refreshMap(lid) {
        try { ulocation(lid == undefined ? localStorage.getItem('lid') : lid); } catch(e) {}
    }

    refreshMap(currLocId);

    function mark_l(lid, s) {
        var el = document.getElementById('l' + lid);
        if (!el) return;
        if ((lc[lid] == undefined || lc[lid] == false) && s < 2)
            el.style.outline = s == 1 ? '4px dotted #B59387' : '';
        if (s == 2) {
            lc[lid] = !lc[lid];
            if (lc[lid]) {
                for (var i in lc) if (i != lid && lc[i]) {
                    var e2 = document.getElementById('l' + i);
                    if (e2) e2.style.outline = '';
                    lc[i] = false;
                }
            }
            el.style.outline = lc[lid] ? '4px dotted #B59387' : '';
            el.scrollIntoView({ block: 'center', inline: 'center' });
        }
    }

    function mark_lid(lid) {
        var prev = document.getElementById('l' + prevlid);
        if (prev) { prev.className = prev.className.replace(' an', ''); prev.style.outline = ''; }
        var cur = document.getElementById('l' + lid);
        if (cur) { cur.className += ' an'; cur.style.outline = '4px dotted OrangeRed'; }
        prevlid = lid;
    }

    function ulocation(lid) {
        if (!lid || lid <= 0) return;
        var prevEl = document.getElementById('u' + prevlocation);
        if (prevEl) prevEl.className = prevEl.className.replace('ulocation', '').trim();

        var el = document.getElementById('u' + lid);
        if (!el) {
            document.location.href = '{{ route('on_map', ['hide' => 1]) }}';
            return;
        }
        el.className = (el.className + ' ulocation').trim();
        prevlocation = lid;
        el.scrollIntoView({ block: 'center', inline: 'center' });
    }

    function storeLid(lid) {
        try { localStorage.setItem('lid', lid); } catch(e) {}
    }

    window.addEventListener('message', function(event) {
        const { currentLocationId } = event.data;
        if (currentLocationId !== undefined) {
            storeLid(currentLocationId);
            ulocation(currentLocationId);
        }
    });
</script>
