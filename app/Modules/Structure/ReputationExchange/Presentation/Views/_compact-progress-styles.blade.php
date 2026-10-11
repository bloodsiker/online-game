<style>
    .rep-progress-bar--small { display: inline-block; position: relative; width: 220px; height: 17px; margin-left: 7px; vertical-align: middle; }
    .rep-progress-bar--small .rep-progress-bar__bg { position: absolute; inset: 0 3px; height: 17px; overflow: hidden; background: url('{{ asset('img/bg/progress-bar.png') }}') 0 -51px repeat-x; }
    .rep-progress-bar--small .rep-progress-bar__fill { height: 17px; background: url('{{ asset('img/bg/progress-bar.png') }}') 0 -68px repeat-x; }
    .rep-progress-bar--small .rep-progress-bar__border { position: absolute; inset: 0; pointer-events: none; }
    .rep-progress-bar--small .rep-progress-bar__border-left,
    .rep-progress-bar--small .rep-progress-bar__border-right,
    .rep-progress-bar--small .rep-progress-bar__border-center { position: absolute; top: 0; height: 17px; background: url('{{ asset('img/bg/progress-bar.png') }}') no-repeat; }
    .rep-progress-bar--small .rep-progress-bar__border-left { left: 0; width: 20px; background-position: 0 -17px; }
    .rep-progress-bar--small .rep-progress-bar__border-right { right: 0; width: 20px; background-position: 0 -34px; }
    .rep-progress-bar--small .rep-progress-bar__border-center { left: 20px; right: 20px; background-position: 0 0; background-repeat: repeat-x; }
    .rep-progress-bar--small .rep-progress-bar__text { position: absolute; inset: 0; color: #fff; font-size: 10px; font-weight: bold; line-height: 17px; text-align: center; text-shadow: 0 1px 2px #402211; pointer-events: none; }
</style>
