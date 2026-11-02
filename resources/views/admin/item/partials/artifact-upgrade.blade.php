<div class="p-3">
    <p>Каждая ступень даёт +5% от собственных характеристик. Максимум: +{{ \App\Modules\Structure\Blacksmith\Domain\Services\ArtifactUpgradeRules::MAX_LEVEL }}. Успех — 100%.</p>
    <p>На каждую ступень расходуется одна копия этого же артефакта +0. Цена и дополнительные материалы одинаковые для всех ступеней.</p>
    <div class="row">
        <div class="col-md-4">
            <label>Заточка доступна</label>
            <select name="is_enabled" class="form-control mb-3" form="artifact-upgrade-settings">
                <option value="0" @selected(! $item->artifactUpgradeConfig?->is_enabled)>Нет</option>
                <option value="1" @selected($item->artifactUpgradeConfig?->is_enabled)>Да</option>
            </select>
        </div>
        <div class="col-md-4">
            <label>Стоимость одной заточки, монет</label>
            <input type="number" min="0" max="2147483647" name="gold_cost" value="{{ $item->artifactUpgradeConfig?->gold_cost ?? 0 }}" class="form-control mb-3" form="artifact-upgrade-settings" required>
        </div>
    </div>
    <button type="submit" form="artifact-upgrade-settings" class="btn btn-primary">Сохранить настройки заточки</button>
    <h4 class="mt-4">Дополнительные материалы</h4>
    <div class="row">
        <div class="col-md-6">
            <select name="share_item_id" class="form-control" data-plugin-selectTwo form="artifact-upgrade-material-add" required>
                @foreach($upgradeTargets as $materialOption)
                    <option value="{{ $materialOption->id }}">[{{ $materialOption->id }}] {{ $materialOption->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><input type="number" min="1" max="1000000" name="count" value="1" class="form-control" form="artifact-upgrade-material-add" required></div>
        <div class="col-md-4"><button type="submit" form="artifact-upgrade-material-add" class="btn btn-primary">Добавить / изменить</button></div>
    </div>
    <table class="table table-bordered mt-3">
        <thead><tr><th>Материал</th><th>Количество</th><th></th></tr></thead>
        <tbody>
        @forelse($item->artifactUpgradeConfig?->materials ?? [] as $material)
            <tr>
                <td><img src="{{ $material->image }}" width="36" alt=""> {{ $material->name }}</td>
                <td>{{ $material->pivot->count }}</td>
                <td><button type="submit" form="artifact-upgrade-material-delete-{{ $material->id }}" class="btn btn-danger btn-sm">Удалить</button></td>
            </tr>
        @empty
            <tr><td colspan="3">Дополнительные материалы не требуются.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
