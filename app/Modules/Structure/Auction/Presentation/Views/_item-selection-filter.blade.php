<form method="get" action="{{ route($filterRoute, ['id' => $auction->id]) }}" style="margin:0 0 10px">
    @if($selectedItem)
        <input type="hidden" name="{{ $selectionKey }}" value="{{ $selectionId }}">
    @endif
    <div style="display:flex;align-items:center;flex-wrap:nowrap;gap:6px">
        <input type="search" name="filter[name]" value="{{ $filter['name'] }}" maxlength="100" placeholder="{{ __('Название') }}" aria-label="{{ __('Название') }}" style="flex:1 1 160px;min-width:0;max-width:100%;height:19px;margin:0;box-sizing:border-box">
        <select name="filter[type]" aria-label="{{ __('Тип предмета') }}" style="flex:0 1 auto;min-width:0;max-width:100%;height:19px;margin:0;box-sizing:border-box">
            <option value="">{{ __('Все типы') }}</option>
            @foreach(\App\Modules\Share\Domain\Enums\ShareItemType::cases() as $itemType)
                <option value="{{ $itemType->value }}" @selected($filter['type'] === $itemType->value)>{{ $itemType->label() }}</option>
            @endforeach
        </select>
        <b class="butt2 pointer" style="flex:0 0 auto;line-height:19px"><b><input type="submit" value="{{ __('Найти') }}"></b></b>
        <b class="butt2 pointer" style="flex:0 0 auto;line-height:19px"><b><input type="button" value="{{ __('Сбросить') }}" onclick="window.location.href = @js(route($filterRoute, array_filter(['id' => $auction->id, $selectionKey => $selectionId])))"></b></b>
    </div>
</form>
