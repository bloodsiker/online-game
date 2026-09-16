<div class="row pt-3 pb-3">
    <div class="col-md-12 mb-3">
        <h2 class="card-title">Требования для надевания / использования</h2>
        <p class="card-subtitle text-muted">Минимальные характеристики персонажа для экипировки или использования предмета</p>
    </div>
    <div class="col-md-4">
        <div class="form-group mb-2">
            <label class="col-form-label">Тип требования</label>
            <select name="type" class="form-control" id="req-type" form="req-form" onchange="updateReqFields()">
                @foreach($requirementTypes as $rType)
                    <option value="{{ $rType->value }}">{{ $rType->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group mb-2" id="req-stat-field" style="display:none">
            <label class="col-form-label">Характеристика</label>
            <select name="stat_key" class="form-control" form="req-form">
                @foreach($playerStatKeys as $key)
                    <option value="{{ $key->value }}">{{ $key->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group mb-2" id="req-skill-field" style="display:none">
            <label class="col-form-label">Навык</label>
            <select name="skill_id" class="form-control" data-plugin-selectTwo form="req-form">
                @foreach($skills as $skill)
                    <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group mb-2">
            <label class="col-form-label">Минимальное значение</label>
            <input type="number" class="form-control" name="min_value" value="1" min="1" form="req-form">
        </div>
        <button class="btn btn-primary btn-sm" type="submit" form="req-form">Добавить требование</button>
    </div>
    <div class="col-md-8">
        <table class="table table-hover table-bordered mb-none">
            <thead>
            <tr>
                <th>Тип</th>
                <th>Условие</th>
                <th width="100">Мин. значение</th>
                <th width="70"></th>
            </tr>
            </thead>
            <tbody>
            @forelse($item->requirements as $req)
                <tr style="vertical-align: middle">
                    <td>{{ $req->type->label() }}</td>
                    <td>{{ $req->label() }}</td>
                    <td>{{ $req->min_value }}</td>
                    <td>
                        <a href="{{ route('admin.item.requirement.delete', ['item' => $item->id, 'requirement' => $req->id]) }}"
                           class="btn btn-xs btn-danger"
                           onclick="return confirm('Удалить?')">Удалить</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">Нет требований</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    function updateReqFields() {
        var type = document.getElementById('req-type').value;
        document.getElementById('req-stat-field').style.display = type === 'stat' ? '' : 'none';
        document.getElementById('req-skill-field').style.display = type === 'skill' ? '' : 'none';
    }

    updateReqFields();
</script>
