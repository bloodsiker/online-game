    <style>
        html, body { min-height: 100%; margin: 0; font-family: Tahoma, Arial, sans-serif; font-size: 11px; color: #000; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .tbl-usi_bg { background: url('/img/bg/tbl-usi_bg.gif') repeat; }
        .brd2-all { border: 1px solid #db9f73; }
        .bg_l { background-image: url('/img/bg/info/bg_l.gif'); }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .p2v, .p2v td { padding-top: 2px; padding-bottom: 2px; }
        .pointer, .pointer input { cursor: pointer; }

        .knowledge-summary { margin-bottom: 10px; }
        .knowledge-hint { margin: 0 0 10px; color: #49382d; text-align: center; }
        .upgrade-icon { display: inline-block; width: 60px; height: 60px; padding: 5px 6px 6px; background: url('/main/images/user-reward-frame.png') no-repeat; cursor: pointer; }
        .upgrade-icon img { width: 60px; height: 60px; object-fit: contain; }
        .req-ok { color: #247327 !important; font-weight: bold; }
        .req-fail { color: #a02020 !important; font-weight: bold; }
        .butt1.pointer.disabled { cursor: default; opacity: .5; }
        .butt1.pointer.disabled input { cursor: default; }
        .message { display: inline-block; margin: 0 0 8px; padding: 4px 8px; border: 1px solid #8d2616; color: #8d2616; font-weight: bold; }
        .message.ok { border-color: #247327; color: #247327; }
        .empty-list { padding: 25px; color: #49382d; font-size: 12px; text-align: center; }

        .knowledge-container { width: 100%; table-layout: fixed; border-spacing: 0; }
        .knowledge-container .common-inner-block-tl { background: url('/main/images/common-inner-block-tl.png') no-repeat; }
        .knowledge-container .common-inner-block-tr { background: url('/main/images/common-inner-block-tr.png') no-repeat; }
        .knowledge-container .common-inner-block-bl { background: url('/main/images/common-inner-block-bl.png') no-repeat; }
        .knowledge-container .common-inner-block-br { background: url('/main/images/common-inner-block-br.png') no-repeat; }
        .knowledge-container .common-inner-block-t { background: url('/main/images/common-inner-block-t.png') repeat-x; }
        .knowledge-container .common-inner-block-b { background: url('/main/images/common-inner-block-b.png') repeat-x; }
        .knowledge-container .common-inner-block-l { background: url('/main/images/common-inner-block-l.png') repeat-y; }
        .knowledge-container .common-inner-block-r { background: url('/main/images/common-inner-block-r.png') repeat-y; }
        .knowledge-container .common-inner-block-bg { background: url('/img/bg/bg_l.gif') repeat; padding: 3px; vertical-align: top; }

        .knowledge-card { width: 100%; table-layout: fixed; border-spacing: 0; margin-bottom: 12px; }
        .knowledge-card .gp-frame-tl, .knowledge-card .gp-frame-tr, .knowledge-card .gp-frame-bl, .knowledge-card .gp-frame-br { background: url('/img/bg/common-corners.png') no-repeat; font-size: 0; line-height: 0; }
        .knowledge-card .gp-frame-t, .knowledge-card .gp-frame-b { background: url('/img/bg/common-tb.png') repeat-x; font-size: 0; line-height: 0; }
        .knowledge-card .gp-frame-l, .knowledge-card .gp-frame-r { background: url('/img/bg/common-lr.png') repeat-y; font-size: 0; line-height: 0; }
        .knowledge-card .gp-frame-tl, .knowledge-card .gp-frame-t, .knowledge-card .gp-frame-l { background-position: 0 0; }
        .knowledge-card .gp-frame-tr, .knowledge-card .gp-frame-r { background-position: 100% 0; }
        .knowledge-card .gp-frame-bl, .knowledge-card .gp-frame-b { background-position: 0 100%; }
        .knowledge-card .gp-frame-br { background-position: 100% 100%; }
        .knowledge-card .gp-frame-bg { background: url('/img/bg/common-bg.png') repeat; padding: 10px; }

        .knowledge-card__title { margin: 0 0 14px; font-size: 13px; font-weight: bold; text-align: center; }
        .knowledge-card__row { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: center; gap: 10px; }
        .knowledge-card__operand { flex: 0 0 80px; width: 80px; min-height: 80px; text-align: center; }
        .knowledge-card__count { display: block; margin-top: 4px; font-weight: bold; white-space: nowrap; }
        .knowledge-card__symbol { flex: 0 0 auto; align-self: flex-start; display: flex; align-items: center; height: 71px; color: #8d2616; font-size: 25px; font-weight: bold; }
        .knowledge-card__text { flex: 1 1 170px; min-width: 150px; max-width: 400px; text-align: left; color: #553e20; line-height: 16px; }
        .knowledge-card__text b { color: #49382d; }
        .knowledge-card__desc { margin-top: 4px; color: #6b513a; font-style: italic; }
        .knowledge-card__text a { text-decoration: none; }
        .knowledge-card__reqs { flex: 0 0 165px; width: 165px; padding: 5px; box-sizing: border-box; text-align: left; background: url('/img/bg/tbl-usi_bg.gif') repeat; border: 2px solid #e3b360; border-radius: 5px; line-height: 16px; }
        .knowledge-card__reqs-title { color: #553e20; font-weight: bold; text-align: center; }
        .knowledge-card__req { display: flex; justify-content: space-between; gap: 6px; }
        .knowledge-card__action { flex: 0 0 auto; min-width: 120px; min-height: 80px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; text-align: center; }
        .knowledge-card__action input[type=number] { width: 60px; text-align: center; }
        .knowledge-card__done { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; color: #1e5e1e; font-weight: bold; background: #e3f3d9; border: 1px solid #7fa865; border-radius: 14px; box-shadow: inset 0 0 0 1px rgba(255,255,255,.5); }
        .knowledge-card__done::before { content: '✓'; display: inline-flex; align-items: center; justify-content: center; width: 15px; height: 15px; color: #fff; font-size: 10px; font-weight: bold; background: #4c8f2f; border-radius: 50%; }
        .knowledge-section { margin: 4px 0 10px; color: #8d2616; font-size: 12px; font-weight: bold; text-align: center; }
    
        .festival-banner { margin: 0 0 10px; padding: 6px 10px; border: 1px solid #db9f73; background: #f3e4c7; font-size: 12px; text-align: center; }
        .w100 { width: 100%; }
        .quest-frame { margin: 0 auto 12px; border-collapse: separate; border-spacing: 0; text-align: center; }
        .quest-frame.quest-frame--narrow { width: 520px; max-width: 100%; }
        .quest-frame > tbody > tr:first-child,
        .quest-frame > tbody > tr:last-child { font-size: 0; line-height: 0; }
        .common-inset-tl { background: url({{ asset('main/images/common-inset-tl.png') }}) no-repeat; }
        .common-inset-tr { background: url({{ asset('main/images/common-inset-tr.png') }}) no-repeat; }
        .common-inset-bl { background: url({{ asset('main/images/common-inset-bl.png') }}) no-repeat; }
        .common-inset-br { background: url({{ asset('main/images/common-inset-br.png') }}) no-repeat; }
        .common-inset-t { background: url({{ asset('main/images/common-inset-t.png') }}) repeat-x; }
        .common-inset-b { background: url({{ asset('main/images/common-inset-b.png') }}) repeat-x; }
        .common-inset-l { background: url({{ asset('main/images/common-inset-l.png') }}) repeat-y; }
        .common-inset-r { background: url({{ asset('main/images/common-inset-r.png') }}) repeat-y; }
        .common-inset-bg { background: #ffffd6; }
        .active-quest { padding: 10px 16px 8px; line-height: 1.5; color: #461c0b; }
        .active-quest .quest-title { margin: 0 0 8px; padding: 0 0 7px; border-bottom: 1px solid #db9f73; font-weight: bold; font-size: 13px; color: #461c0b; }
        .active-quest .quest-title .badge-step { display: inline-block; margin-left: 6px; padding: 0 4px 1px; border: 1px solid #c49485; background: #f5dfc7 url(/img/bg/info/bg_l.gif) repeat; color: #8b0000; font-size: 9px; font-weight: bold; line-height: 12px; vertical-align: middle; }
        .active-quest .quest-acceptor { color: #955c4a; margin-bottom: 6px; }
        .active-quest .quest-winner { margin-bottom: 6px; color: #8b0000; font-weight: bold; }
        .progress-row { margin: 0 0 6px; }
        .progress-label { color: #461c0b; margin-bottom: 2px; }
        .rep-progress-bar { position: relative; width: 100%; max-width: 600px; height: 31px; margin: 0 auto; overflow: hidden; }
        .rep-progress-bar__bg { height: 27px; margin: 2px 5px 0; overflow: hidden; border-radius: 5px; background: url({{ asset('img/progressbar/progress-bar-1-bg.png') }}) 0 -54px repeat-x; }
        .rep-progress-bar__fill { height: 27px; background: url({{ asset('img/progressbar/progress-bar-1-bg.png') }}) 0 -27px repeat-x; }
        .rep-progress-bar__border { position: absolute; top: 0; left: 0; width: 100%; height: 31px; }
        .rep-progress-bar__border-left,
        .rep-progress-bar__border-right,
        .rep-progress-bar__border-center { height: 31px; background: url({{ asset('img/progressbar/progress-bar-1-border.png') }}) no-repeat; }
        .rep-progress-bar__border-left,
        .rep-progress-bar__border-right { position: absolute; top: 0; width: 20px; }
        .rep-progress-bar__border-left { left: 0; }
        .rep-progress-bar__border-right { right: 0; background-position: 0 -31px; }
        .rep-progress-bar__border-center { margin: 0 20px; background-position: 0 -62px; background-repeat: repeat-x; }
        .rep-progress-bar__text { position: absolute; top: 0; left: 0; width: 100%; color: #fff; font-size: 11px; font-weight: bold; line-height: 31px; text-align: center; text-shadow: -1px 0 2px #444, 0 1px 2px #444, 1px 0 2px #444, 0 -1px 2px #444; }
        .festival-lore { text-align: center; }
        .festival-lore .quest-title { font-size: 14px; letter-spacing: .4px; }
        .festival-lore .lore-orn { color: #b8452b; font-size: 11px; margin: 0 4px; }
        .festival-lore p { margin: 0 0 8px; color: #631c0b; font-style: italic; line-height: 1.5; text-align: justify; text-indent: 1.4em; }
        .festival-lore p:first-of-type::first-letter { color: #8d2616; font-size: 20px; font-weight: bold; font-style: normal; }
        .festival-lore .lore-sign { margin-top: 4px; color: #955c4a; text-align: right; font-style: italic; }
        .festival-rules { text-align: left; }
        .festival-rules ol { margin: 0; padding-left: 20px; }
        .festival-rules li { margin: 0 0 5px; color: #461c0b; line-height: 1.45; }
        .festival-rules li::marker { color: #b8452b; font-weight: bold; }
        .p6v, .p6v td { padding-top: 6px; padding-bottom: 6px; }
        .cart-amount-sell-price input { width: 50px; padding: 0 4px; }
        .festival-counter { display: inline-block; vertical-align: middle; margin-right: 6px; }
        .festival-row .festival-table { margin: 0; }
        .festival-row .festival-table td { border-bottom: 0; }
        .festival-row .festival-table { table-layout: fixed; width: 100%; }
        .festival-row .festival-table td:nth-child(1) { width: 76px; padding-right: 0; }
        .festival-row .festival-table td:nth-child(2) { padding-left: 4px; }
        .festival-row .festival-table td:nth-child(3) { width: 130px; text-align: center; }
        .festival-row .festival-table td:nth-child(4) { width: 230px; text-align: center; white-space: nowrap; }
        @media (max-width: 640px) {
            .festival-row .festival-table td:nth-child(3) { width: 80px; }
            .festival-row .festival-table td:nth-child(4) { width: 190px; }
        }
        .festival-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .festival-table td, .festival-table th { padding: 4px 6px; border-bottom: 1px solid #e3b360; text-align: left; }
        .festival-table td.num { text-align: center; white-space: nowrap; }
        .festival-table input[type=number] { width: 70px; text-align: center; }
        .festival-sides { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin: 8px 0 12px; }
        .festival-sides form { margin: 0; }
        .festival-result { text-align: center; }
        .festival-result .quest-title { color: #461c0b; }
        .festival-result--won .quest-title { color: #114d01; }
        .festival-result--lost .quest-title { color: #8b0000; }
    </style>
