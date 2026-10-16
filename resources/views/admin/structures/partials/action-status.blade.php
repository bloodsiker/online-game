<form action="{{ route('admin.structure.action.active', [$structure->id, $action->id]) }}" method="post">
    @csrf
    <select name="is_active" class="form-control form-control-sm" aria-label="Активность действия {{ $action->name }}" onchange="this.form.requestSubmit()">
        <option value="1" @selected($action->pivot->is_active)>Активно</option>
        <option value="0" @selected(!$action->pivot->is_active)>Отключено</option>
    </select>
</form>
