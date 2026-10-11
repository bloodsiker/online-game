<style>
    * { font-family: Tahoma, Geneva, sans-serif; font-size: 11px; }
    body { margin: 0; color: #461c0b; }
    table.coll { border-collapse: collapse; border-spacing: 0; }
    .brd2-all { border: 1px solid #db9f73; }
    .w100 { width: 100%; }
    .p10h td { padding-left: 10px; padding-right: 10px; }
    .p6v td { padding-top: 6px; padding-bottom: 6px; }
    .p2v td { padding-top: 2px; padding-bottom: 2px; }
    .bg_l { background-image: url('{{ asset('img/bg/bg_l.gif') }}'); }
    .redd, .redd * { color: #ba0000 !important; }
    .btn_1 { color: #461c0b !important; }
    .btn_2 { color: #ffe9ba !important; }
    .btn_1, .btn_2 { text-decoration: none; font-weight: 700; white-space: nowrap; }
    .trade-tabs { table-layout: auto; }
    .trade-tab-edge { width: 19px; min-width: 19px; max-width: 19px; padding: 0; }
    .trade-tab-edge img { display: block; }
    .trade-tab-title { width: 1%; white-space: nowrap; text-align: center; padding: 0 2px 4px; background-position: center top; background-repeat: repeat-x; }
    .trade-tab-spacer { width: 100%; }
    .tbl-shp-sml { background: url('{{ asset('img/bg/tbl-shp-sml.png') }}') no-repeat; font-size: 0; }
    .tbl-shp-sml.lt { background-position: 0 0; height: 22px; }
    .tbl-shp-sml.tt { background-position: center -50px; background-repeat: repeat-x; height: 22px; }
    .tbl-shp-sml.rt { background-position: 0 -25px; height: 22px; }
    .tbl-shp-sml.lb { background-position: 0 -75px; }
    .tbl-shp-sml.bb { background-position: center -125px; background-repeat: repeat-x; height: 18px; }
    .tbl-shp-sml.rb { background-position: 0 -100px; }
    .tbl-shp-sides { background: url('{{ asset('img/bg/tbl-shp-sides.png') }}') no-repeat; width: 20px; font-size: 0; }
    .tbl-shp-sides.ls { background-position: left top; background-repeat: repeat-y; }
    .tbl-shp-sides.rs { background-position: right top; background-repeat: repeat-y; }
    .tbl-usi_bg { background: url('{{ asset('img/bg/tbl-usi_bg.gif') }}') repeat; }
    .trade-content { padding: 10px 6px; }
    .trade-summary td { padding-top: 6px; padding-bottom: 6px; }
    .rep-progress-bar--trade { display: inline-block; position: relative; width: 220px; height: 17px; margin-left: 7px; vertical-align: middle; }
    .rep-progress-bar--trade .rep-progress-bar__bg { position: absolute; inset: 0 3px; height: 17px; overflow: hidden; background: url('{{ asset('img/bg/progress-bar.png') }}') 0 -51px repeat-x; }
    .rep-progress-bar--trade .rep-progress-bar__fill { height: 17px; background: url('{{ asset('img/bg/progress-bar.png') }}') 0 -68px repeat-x; }
    .rep-progress-bar--trade .rep-progress-bar__border { position: absolute; inset: 0; pointer-events: none; }
    .rep-progress-bar--trade .rep-progress-bar__border-left,
    .rep-progress-bar--trade .rep-progress-bar__border-right,
    .rep-progress-bar--trade .rep-progress-bar__border-center { position: absolute; top: 0; height: 17px; background: url('{{ asset('img/bg/progress-bar.png') }}') no-repeat; }
    .rep-progress-bar--trade .rep-progress-bar__border-left { left: 0; width: 20px; background-position: 0 -17px; }
    .rep-progress-bar--trade .rep-progress-bar__border-right { right: 0; width: 20px; background-position: 0 -34px; }
    .rep-progress-bar--trade .rep-progress-bar__border-center { left: 20px; right: 20px; background-position: 0 0; background-repeat: repeat-x; }
    .rep-progress-bar--trade .rep-progress-bar__text { position: absolute; inset: 0; color: #fff; font-size: 10px; font-weight: bold; line-height: 17px; text-align: center; text-shadow: 0 1px 2px #402211; pointer-events: none; }
    .trade-license { margin-top: 12px; }
    .trade-message { margin: 10px 0; }
    .trade-heading { margin: 16px 0 8px; font-size: 12px; color: #461c0b; }
    .trade-item-title, .trade-item-detail { margin-bottom: 3px; }
    .trade-item-detail { color: #777; }
    .trade-row-gap { height: 5px; }
    .trade-empty { padding: 10px; color: #955c4a; }
    .relic-frame { position: relative; width: 60px; height: 60px; padding: 5px 6px 6px; background: url('{{ asset('main/images/user-reward-frame.png') }}') 0 0 no-repeat; }
    .relic-img { display: block; width: 60px; height: 60px; object-fit: contain; cursor: pointer; }
    .relic-name { font-weight: 700; cursor: pointer; text-decoration: underline; }
    .relic-badge { font-weight: 700; padding: 1px 5px; border-radius: 3px; color: #fff; font-size: 10px; }
    .relic-badge.active { background: #489200; }
    .relic-badge.inactive { background: #999; }
    .cart-amount-sell-price input { width: 40px; padding: 0 4px; text-align: center; }
    .pointer, .pointer input { cursor: pointer; }
    .cart-amount-sell-price.disabled, .trade-action.disabled { opacity: .45; cursor: default; }
    .trade-action.disabled input { cursor: default; }
    form { margin: 0; }
    .aa-table { font-size: 11px; }
    .aa-tl { background: url('/img/bg/item_info/tbl-pop_corner-top-left.gif') no-repeat; width: 14px; height: 24px; }
    .aa-t { background: url('/img/bg/item_info/tbl-pop_top.gif'); height: 24px; }
    .aa-tr { background: url('/img/bg/item_info/tbl-pop_corner-top-right.gif') no-repeat; width: 14px; height: 24px; }
    .aa-l { background: url('/img/bg/item_info/tbl-pop_left.gif') repeat-y; width: 14px; }
    .aa-r { background: url('/img/bg/item_info/tbl-pop_right.gif') repeat-y; width: 14px; }
    .aa-bl { background: url('/img/bg/item_info/tbl-pop_corner-bottom-left.gif') no-repeat; width: 14px; height: 5px; }
    .aa-b { background: url('/img/bg/item_info/tbl-pop_bottom.gif') repeat-x; height: 5px; }
    .aa-br { background: url('/img/bg/item_info/tbl-pop_corner-bottom-right.gif') no-repeat; width: 14px; height: 5px; }
    .list_dark { background-color: #f4bb8a; }
    .skill_list td { padding: 0 7px; }
    .red, .red * { color: #d00000; }
</style>
