<div class="row align-items-end mb-2 world-event-target-row">
    @if(!empty($target['id']))
        <input type="hidden" name="stages[{{ $stageIndex }}][targets][{{ $targetIndex }}][id]" value="{{ $target['id'] }}">
    @endif
    <div class="col-md-5 world-event-target-item">
        <div class="form-group mb-0">
            <label>Собираемый предмет</label>
            <select class="form-control" data-target-item name="stages[{{ $stageIndex }}][targets][{{ $targetIndex }}][share_item_id]">
                <option value="">—</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}" @selected((int) ($target['share_item_id'] ?? 0) === $item->id)>[{{ $item->id }}] {{ $item->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-5 world-event-target-monster">
        <div class="form-group mb-0">
            <label>Событийный монстр</label>
            <select class="form-control" data-target-monster name="stages[{{ $stageIndex }}][targets][{{ $targetIndex }}][monster_id]">
                <option value="">—</option>
                @foreach($monsters as $monster)
                    <option value="{{ $monster->id }}" @selected((int) ($target['monster_id'] ?? 0) === $monster->id)>[{{ $monster->id }}] {{ $monster->name }} ({{ $monster->lvl }} ур.)</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group mb-0">
            <label>Вес появления</label>
            <input type="number" min="1" max="10000" class="form-control" name="stages[{{ $stageIndex }}][targets][{{ $targetIndex }}][spawn_weight]" required value="{{ $target['spawn_weight'] ?? 100 }}">
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group mb-0">
            <label>Максимум на карте</label>
            <input type="number" min="1" class="form-control" name="stages[{{ $stageIndex }}][targets][{{ $targetIndex }}][max_active]" value="{{ $target['max_active'] ?? '' }}" placeholder="Без лимита">
        </div>
    </div>
    <div class="col-md-1">
        <button type="button" class="btn btn-danger btn-xs world-event-target-remove">Удалить</button>
    </div>
</div>
