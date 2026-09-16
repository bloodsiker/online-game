<section class="card mt-3 mb-0">
    <header class="card-header">
        <h2 class="card-title">Материалы для апгрейда редкости</h2>
        <p class="card-subtitle text-muted">Списываются вместе с монетами при превращении предмета в настроенный результат.</p>
    </header>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group mb-2">
                    <label class="col-form-label">Материал</label>
                    <select name="share_item_id" class="form-control" data-plugin-selectTwo required form="rarity-upgrade-material-add-form">
                        @foreach($upgradeTargets as $target)
                            <option value="{{ $target->id }}">[{{ $target->id }}] {{ $target->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-2">
                    <label class="col-form-label">Количество</label>
                    <input type="number" min="1" name="count" value="1" class="form-control" required form="rarity-upgrade-material-add-form">
                </div>
                <button class="btn btn-primary btn-sm" type="submit" form="rarity-upgrade-material-add-form">Добавить материал</button>
            </div>
            <div class="col-md-8">
                <table class="table table-hover table-bordered mb-none">
                    <thead><tr><th width="45"></th><th>Материал</th><th width="110">Количество</th><th width="70"></th></tr></thead>
                    <tbody>
                    @forelse($item->rarityUpgradeMaterials as $material)
                        <tr style="vertical-align: middle">
                            <td><img src="{{ $material->image }}" width="36" alt=""></td>
                            <td><a href="{{ route('admin.item.info', $material->id) }}">{{ $material->name }}</a></td>
                            <td>{{ $material->pivot->count }}</td>
                            <td>
                                <button class="btn btn-xs btn-danger" type="submit"
                                        form="rarity-upgrade-material-delete-form-{{ $material->id }}"
                                        onclick="return confirm('Удалить?')">Удалить</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">Материалы не требуются</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
