{{--
    Общий скрипт фрейма карты — одинаков для всех карт, поэтому вынесен сюда
    вместо копии в каждом maps/*/frame.blade.php.

    Карта задаётся ТОЛЬКО разметкой; скрипт работает по id ячеек:
      u{id} — обёртка ячейки (подсветка текущей позиции игрока)
      l{id} — сама ячейка (пометки, обводка)
      z{id} — уровень (высота) ячейки
      m{n}  — таблица уровня n (n = zbmin..zbmax)

    Параметры:
      $zbmin, $zbmax — диапазон уровней карты (по умолчанию 0/0 — одноуровневая).
                       Сейчас все карты одноуровневые, механика уровней осталась
                       с исходного движка и работает вхолостую.

    Подключение:  @include('maps.partials.frame-script')
                  @include('maps.partials.frame-script', ['zbmin' => -1, 'zbmax' => 2])
--}}
@php
    $user = auth()->user();
    $zbmin = $zbmin ?? 0;
    $zbmax = $zbmax ?? 0;
@endphp

<script>
    var zbmin = {{ $zbmin }};
    var zbmax = {{ $zbmax }};

    var locInURL = document.location.href.split('#', 2);
    var currLocId = locInURL[1] !== undefined ? locInURL[1] : {{ $user?->location_id ?? 0 }};
    var lc = [], ma = [], prevlid = 0, prevlocation = 0, zcurrent = 0;
    var currentLocationElement = document.getElementById('z' + currLocId);

    if (currentLocationElement !== null) {
        zcurrent = currentLocationElement.innerHTML * 1;
        for (var i = zbmin; i <= zbmax; i++) {
            var n = i - zcurrent;
            document.getElementById('m' + (i - zbmin)).style.display = n === 0 ? '' : 'none';
        }
    }

    function refreshMap(lid) {
        try {
            ulocation(lid === undefined ? localStorage.getItem('lid') : lid);
        } catch (e) {
        }
    }

    @if($user)
        refreshMap(currLocId);
    @endif

    function gameFocus() {
        window.top.hero.focus();
    }

    function area_show(aid, show) {
        if (show === false || show === true || ma[aid] === undefined || ma[aid] === false) {
            var elements = document.getElementsByClassName('a' + aid);
            for (var i in elements) {
                if (elements[i].style !== undefined) {
                    elements[i].style.backgroundColor = show ? '#D1C1B4' : '';
                }
            }
        }
    }

    function area_click(aid) {
        for (var areaId in ma) {
            if (areaId != aid && ma[areaId]) {
                area_show(areaId, false);
                ma[areaId] = false;
            }
        }

        ma[aid] = ma[aid] === undefined || ma[aid] === false;
        area_show(aid, ma[aid]);
    }

    function mapshow() {
        for (var i = zbmin; i <= zbmax; i++) {
            var n = i - zcurrent;
            document.getElementById('m' + (i - zbmin)).style.display = n === 0 ? '' : 'none';
        }
    }

    function mark_l(lid, state) {
        var locationElement = document.getElementById('l' + lid);
        if (locationElement === null) {
            return;
        }

        if ((lc[lid] === undefined || lc[lid] === false) && state < 2) {
            locationElement.style.outline = state === 1 ? '4px dotted blue' : '';
        }

        if (state === 2) {
            lc[lid] = lc[lid] === undefined || lc[lid] === false;
            if (lc[lid]) {
                for (var locationId in lc) {
                    if (locationId != lid && lc[locationId]) {
                        document.getElementById('l' + locationId).style.outline = '';
                        lc[locationId] = false;
                    }
                }
            }

            locationElement.style.outline = lc[lid] ? '4px dotted blue' : '';
            locationElement.scrollIntoView({block: 'center', inline: 'center'});
        }
    }

    function map_get_lvl(lid) {
        for (var level = zbmin; level <= zbmax; level++) {
            try {
                var table = document.getElementById('m' + (level - zbmin));
                if (table) {
                    var elements = table.getElementsByClassName('s2box');
                    for (var i in elements) {
                        if (elements[i].id === 'l' + lid) {
                            return level;
                        }
                    }
                }
            } catch (e) {
            }
        }

        return zbmin;
    }

    function mark_lid(lid) {
        if (prevlid > 0) {
            var previousElement = document.getElementById('l' + prevlid);
            previousElement.classList.remove('an');
            previousElement.style.outline = '';
        }

        var locationElement = document.getElementById('l' + lid);
        locationElement.classList.add('an');
        locationElement.style.outline = '4px dotted OrangeRed';
        prevlid = lid;
    }

    {{--
        Переключатель уровня карты («+/- высота»). Вызывается только из
        resources/views/map3.blade.php; при zbmin == zbmax работает вхолостую.
        Ветка с 'map.php?m=' — наследие исходного движка, живых ссылок с
        непустым lnk в проекте нет.
    --}}
    function ms5(nn, lid, lnk) {
        if (lnk !== undefined && lnk !== '') {
            document.location.href = 'map.php?m=' + lnk + '#' + lid;
            return;
        }

        if (lid !== undefined && lid > 0) {
            mark_lid(lid);
        }

        if (nn > 0 && zcurrent + nn <= zbmax) {
            zcurrent += nn;
            mapshow();
        }

        if (nn < 0 && zcurrent + nn >= zbmin) {
            zcurrent += nn;
            mapshow();
        }
    }

    function ulocation(lid) {
        if (lid <= 0) {
            return;
        }

        if (prevlocation > 0) {
            document.getElementById('u' + prevlocation).className = '';
        }

        var locationElement = document.getElementById('u' + lid);
        if (locationElement === null) {
            {{-- Игрока нет на этой карте. На /on-map это штатный случай (попросили
                 карту по ?s=, а игрок не там) — возвращаем его на свою карту.
                 На превью-странице /mapN редирект не нужен: она открывается
                 именно чтобы посмотреть карту со стороны, поэтому просто не
                 подсвечиваем текущую позицию. --}}
            @if(request()->routeIs('on_map'))
                document.location.href = '{{ route('on_map', ['hide' => 1]) }}';
            @endif
            return;
        }

        zcurrent = map_get_lvl(lid);
        mapshow();
        locationElement.className = 'ulocation';
        prevlocation = lid;
        elementCenter(lid);
    }

    function elementCenter(lid) {
        document.getElementById('u' + lid).scrollIntoView({block: 'center', inline: 'center'});
    }

    function storeLid(lid) {
        try {
            localStorage.setItem('lid', lid);
        } catch (e) {
        }
    }

    function mark_lid2(lid) {
        var locationElement = document.getElementById('l' + lid);
        if (locationElement === null) {
            return;
        }

        locationElement.classList.add('an');
        locationElement.style.outline = '4px dotted OrangeRed';
        locationElement.scrollIntoView({block: 'center', inline: 'center'});
        prevlid = lid;
    }

    var locid = document.location.href.split('#', 2);
    if (locid[1] !== undefined) {
        var locIDs = locid[1].split('-');
        var lastLocationId = 0;
        for (var i in locIDs) {
            lastLocationId = locIDs[i];
            mark_lid2(lastLocationId);
        }
        zcurrent = map_get_lvl(lastLocationId);
        mapshow();
    }

    window.addEventListener('message', function (event) {
        var currentLocationId = event.data.currentLocationId;
        if (currentLocationId !== undefined) {
            storeLid(currentLocationId);
            ulocation(currentLocationId);
        }
    });
</script>