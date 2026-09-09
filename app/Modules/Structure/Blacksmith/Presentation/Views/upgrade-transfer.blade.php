<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Перенос заточки</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        * { font-family: Tahoma, Geneva, sans-serif; font-size: 11px; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .brd2-all { border: 1px solid #db9f73; }
        .w100 { width: 100%; }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .p2v, .p2v td { padding-top: 2px; padding-bottom: 2px; }
        .bg_l { background-image: url(/img/bg/bg_l.gif); }
        .btn_1 { color: #461c0b !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .btn_2 { color: #ffe9ba !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .regcolor, .regcolor * { color: #955c4a; }
        .gp-frame-tl, .gp-frame-tr, .gp-frame-bl, .gp-frame-br { background: url('/img/bg/common-corners.png') no-repeat; font-size: 0; line-height: 0; }
        .gp-frame-t, .gp-frame-b { background: url('/img/bg/common-tb.png') repeat-x; font-size: 0; line-height: 0; }
        .gp-frame-l, .gp-frame-r { background: url('/img/bg/common-lr.png') repeat-y; font-size: 0; line-height: 0; }
        .gp-frame-tl { background-position: 0 0; }
        .gp-frame-tr { background-position: 100% 0; }
        .gp-frame-bl { background-position: 0 100%; }
        .gp-frame-br { background-position: 100% 100%; }
        .gp-frame-b { background-position: 0 100%; }
        .gp-frame-r { background-position: 100% 0; }
        .gp-frame-bg { background: url('/img/bg/common-bg.png'); padding: 10px; }
        .transfer-list { width: 100%; }
        .transfer-list-wrap { max-height: 430px; overflow-y: auto; }
        .transfer-row { cursor: pointer; border-bottom: 1px solid #e8c899; }
        .transfer-row:hover { background: #ffefd5; }
        .transfer-row.selected { background: #ffe0a0; font-weight: bold; }
        .transfer-row td { padding: 4px; }
        .transfer-row img { width: 40px; height: 40px; object-fit: contain; }
        .transfer-level { color: #2255aa; font-weight: bold; }
        .transfer-slot-label { color: #7d674f; font-size: 10px; }
        .transfer-center { min-height: 250px; text-align: center; }
        .transfer-preview { min-height: 92px; }
        .transfer-preview img, .transgressor-icon img { width: 60px; height: 60px; object-fit: contain; }
        .transfer-icon-frame, .transgressor-icon {
            display: inline-block;
            width: 60px;
            height: 60px;
            padding: 5px 6px 6px;
            background: url('/main/images/user-reward-frame.png') no-repeat;
        }
        .transfer-arrow { margin: 5px 0; color: #965b20; font-size: 24px; font-weight: bold; }
        .transfer-required { margin: 8px 0; padding: 6px; border: 1px solid #d1a45d; background: rgba(255, 244, 211, .65); }
        .transgressor-option { display: flex; align-items: center; gap: 7px; margin-top: 5px; padding: 4px; border: 1px solid transparent; cursor: pointer; text-align: left; }
        .transgressor-option:hover { background: #ffefd5; }
        .transgressor-option.selected { border-color: #b77a25; background: #ffe0a0; }
        .transgressor-details { line-height: 16px; }
        .transfer-chance { color: #2255aa; font-weight: bold; }
        .transfer-submit { cursor: pointer; }
        .butt1.pointer.disabled { cursor: default; opacity: .5; }
        .butt1.pointer.disabled input { cursor: default; }
        .flash-success { background: #d4f0c0; border: 1px solid #60a840; color: #2a6010; padding: 6px 10px; margin-bottom: 8px; }
        .flash-fail { background: #f8dcd0; border: 1px solid #c05030; color: #7a2010; padding: 6px 10px; margin-bottom: 8px; }
        .empty-list { padding: 12px; color: #888; text-align: center; }
    </style>
</head>
<body class="regcolor" leftmargin="0" rightmargin="0">

{!! $itemTooltipScript !!}
<script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>

<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" valign="top" align="left">
            @include('blacksmith::_tabs', ['activeTab' => 'upgrade-transfer'])
        </td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" style="padding:10px 6px;">
            @if(session('message'))
                <div class="{{ session('upgrade_transfer_success', false) ? 'flash-success' : 'flash-fail' }}">{{ session('message') }}</div>
            @endif

            <table class="coll w100 p10h p2v brd2-all">
                <tr class="bg_l">
                    <td align="left"><b>Кузня:</b> Перенос заточки</td>
                    <td align="right"><b>Правило:</b> одинаковый тип и слот экипировки</td>
                </tr>
            </table>

            <p style="text-align:center;">Выберите Трансгрессор подходящей редкости. При неудаче исходная заточка будет полностью потеряна.</p>

            <form action="{{ route('blacksmith.upgrade_transfer.process', ['id' => $blacksmith->id]) }}" method="post" id="transfer-form">
                @csrf
                <input type="hidden" name="source_item_id" id="source-item-id">
                <input type="hidden" name="target_item_id" id="target-item-id">
                <input type="hidden" name="transgressor_share_item_id" id="transgressor-share-item-id">

                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr valign="top">
                        <td width="35%">
                            <div style="text-align:center; font-weight:bold; margin-bottom:5px;">Откуда переносим</div>
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr><td width="8" height="10" class="gp-frame-tl"></td><td class="gp-frame-t"></td><td width="8" class="gp-frame-tr"></td></tr>
                                <tr>
                                    <td class="gp-frame-l"></td>
                                    <td class="gp-frame-bg">
                                        <div class="transfer-list-wrap">
                                            <table class="transfer-list coll">
                                                <tbody id="source-list">
                                                @forelse($sourceItems as $slot)
                                                    <tr class="transfer-row source-row"
                                                        data-item-id="{{ $slot['id'] }}"
                                                        data-key="{{ $slot['compatibilityKey'] }}"
                                                        data-name="{{ $slot['name'] }}"
                                                        data-image="{{ $slot['image'] }}"
                                                        data-level="{{ $slot['level'] }}">
                                                        <td width="44"><img src="{{ $slot['image'] }}" data-id="{{ $slot['id'] }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" alt=""></td>
                                                        <td><span style="color:{{ $slot['rarityColor'] }}">{{ $slot['name'] }}</span> <span class="transfer-level">+{{ $slot['level'] }}</span><br><span class="transfer-slot-label">{{ $slot['typeLabel'] }} · {{ $slot['slotLabel'] }}</span></td>
                                                    </tr>
                                                @empty
                                                    <tr><td class="empty-list">Нет заточенных предметов в рюкзаке</td></tr>
                                                @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                    <td class="gp-frame-r"></td>
                                </tr>
                                <tr><td height="10" class="gp-frame-bl"></td><td class="gp-frame-b"></td><td class="gp-frame-br"></td></tr>
                            </table>
                        </td>

                        <td width="10">&nbsp;</td>

                        <td width="30%">
                            <div style="text-align:center; font-weight:bold; margin-bottom:5px;">Перенос</div>
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr><td width="8" height="10" class="gp-frame-tl"></td><td class="gp-frame-t"></td><td width="8" class="gp-frame-tr"></td></tr>
                                <tr>
                                    <td class="gp-frame-l"></td>
                                    <td class="gp-frame-bg transfer-center">
                                        <div id="source-preview" class="transfer-preview" style="color:#888;">Выберите исходный предмет</div>
                                        <div class="transfer-arrow">↓</div>
                                        <div id="target-preview" class="transfer-preview" style="color:#888;">Выберите новый предмет</div>

                                        <div class="transfer-required">
                                            <b>Выберите Трансгрессор:</b>
                                            @forelse($transgressors as $transgressor)
                                                <div class="transgressor-option"
                                                     data-share-item-id="{{ $transgressor['shareItemId'] }}"
                                                     data-rarity="{{ $transgressor['rarityLabel'] }}"
                                                     data-chance="{{ $transgressor['chance'] }}">
                                                    <span class="transgressor-icon" data-id="{{ $transgressor['tooltipItemId'] }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)">
                                                        <img src="{{ $transgressor['image'] }}" alt="{{ $transgressor['name'] }}">
                                                    </span>
                                                    <span class="transgressor-details">
                                                        <b style="color:{{ $transgressor['rarityColor'] }}">{{ $transgressor['rarityLabel'] }}</b><br>
                                                        Шанс: <span class="transfer-chance">{{ $transgressor['chance'] }}%</span><br>
                                                        В наличии: <b class="grnn">{{ $transgressor['count'] }} шт.</b>
                                                    </span>
                                                </div>
                                            @empty
                                                <b class="redd">Трансгрессор отсутствует</b>
                                            @endforelse
                                        </div>

                                        <span id="transfer-submit-wrap" class="butt1 pointer disabled">
                                            <span><input value="Перенести" type="button" id="transfer-submit" class="grnn transfer-submit" onclick="openTransferConfirm()" disabled></span>
                                        </span>
                                    </td>
                                    <td class="gp-frame-r"></td>
                                </tr>
                                <tr><td height="10" class="gp-frame-bl"></td><td class="gp-frame-b"></td><td class="gp-frame-br"></td></tr>
                            </table>
                        </td>

                        <td width="10">&nbsp;</td>

                        <td width="35%">
                            <div style="text-align:center; font-weight:bold; margin-bottom:5px;">Куда переносим</div>
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr><td width="8" height="10" class="gp-frame-tl"></td><td class="gp-frame-t"></td><td width="8" class="gp-frame-tr"></td></tr>
                                <tr>
                                    <td class="gp-frame-l"></td>
                                    <td class="gp-frame-bg">
                                        <div class="transfer-list-wrap">
                                            <table class="transfer-list coll">
                                                <tbody id="target-list">
                                                @forelse($targetItems as $slot)
                                                    <tr class="transfer-row target-row"
                                                        data-item-id="{{ $slot['id'] }}"
                                                        data-key="{{ $slot['compatibilityKey'] }}"
                                                        data-name="{{ $slot['name'] }}"
                                                        data-image="{{ $slot['image'] }}"
                                                        data-level="0">
                                                        <td width="44"><img src="{{ $slot['image'] }}" data-id="{{ $slot['id'] }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" alt=""></td>
                                                        <td><span style="color:{{ $slot['rarityColor'] }}">{{ $slot['name'] }}</span><br><span class="transfer-slot-label">{{ $slot['typeLabel'] }} · {{ $slot['slotLabel'] }}</span></td>
                                                    </tr>
                                                @empty
                                                    <tr><td class="empty-list">Нет незаточенных предметов в рюкзаке</td></tr>
                                                @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                    <td class="gp-frame-r"></td>
                                </tr>
                                <tr><td height="10" class="gp-frame-bl"></td><td class="gp-frame-b"></td><td class="gp-frame-br"></td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </form>
        </td>
        <td class="tbl-shp-sides rs">&nbsp;</td>
    </tr>
    <tr height="18">
        <td class="tbl-shp-sml lb"><b></b></td>
        <td class="tbl-shp-sml bb"><b></b></td>
        <td class="tbl-shp-sml rb"><b></b></td>
    </tr>
    </tbody>
</table>

<div id="transfer-confirm-overlay"
     onclick="if (event.target === this) closeTransferConfirm();"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:9999; align-items:center; justify-content:center;">
    <div class="popup_global_container" style="width:320px;">
        <div class="popup-top-left">
            <div class="popup-top-right">
                <div class="popup-top-center">
                    <div class="popup_global_title">Перенос заточки</div>
                </div>
            </div>
            <div class="popup_global_close_btn" onclick="closeTransferConfirm()"></div>
        </div>
        <div class="popup-left-center">
            <div class="popup-right-center">
                <div class="popup_global_content" style="padding:20px; text-align:center;">
                    <div id="transfer-confirm-text" class="redd" style="margin-bottom:8px;"></div>
                    <div id="transfer-confirm-warning" style="margin-bottom:14px;"></div>
                    <b class="butt1 pointer"><b><input value="Перенести" type="button" onclick="confirmTransfer()" class="grnn" style="width:100px;"></b></b>
                    &nbsp;
                    <b class="butt1 pointer"><b><input value="Отмена" type="button" onclick="closeTransferConfirm()" style="width:100px;"></b></b>
                </div>
            </div>
        </div>
        <div class="popup-left-bottom">
            <div class="popup-right-bottom">
                <div class="popup-bottom-center"></div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var selectedSource = null;
        var selectedTarget = null;
        var selectedTransgressor = null;
        var sourceInput = document.getElementById('source-item-id');
        var targetInput = document.getElementById('target-item-id');
        var transgressorInput = document.getElementById('transgressor-share-item-id');
        var submitButton = document.getElementById('transfer-submit');
        var submitButtonWrap = document.getElementById('transfer-submit-wrap');

        function renderPreview(elementId, row, placeholder) {
            var element = document.getElementById(elementId);
            element.innerHTML = '';

            if (!row) {
                element.textContent = placeholder;
                element.style.color = '#888';
                return;
            }

            element.style.color = '';
            var frame = document.createElement('span');
            frame.className = 'transfer-icon-frame';
            var image = document.createElement('img');
            image.src = row.dataset.image;
            image.alt = row.dataset.name;
            frame.appendChild(image);
            element.appendChild(frame);
            element.appendChild(document.createElement('br'));
            element.appendChild(document.createTextNode(row.dataset.name + (row.dataset.level > 0 ? ' +' + row.dataset.level : '')));
        }

        function updateSubmit() {
            var disabled = !selectedSource || !selectedTarget || !selectedTransgressor;
            submitButton.disabled = disabled;
            submitButtonWrap.classList.toggle('disabled', disabled);
        }

        document.querySelectorAll('.source-row').forEach(function (row) {
            row.addEventListener('click', function () {
                document.querySelectorAll('.source-row').forEach(function (candidate) { candidate.classList.remove('selected'); });
                row.classList.add('selected');
                selectedSource = row;
                sourceInput.value = row.dataset.itemId;
                renderPreview('source-preview', row, 'Выберите исходный предмет');

                selectedTarget = null;
                targetInput.value = '';
                renderPreview('target-preview', null, 'Выберите новый предмет');
                document.querySelectorAll('.target-row').forEach(function (targetRow) {
                    targetRow.classList.remove('selected');
                    targetRow.style.display = targetRow.dataset.key === row.dataset.key ? '' : 'none';
                });
                updateSubmit();
            });
        });

        document.querySelectorAll('.target-row').forEach(function (row) {
            row.addEventListener('click', function () {
                if (!selectedSource || row.dataset.key !== selectedSource.dataset.key) return;
                document.querySelectorAll('.target-row').forEach(function (candidate) { candidate.classList.remove('selected'); });
                row.classList.add('selected');
                selectedTarget = row;
                targetInput.value = row.dataset.itemId;
                renderPreview('target-preview', row, 'Выберите новый предмет');
                updateSubmit();
            });
        });

        document.querySelectorAll('.transgressor-option').forEach(function (option) {
            option.addEventListener('click', function () {
                document.querySelectorAll('.transgressor-option').forEach(function (candidate) { candidate.classList.remove('selected'); });
                option.classList.add('selected');
                selectedTransgressor = option;
                transgressorInput.value = option.dataset.shareItemId;
                updateSubmit();
            });
        });

        window.openTransferConfirm = function () {
            if (!selectedSource || !selectedTarget || !selectedTransgressor) return;
            document.getElementById('transfer-confirm-text').textContent =
                'Перенести заточку +' + selectedSource.dataset.level + ' с «' + selectedSource.dataset.name + '» на «' + selectedTarget.dataset.name + '»?';
            document.getElementById('transfer-confirm-warning').textContent =
                'Шанс успеха: ' + selectedTransgressor.dataset.chance + '%. При неудаче заточка будет потеряна. Будет израсходован 1 Трансгрессор.';
            document.getElementById('transfer-confirm-overlay').style.display = 'flex';
        };

        window.closeTransferConfirm = function () {
            document.getElementById('transfer-confirm-overlay').style.display = 'none';
        };

        window.confirmTransfer = function () {
            if (!selectedSource || !selectedTarget || !selectedTransgressor) return;
            closeTransferConfirm();
            document.getElementById('transfer-form').submit();
        };

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') closeTransferConfirm();
        });
    })();
</script>
</body>
</html>
