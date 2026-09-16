<section class="card mt-3 mb-0">
    <header class="card-header">
        <h2 class="card-title">Содержимое сундука</h2>
        <p class="card-subtitle text-muted">При открытии каждый предмет разыгрывается независимо с указанным шансом.</p>
    </header>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group mb-2">
                    <label class="col-form-label" for="sel-chest-content-item">Предмет</label>
                    <select id="sel-chest-content-item" name="share_item_id" class="form-control" required form="chest-content-add-form"></select>
                </div>
                <div class="form-group mb-2">
                    <label class="col-form-label" for="chest-drop-chance">Шанс, %</label>
                    <input id="chest-drop-chance" type="number" class="form-control" name="drop_chance" value="100" min="0" max="100" required form="chest-content-add-form">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-2">
                            <label class="col-form-label" for="chest-min-count">Мин. количество</label>
                            <input id="chest-min-count" type="number" class="form-control" name="min_count" value="1" min="1" max="999999" required form="chest-content-add-form">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-2">
                            <label class="col-form-label" for="chest-max-count">Макс. количество</label>
                            <input id="chest-max-count" type="number" class="form-control" name="max_count" value="1" min="1" max="999999" required form="chest-content-add-form">
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary btn-sm" type="submit" form="chest-content-add-form">Добавить предмет</button>
            </div>
            <div class="col-md-8">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-none">
                        <thead>
                        <tr><th width="45"></th><th>Предмет</th><th width="105">Шанс, %</th><th width="105">Мин.</th><th width="105">Макс.</th><th width="145"></th></tr>
                        </thead>
                        <tbody>
                        @forelse($item->itemHasItems as $containedItem)
                            @php
                                $updateFormId = 'chest-content-update-'.$containedItem->id;
                                $deleteFormId = 'chest-content-delete-'.$containedItem->id;
                            @endphp
                            <tr style="vertical-align: middle">
                                <td><a href="{{ route('admin.item.info', $containedItem->id) }}" title="Открыть предмет"><img src="{{ $containedItem->image }}" width="36" height="36" style="object-fit:contain" alt="{{ $containedItem->name }}"></a></td>
                                <td><a href="{{ route('admin.item.info', $containedItem->id) }}">[{{ $containedItem->id }}] {{ $containedItem->name }}</a></td>
                                <td><input form="{{ $updateFormId }}" type="number" class="form-control" name="drop_chance" value="{{ $containedItem->pivot->drop_chance }}" min="0" max="100" required></td>
                                <td><input form="{{ $updateFormId }}" type="number" class="form-control" name="min_count" value="{{ $containedItem->pivot->min_count }}" min="1" max="999999" required></td>
                                <td><input form="{{ $updateFormId }}" type="number" class="form-control" name="max_count" value="{{ $containedItem->pivot->max_count }}" min="1" max="999999" required></td>
                                <td class="text-nowrap">
                                    <button class="btn btn-xs btn-warning" type="submit" form="{{ $updateFormId }}">Сохранить</button>
                                    <button class="btn btn-xs btn-danger" type="submit" form="{{ $deleteFormId }}" onclick="return confirm('Удалить предмет из сундука?')">Удалить</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Содержимое не настроено</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
