@use('App\Modules\Share\Domain\Enums\ItemRarity')
@use('App\Modules\Structure\Shop\Application\DTOs\ShopFilterDTO')
@php
    /** @var \App\Modules\Structure\Shop\Application\DTOs\ShopBuyPageDTO $page */
    $filter = $page->filter;
@endphp
<style>
    /* Контролы — как на странице настроек (interface::settings). */
    .shop-filter { display: block; width: 100%; box-sizing: border-box; color: #795b48; text-align: left; margin: 0; }
    .shop-filter__box { width: 100%; box-sizing: border-box; overflow: visible; padding-top: 12px !important; padding-bottom: 5px !important; }
    .shop-filter__box * { box-sizing: border-box; }
    /* profile-select.js фиксирует ширину триггера в px — растягиваем по контейнеру. */
    .shop-filter .profile-select-trigger { width: 100% !important; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .shop-filter__row { margin: 0 0 7px; }
    .shop-filter__title { margin: 0 0 3px; color: #a4150c; font-weight: bold; }
    .shop-filter__checks { display: flex; flex-wrap: wrap; gap: 0 10px; }
    .shop-filter__check { display: inline-flex; align-items: center; gap: 3px; min-height: 24px; white-space: nowrap; cursor: pointer; }
    .shop-filter__range { display: flex; align-items: center; gap: 5px; margin-top: 4px; }
    .shop-filter__range[hidden] { display: none; }
    .shop-filter__actions { text-align: center; }
    .shop-filter__count { color: #927660; margin: 0 0 6px; }
    .shop-filter .profile-input-wrap { display: inline-block; width: 100%; height: 23px; vertical-align: middle; background: url({{ asset('main/images/gui/input.png') }}) no-repeat 0 0; }
    .shop-filter .profile-input-wrap--num { flex: 0 0 60px; width: 60px; }
    .shop-filter .profile-input-inner { height: 100%; margin-left: 6px; padding-right: 6px; background: url({{ asset('main/images/gui/input.png') }}) no-repeat 100% -23px; }
    .shop-filter .profile-input-inner input { box-sizing: border-box; width: 100%; height: 23px; padding: 0 4px; border: 0; outline: none; color: #6c382c; background: transparent; font: 11px Tahoma, Arial, sans-serif; }
    .shop-filter .profile-input-wrap:focus-within { background-position: 0 -92px; }
    .shop-filter .profile-input-wrap:focus-within .profile-input-inner { background-position: 100% -115px; }
    .shop-filter .profile-select-wrap { display: block; position: relative; }
    .shop-filter .profile-select-wrap::after { content: ''; position: absolute; right: 2px; top: 0; width: 23px; height: 23px; background: url({{ asset('main/images/gui/btn-select.png') }}) no-repeat 0 0; pointer-events: none; }
    .shop-filter .profile-select { box-sizing: border-box; width: 100%; height: 23px; padding: 2px 27px 2px 6px; border: 1px solid #baa276; border-radius: 4px; color: #6c382c; background: #f9e4ab; box-shadow: inset 0 1px 3px rgba(106, 74, 33, .2); appearance: none; font: 11px Tahoma, Arial, sans-serif; cursor: pointer; }
    .shop-filter .profile-select:focus-visible, .shop-filter .profile-select-trigger:focus-visible { outline: 1px solid #9c6c3b; }
    .shop-filter .profile-select option { background: #fff3cf; }
    .shop-filter .profile-select-trigger { text-align: left; }
    .shop-filter .profile-select-wrap.is-open { z-index: 10; }
    .shop-filter .profile-select-wrap.is-open::after { background-position: -46px 0; }
    .shop-filter .profile-select-list { position: absolute; z-index: 20; top: calc(100% - 1px); left: 0; box-sizing: border-box; min-width: 100%; max-height: 180px; overflow-y: auto; padding: 3px; border: 1px solid #ad895c; border-radius: 3px; background: #f9e4ab url({{ asset('img/bg/bgg.gif') }}) repeat; box-shadow: inset 0 0 0 1px #fff1c9, 0 3px 6px rgba(70, 28, 11, .3); scrollbar-width: thin; scrollbar-color: #b89b70 #f9e4ab; }
    .shop-filter .profile-select-option { display: block; padding: 4px 7px; border-radius: 2px; white-space: nowrap; color: #6c382c; cursor: pointer; font: 11px Tahoma, Arial, sans-serif; }
    .shop-filter .profile-select-option[aria-selected="true"] { font-weight: bold; background: rgba(195, 153, 91, .25); }
    .shop-filter .profile-select-option.is-highlighted { color: #fff2ce; background: linear-gradient(#a65a3b, #76351e); box-shadow: inset 0 1px rgba(255, 238, 197, .35); }
    .shop-filter .profile-select-list[hidden], .shop-filter .profile-select[hidden] { display: none; }
    .shop-filter .game-settings-checkbox { flex: 0 0 24px; width: 24px; height: 23px; margin: 0; border: 0; background: url({{ asset('main/images/gui/gui-checkbox.png') }}) no-repeat 0 -23px; appearance: none; -webkit-appearance: none; cursor: pointer; }
    .shop-filter .game-settings-checkbox:checked { background-position: 0 0; }
    .shop-filter .game-settings-checkbox:focus-visible { outline: 1px solid #9c6c3b; border-radius: 4px; }
</style>

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top: 5px;">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" valign="top" align="center">
            <table border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr height="22">
                    <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                    <td align="center" class="tbl-usi-hdr mbg">Фильтры</td>
                    <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                </tr>
                </tbody>
            </table>
        </td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" align="center" style="padding: 6px 4px">
<form method="get" action="{{ route($indexRoute, ['id' => $page->shopId]) }}" class="shop-filter">
<div class="shop-filter__box p10h p4v brd2-all bg_l">
    <input type="hidden" name="filter" value="1">
    @if($page->activeCategoryId !== null)
        <input type="hidden" name="category_id" value="{{ $page->activeCategoryId }}">
    @endif

    <div class="shop-filter__row">
        <div class="profile-input-wrap"><div class="profile-input-inner"><input type="text" name="search" value="{{ $filter->search }}" maxlength="50" placeholder="Поиск по названию"></div></div>
    </div>

    <div class="shop-filter__row">
        <label class="shop-filter__check b"><input class="game-settings-checkbox" type="checkbox" name="available" value="1" @checked($filter->availableOnly)>Только доступные мне</label>
    </div>

    <div class="shop-filter__row">
        <div class="shop-filter__title">Уровень</div>
        <span class="profile-select-wrap"><select class="profile-select" name="level_mode" aria-label="Уровень" data-level-mode>
            <option value="{{ ShopFilterDTO::LEVEL_ANY }}" @selected($filter->levelMode === ShopFilterDTO::LEVEL_ANY)>любой</option>
            <option value="{{ ShopFilterDTO::LEVEL_MINE }}" @selected($filter->levelMode === ShopFilterDTO::LEVEL_MINE)>до моего ({{ $page->playerLevel }})</option>
            <option value="{{ ShopFilterDTO::LEVEL_RANGE }}" @selected($filter->levelMode === ShopFilterDTO::LEVEL_RANGE)>диапазон</option>
        </select></span>
        <div class="shop-filter__range" data-level-range @if($filter->levelMode !== ShopFilterDTO::LEVEL_RANGE) hidden @endif>
            <span>от</span>
            <div class="profile-input-wrap profile-input-wrap--num"><div class="profile-input-inner"><input type="text" name="level_from" value="{{ $filter->levelFrom }}" inputmode="numeric" maxlength="3" aria-label="Уровень от"></div></div>
            <span>до</span>
            <div class="profile-input-wrap profile-input-wrap--num"><div class="profile-input-inner"><input type="text" name="level_to" value="{{ $filter->levelTo }}" inputmode="numeric" maxlength="3" aria-label="Уровень до"></div></div>
        </div>
    </div>

    <div class="shop-filter__row">
        <div class="shop-filter__title">Редкость</div>
        <div class="shop-filter__checks">
            @foreach(ItemRarity::cases() as $rarity)
                <label class="shop-filter__check" style="color: {{ $rarity->color() }};"><input class="game-settings-checkbox" type="checkbox" name="rarity[]" value="{{ $rarity->value }}" @checked($filter->allowsRarity($rarity->value))>{{ $rarity->label() }}</label>
            @endforeach
        </div>
    </div>

    <div class="shop-filter__row">
        <div class="shop-filter__title">Сортировка</div>
        <span class="profile-select-wrap"><select class="profile-select" name="sort" aria-label="Сортировка">
            @foreach(ShopFilterDTO::SORTS as $value => $label)
                <option value="{{ $value }}" @selected($filter->sort === $value)>{{ $label }}</option>
            @endforeach
        </select></span>
    </div>

</div>
<br>
<div class="shop-filter__actions">
    @if($filter->isActive())
        <div class="shop-filter__count">Показано {{ count($page->items) }} из {{ $page->totalItemsCount }}</div>
    @endif
    <span class="butt1 pointer"><span><input value="Применить" type="submit" class="grnn"></span></span>
    <br>
    @if($filter->isActive())
        <span class="butt1 pointer"><span><input value="Сбросить фильтры" type="button" class="redd"
            onclick="location.href='{{ route($indexRoute, array_filter(['id' => $page->shopId, 'category_id' => $page->activeCategoryId, 'filter_reset' => 1])) }}';"></span></span>
        <br>
    @endif
</div>
</form>
        </td>
        <td class="tbl-shp-sides rs">&nbsp;</td>
    </tr>
    <tr height="18">
        <td width="20" align="right" valign="top" class="tbl-shp-sml lb"><b></b></td>
        <td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td>
        <td width="20" align="left" valign="top" class="tbl-shp-sml rb"><b></b></td>
    </tr>
    </tbody>
</table>
<script src="{{ asset('js/profile-select.js') }}?v={{ filemtime(public_path('js/profile-select.js')) }}" defer></script>
<script>
    // Поля «от / до» видны только в режиме «диапазон».
    (() => {
        const mode = document.querySelector('select[data-level-mode]');
        const range = document.querySelector('[data-level-range]');
        if (!mode || !range) return;
        mode.addEventListener('change', () => {
            range.hidden = mode.value !== @js(ShopFilterDTO::LEVEL_RANGE);
            if (!range.hidden) range.querySelector('input')?.focus();
        });
    })();
</script>
