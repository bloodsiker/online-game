<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Карты мира</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html, body { min-height: 100%; }
        body { margin: 0; padding: 10px; background: transparent; color: #321b0c; font: 11px Tahoma, sans-serif; }
        .map-tree-page { max-width: 850px; margin: 0 auto; }
        .map-tree-header { padding: 12px; border: 1px solid #b78358; border-radius: 4px 4px 0 0; background: linear-gradient(90deg, rgba(239, 210, 168, .9), rgba(199, 132, 74, .78)), url('{{ asset('img/bg/tbl-usi_bg.gif') }}') repeat; color: #3c1808; text-align: center; text-shadow: 0 1px 0 #ffe8bd; box-shadow: inset 0 -1px 0 rgba(255, 239, 205, .5); }
        .map-tree-header h1 { margin: 0; font-size: 14px; }
        .map-tree-stats { display: flex; justify-content: center; gap: 5px; margin: 6px 0 0; }
        .map-tree-stat { display: inline-flex; align-items: center; min-height: 16px; padding: 4px 7px; border: 1px solid #ba8c5d; border-radius: 3px; background: linear-gradient(#fff1ce, #e6c99f); color: #4a260e; box-shadow: 0 1px 1px rgba(70, 36, 10, .22); font-weight: bold; text-shadow: none; }
        .map-tree { margin: 0; padding: 13px 13px 13px 28px; border: 1px solid #a8784d; border-top: 0; background: #ead7bc url('{{ asset('img/bg/bgg2.gif') }}') repeat; list-style: none; }
        .map-tree ul { position: relative; margin: 3px 0 3px 13px; padding: 0 0 0 18px; list-style: none; }
        .map-tree li { position: relative; margin: 7px 0; }
        .map-tree ul > li::before { position: absolute; top: 13px; left: -18px; width: 18px; border-top: 1px solid #a4744e; content: ''; }
        .map-tree ul > li::after { position: absolute; top: -7px; right: auto; bottom: -7px; left: -18px; border-left: 1px solid #a4744e; content: ''; }
        .map-tree ul > li:last-child::after { bottom: auto; height: 20px; }
        .map-node { display: inline-flex; align-items: center; min-height: 16px; padding: 5px 8px; border: 1px solid #ba8c5d; border-radius: 3px; background: linear-gradient(#fff1ce, #e6c99f); color: #4a260e; box-shadow: 0 1px 1px rgba(70, 36, 10, .22); font-weight: bold; text-decoration: none; }
        .map-node:hover { border-color: #7b451e; background: linear-gradient(#fff7df, #e9b970); color: #6b2d0b; }
        .map-node--current { border-color: #658c31; background: linear-gradient(#eff7c9, #bad577); color: #345016; box-shadow: inset 0 0 0 1px #f9ffe0, 0 0 5px rgba(91, 139, 37, .65); }
        .map-node--current:hover { border-color: #4b741f; background: linear-gradient(#f6ffd8, #c9e586); color: #294212; }
        .map-monsters-button, .map-resources-button { display: inline-flex; align-items: center; justify-content: center; min-width: 26px; min-height: 26px; margin-left: 4px; padding: 2px 5px; border: 1px solid #a96d3a; border-radius: 3px; background: linear-gradient(#ffe5ae, #d9994d); color: #542506; cursor: pointer; font: 16px/1 Georgia, serif; text-shadow: 0 1px #fff0c5; vertical-align: middle; }
        .map-monsters-button:hover, .map-resources-button:hover { border-color: #754014; background: linear-gradient(#fff0c2, #e9ad59); color: #321403; }
        .map-resources-button img { width: 17px; height: 17px; object-fit: contain; }
        .map-tree-empty { padding: 18px; border: 1px solid #a8784d; border-top: 0; background: #ead7bc; text-align: center; }
    </style>
</head>
<body>
<main class="map-tree-page">
    <header class="map-tree-header">
        <h1>Карты мира</h1>
        <div class="map-tree-stats">
            <span class="map-tree-stat">{{ $page->mapsCount }} карт</span>
            <span class="map-tree-stat">{{ $page->locationsCount }} локаций</span>
        </div>
    </header>

    @if($page->roots !== [])
        <ul class="map-tree">
            @foreach($page->roots as $node)
                @include('location::partials.map-tree-node', ['node' => $node])
            @endforeach
        </ul>
    @else
        <div class="map-tree-empty">Карты пока не созданы.</div>
    @endif
</main>

<script>
    (function () {
        const modalHost = window.parent;

        document.addEventListener('click', function (event) {
            const button = event.target.closest('.map-monsters-button');
            if (button) {
                if (typeof modalHost.openMapMonstersModal !== 'function') {
                    return;
                }

                modalHost.openMapMonstersModal({map: button.dataset.mapName});

                fetch(button.dataset.monstersUrl, {headers: {'Accept': 'application/json'}})
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Не удалось загрузить список монстров.');
                        }

                        return response.json();
                    })
                    .then(function (data) {
                        modalHost.openMapMonstersModal({map: data.map, monsters: data.monsters || []});
                    })
                    .catch(function () {
                        modalHost.openMapMonstersModal({map: button.dataset.mapName, error: 'Не удалось загрузить список монстров.'});
                    });
            }

            const resourcesButton = event.target.closest('.map-resources-button');
            if (resourcesButton) {
                if (typeof modalHost.openMapResourcesModal !== 'function') {
                    return;
                }

                modalHost.openMapResourcesModal({map: resourcesButton.dataset.mapName});

                fetch(resourcesButton.dataset.resourcesUrl, {headers: {'Accept': 'application/json'}})
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Не удалось загрузить список ресурсов.');
                        }

                        return response.json();
                    })
                    .then(function (data) {
                        modalHost.openMapResourcesModal({map: data.map, resources: data.resources || []});
                    })
                    .catch(function () {
                        modalHost.openMapResourcesModal({map: resourcesButton.dataset.mapName, error: 'Не удалось загрузить список ресурсов.'});
                    });
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && typeof modalHost.closeMapCatalogModal === 'function') {
                modalHost.closeMapCatalogModal();
            }
        });
    })();
</script>
</body>
</html>
