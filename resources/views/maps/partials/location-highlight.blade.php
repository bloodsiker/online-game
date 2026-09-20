<style>
    .s2box.map-search-highlight {
        position: relative;
        z-index: 3;
        outline: 3px solid #ffd45c !important;
        box-shadow: 0 0 0 2px #8e3d0b, 0 0 11px 4px rgba(255, 188, 42, .92) !important;
        animation: map-search-highlight-pulse 1.15s ease-in-out infinite;
    }

    @keyframes map-search-highlight-pulse {
        0%, 100% {
            outline-color: #ffd45c;
            box-shadow: 0 0 0 2px #8e3d0b, 0 0 8px 2px rgba(255, 188, 42, .72);
        }
        50% {
            outline-color: #fff4a8;
            box-shadow: 0 0 0 2px #b65412, 0 0 15px 6px rgba(255, 205, 66, 1);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .s2box.map-search-highlight {
            animation: none;
        }
    }
</style>

<script>
    (function () {
        var locationId = new URLSearchParams(window.location.search).get('highlight_location');
        if (!locationId || !/^\d+$/.test(locationId)) {
            return;
        }

        var locationElement = document.getElementById('l' + locationId);
        if (!locationElement) {
            return;
        }

        var levelElement = document.getElementById('z' + locationId);
        if (levelElement && typeof zcurrent !== 'undefined' && typeof mapshow === 'function') {
            zcurrent = Number(levelElement.textContent) || 0;
            mapshow();
        }

        locationElement.classList.add('map-search-highlight');
        locationElement.title = 'Найденная локация №' + locationId;

        window.requestAnimationFrame(function () {
            locationElement.scrollIntoView({block: 'center', inline: 'center'});
        });
    })();
</script>
