<div class="row mb-3">
    <div class="col-md-12">
        <div class="d-flex flex-wrap" style="gap: 8px;">
            <a href="{{ route('admin.item.info', $item->id) }}"
               class="btn btn-sm {{ request()->routeIs('admin.item.info') ? 'btn-primary' : 'btn-default' }}">
                Основная информация
            </a>
            <a href="{{ route('admin.item.drop', $item->id) }}"
               class="btn btn-sm {{ request()->routeIs('admin.item.drop') ? 'btn-primary' : 'btn-default' }}">
                Дроп с монстров
            </a>
            <a href="{{ route('admin.item.quests', $item->id) }}"
               class="btn btn-sm {{ request()->routeIs('admin.item.quests') ? 'btn-primary' : 'btn-default' }}">
                Участие в квестах
            </a>
        </div>
    </div>
</div>
