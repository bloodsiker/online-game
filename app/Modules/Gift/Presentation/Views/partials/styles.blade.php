<style>
    /* Плитка — как у артефактов в информации игрока (.profile-artifact): 72×71 с наградной рамкой. */
    .player-gift { position: relative; display: inline-block; width: 72px; height: 71px; vertical-align: middle; cursor: pointer; }
    .player-gift img { position: absolute; top: 5px; left: 6px; width: 60px; height: 60px; object-fit: contain; border: 0; }
    .player-gift::after { content: ''; position: absolute; inset: 0; background: url({{ asset('main/images/user-reward-frame.png') }}) center / 72px 71px no-repeat; pointer-events: none; }
    .player-gifts__all { padding: 4px 0 0; text-align: center; font-size: 11px; }
    .gifts-page .player-gifts { text-align: center; }
    .player-gifts__all a { color: #8b2000; font-weight: bold; }
</style>
