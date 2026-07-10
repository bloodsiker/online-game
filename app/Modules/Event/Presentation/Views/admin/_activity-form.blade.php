{{-- Форма активности: $activity = null для создания, модель — для редактирования --}}

<h4 class="mb-3">Основное</h4>
<div class="row">
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label class="form-label">Название</label>
            <input type="text" name="title" class="form-control" value="{{ old('title', $activity?->title) }}" placeholder="Например: Охота на мышей" required>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-3">
            <label class="form-label">Период</label>
            <select name="period" class="form-control" required>
                @foreach($periods as $period)
                    <option value="{{ $period->value }}" @selected(old('period', $activity?->period->value) === $period->value)>
                        {{ $period->label() }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-3">
            <label class="form-label">Порядок сортировки</label>
            <input type="number" name="sort_order" class="form-control" min="0" value="{{ old('sort_order', $activity?->sort_order ?? 0) }}" required>
        </div>
    </div>
</div>

<div class="form-group mb-3">
    <label class="form-label">Описание</label>
    <textarea id="description-editor" name="description" class="form-control" rows="2">{{ old('description', $activity?->description) }}</textarea>
    <small class="text-muted">Отображается на карточке активности. Кнопка &lt;/&gt; — правка HTML напрямую</small>
</div>

<hr>

<h4 class="mb-3">Условие выполнения</h4>
<div class="row">
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label class="form-label">Монстр</label>
            <select id="sel-monster" name="monster_id" class="form-control">
                <option value=""></option>
                @foreach($monsters as $monster)
                    <option value="{{ $monster->id }}" @selected((int) old('monster_id', $activity?->monster_id) === $monster->id)>
                        [{{ $monster->id }}] {{ $monster->name }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted">Прогресс засчитывается за убийства этого монстра</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-3">
            <label class="form-label">Требуемое кол-во</label>
            <input type="number" name="required_count" class="form-control" min="1" value="{{ old('required_count', $activity?->required_count ?? 1) }}" required>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-3">
            <label class="form-label d-block">Активна</label>
            <div class="form-check form-switch pt-1">
                <input type="checkbox" class="form-check-input" name="is_active" value="1" @checked(old('is_active', $activity?->is_active ?? true))>
            </div>
        </div>
    </div>
</div>

<hr>

<h4 class="mb-3">Награда</h4>
<div class="row">
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label class="form-label">Предмет</label>
            <select id="sel-reward-item" name="reward_share_item_id" class="form-control" required>
                @if($rewardItemSelected)
                    <option value="{{ $rewardItemSelected->id }}" selected>[{{ $rewardItemSelected->id }}] {{ $rewardItemSelected->name }}</option>
                @endif
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-3">
            <label class="form-label">Кол-во</label>
            <input type="number" name="reward_item_amount" class="form-control" min="1" value="{{ old('reward_item_amount', $activity?->reward_item_amount ?? 1) }}" required>
        </div>
    </div>
</div>

<hr>

<h4 class="mb-3">Бонус <small class="text-muted" style="font-size: 60%;">(необязательно)</small></h4>
<div class="row">
    <div class="col-md-3">
        <div class="form-group mb-3">
            <label class="form-label">Тип бонуса</label>
            <select id="sel-bonus-type" name="bonus_reward_type" class="form-control">
                <option value="">— без бонуса —</option>
                @foreach($bonusTypes as $type)
                    <option value="{{ $type->value }}" @selected(old('bonus_reward_type', $activity?->bonus_reward_type?->value) === $type->value)>
                        {{ $type->label() }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group mb-3">
            <label class="form-label">Бонус-предмет</label>
            <select id="sel-bonus-item" name="bonus_reward_share_item_id" class="form-control">
                @if($bonusItemSelected)
                    <option value="{{ $bonusItemSelected->id }}" selected>[{{ $bonusItemSelected->id }}] {{ $bonusItemSelected->name }}</option>
                @endif
            </select>
            <small class="text-muted">Только для типа бонуса «Предмет»</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-3">
            <label class="form-label">Кол-во бонуса</label>
            <input type="number" name="bonus_reward_amount" class="form-control" min="1" value="{{ old('bonus_reward_amount', $activity?->bonus_reward_amount) }}">
        </div>
    </div>
</div>

@include('admin.layout.summernote', ['selector' => '#description-editor', 'placeholder' => 'Убейте Мышь 10 раз'])

@push('footer_scripts')
<script>
    function formatItemOption(item) {
        if (!item.id) return item.text;
        var img = item.image
            ? '<img src="' + item.image + '" style="width:22px;height:22px;object-fit:contain;margin-right:6px;vertical-align:middle;">'
            : '';
        return $('<span>' + img + item.text + '</span>');
    }

    var itemAjax = {
        url: '{{ route('admin.api.items') }}',
        dataType: 'json',
        delay: 250,
        data: function (p) { return { q: p.term, page: p.page || 1 }; },
        processResults: function (data, p) {
            p.page = p.page || 1;
            return { results: data.results, pagination: { more: data.pagination.more } };
        },
        cache: true
    };

    $('#sel-monster').select2({
        theme: 'bootstrap',
        placeholder: '— не привязан —',
        allowClear: true,
        minimumInputLength: 0
    });

    $('#sel-reward-item').select2({
        theme: 'bootstrap',
        placeholder: 'Выберите предмет',
        templateResult: formatItemOption,
        templateSelection: formatItemOption,
        ajax: itemAjax,
        minimumInputLength: 0
    });

    $('#sel-bonus-item').select2({
        theme: 'bootstrap',
        placeholder: 'Выберите предмет',
        allowClear: true,
        templateResult: formatItemOption,
        templateSelection: formatItemOption,
        ajax: itemAjax,
        minimumInputLength: 0
    });

    // Бонус-предмет доступен только при типе бонуса «Предмет»
    function toggleBonusItem() {
        var isItem = $('#sel-bonus-type').val() === 'item';
        $('#sel-bonus-item').prop('disabled', !isItem);
    }
    $('#sel-bonus-type').on('change', toggleBonusItem);
    toggleBonusItem();
</script>
@endpush