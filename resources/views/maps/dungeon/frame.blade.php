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
    }
}
@endphp

<style type="text/css">
    <!--
    body {
        scrollbar-color: #4a3060 #1a0a2e;
        scrollbar-width: thin;
    }
    body::-webkit-scrollbar { width: 7px; height: 7px; }
    body::-webkit-scrollbar-thumb { background: #5a3a80; border-radius: 5px; }
    body::-webkit-scrollbar-track { background: #1a0a2e; }
    a:link, a:visited { color: #c0a0e0; }
    a:hover { color: #FF8000; }
    a:active { color: yellow; background-color: black; }

    .br { border-right: 1px solid #6a3a9a; }
    .bt { border-top: 1px solid #6a3a9a; }
    .bl { border-left: 1px solid #6a3a9a; }
    .bb { border-bottom: 1px solid #6a3a9a; }

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
        color: #d0b0f0;
    }
    .s2box s { display: none; }

    .ulocation {
        animation: ula 1s linear infinite;
        border-radius: 50%;
        box-shadow: inset 0 0 0 4px #9966cc;
        background-color: rgba(153, 102, 204, 0.3);
        display: inline-block;
    }

    @keyframes ula {
        0%   { box-shadow: inset 0 0 0 4px #9966cc; }
        50%  { box-shadow: inset 0 0 0 4px #cc88ff; }
        100% { box-shadow: inset 0 0 0 4px #9966cc; }
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
        color: #d0b0f0;
        display: inline;
        background-color: #1a0a2e;
        border: 1px outset #5a3a80;
        padding: 8px;
    }
    .s2box em {
        position: relative;
        font-style: normal;
        font-weight: normal;
        opacity: 0.90;
        display: inline-block;
        margin: 0;
        min-width: 20px;
        border: 1px outset #6a3a9a;
        padding: 3px;
        color: #d0b0f0;
        background-color: rgba(40, 10, 70, 0.6);
    }
    .listloc {
        border: 1px solid #6a3a9a;
        width: 32px;
        vertical-align: middle;
        display: inline-block;
        text-align: center;
        margin: 0;
        font-size: 10px;
        cursor: pointer;
    }
    .anorth {
        position: absolute; left: 14px; top: -13px;
        font-size: 14px; text-decoration: none; z-index: 1;
    }
    .asouth {
        position: absolute; left: 14px; bottom: -10px;
        font-size: 14px; text-decoration: none; z-index: 1;
    }
    .awest {
        position: absolute; left: -10px; top: 12px;
        font-size: 14px; text-decoration: none; z-index: 1;
    }
    .aeast {
        position: absolute; right: -10px; top: 12px;
        font-size: 14px; text-decoration: none; z-index: 1;
    }
    .anorth:hover, .awest:hover, .asouth:hover, .aeast:hover { text-decoration: none; }
    .an { animation: borderAnimation 1s linear infinite; }

    .maptable {
        box-sizing: padding-box;
        empty-cells: hide;
        border-collapse: separate;
        background-color: rgba(20, 5, 40, 0.8);
        padding: 0;
        margin: 0;
        border: 1px solid #5a3a80;
    }
    .maptable a { text-decoration: none; }

    /* Zone colours */
    .a-z1  { background-color: rgba(60, 30, 90, 0.7); }
    .a-z2  { background-color: rgba(30, 10, 70, 0.7); }
    .a-z3  { background-color: rgba(80, 10, 30, 0.7); }
    .a-exit { background-color: rgba(10, 60, 30, 0.7); }

    .gate-ico { color: #cc2222; font-size: 11px; }
    .loc-boss em { border-color: #cc2222 !important; color: #ff6666 !important; }
    .loc-exit em { border-color: #22aa55 !important; color: #44ff88 !important; }
    -->
</style>

@if($dungeon)

@php
    // Zone BFS: assign zones by DungeonGate boundaries
    $zoneMap    = [];
    $z          = 1;
    $frontier   = [$dungeon->first_location_id];
    $allVisited = [];
    while (!empty($frontier) && $z <= 3) {
        $next  = [];
        $inner = $frontier;
        while (!empty($inner)) {
            $id = array_shift($inner);
            if (isset($allVisited[$id])) continue;
            $allVisited[$id] = true;
            $zoneMap[$id]    = $z;

            $l = $locations[$id] ?? null;
            if (!$l) continue;

            foreach (['east', 'west', 'north', 'south'] as $dir) {
                $nId = $l->$dir;
                if (!$nId || !isset($locations[$nId]) || isset($allVisited[$nId])) continue;
                if (isset($gated[$id . '-' . $nId])) {
                    $next[] = $nId;
                } else {
                    $inner[] = $nId;
                }
            }
        }
        $frontier = $next;
        $z++;
    }
@endphp

<p style="color:#c0a0e0; font-size:11px; margin:0 0 4px 0; text-align:center;">
    <b>{{ $dungeon->name }}</b>
    @if($dungeon->time_limit_seconds)
        &nbsp;— {{ round($dungeon->time_limit_seconds / 60) }} мин.
    @endif
    @if(!$dungeon->monster_respawn)
        &nbsp;| Мобы не респят
    @endif
</p>

<table cellspacing="1" cellpadding="0" id="m0" class="maptable">
<tbody>
@for($r = 0; $r <= $maxRow; $r++)
<tr>
@for($c = 0; $c <= $maxCol; $c++)
@if(isset($grid[$r][$c]))
@php
    $locId = $grid[$r][$c];
    $l     = $locations[$locId] ?? null;

    $borders = [];
    if ($l && $l->north) $borders[] = 'bt';
    if ($l && $l->south) $borders[] = 'bb';
    if ($l && $l->west)  $borders[] = 'bl';
    if ($l && $l->east)  $borders[] = 'br';
    $borderClass = implode(' ', $borders);

    $zone      = $zoneMap[$locId] ?? 1;
    $zoneClass = 'a-z' . $zone;

    $isExit = $dungeon->exit_location_id === (int)$locId;
    $isBoss = $l && $l->count_monster >= 4 && $zone === 3 && !$isExit;

    $locClass  = $isBoss ? 'loc-boss' : ($isExit ? 'loc-exit' : '');
    $zoneClass = $isExit ? 'a-exit' : $zoneClass;

    $hasEastGate  = $l && $l->east  && isset($gated[$locId . '-' . $l->east]);
    $hasSouthGate = $l && $l->south && isset($gated[$locId . '-' . $l->south]);
    $hasNorthGate = $l && $l->north && isset($gated[$l->north . '-' . $locId]);
    $hasWestGate  = $l && $l->west  && isset($gated[$l->west  . '-' . $locId]);
@endphp
<td width="48" height="48">
    <div class="{{ $zoneClass }}">
        <div id="u{{ $locId }}">
            <div id="l{{ $locId }}" class="s2box {{ $borderClass }} {{ $locClass }}">
                <s id="z{{ $locId }}">0</s>
                @if($isExit)
                    <em title="Выход">⬆</em>
                @elseif($isBoss)
                    <em title="Босс">☠</em>
                @else
                    <em>{{ $locId }}</em>
                @endif
                <cite>
                    {{ $l->name ?? '' }}
                    @if($hasEastGate || $hasSouthGate || $hasNorthGate || $hasWestGate) ⚷ [Ворота] @endif
                    @if($isExit) — ВЫХОД @endif
                    @if($isBoss) — БОСС @endif
                </cite>
                @if($l && $l->north)<a href="#{{ $l->north }}" class="anorth">↑</a>@endif
                @if($l && $l->south)<a href="#{{ $l->south }}" class="asouth">↓</a>@endif
                @if($l && $l->west)<a href="#{{ $l->west }}" class="awest">←</a>@endif
                @if($l && $l->east)<a href="#{{ $l->east }}" class="aeast">→</a>@endif
                @if($hasEastGate)<span class="aeast gate-ico">🔒</span>@endif
                @if($hasSouthGate)<span class="asouth gate-ico">🔒</span>@endif
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
<p style="color:#c0a0e0; text-align:center; padding:20px;">Вы не в подземелье</p>
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
            el.style.outline = s == 1 ? '4px dotted #9966cc' : '';
        if (s == 2) {
            lc[lid] = !lc[lid];
            if (lc[lid]) {
                for (var i in lc) if (i != lid && lc[i]) {
                    var e2 = document.getElementById('l' + i);
                    if (e2) e2.style.outline = '';
                    lc[i] = false;
                }
            }
            el.style.outline = lc[lid] ? '4px dotted #9966cc' : '';
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
