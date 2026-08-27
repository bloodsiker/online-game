@extends('admin.layout.base')

@section('title', 'Клановый навык: '.$skill->name)

@section('body')
    @include('admin.clan_skill.partials.definition-form', ['action' => route('admin.clan_skill.edit', $skill), 'skill' => $skill])

    <section class="card"><div class="card-body">
        <h4 class="mb-3">Уровни и требования</h4>
        @foreach($skill->levels as $level)
            <div class="border rounded p-3 mb-3">
                <form action="{{ route('admin.clan_skill.level.update', [$skill, $level]) }}" method="post" class="row align-items-end">
                    @csrf
                    <div class="col-md-2"><label>Уровень</label><input class="form-control" type="number" min="1" name="level" value="{{ $level->level }}"></div>
                    <div class="col-md-3"><label>Уровень клана</label><input class="form-control" type="number" min="1" name="required_clan_level" value="{{ $level->required_clan_level }}"></div>
                    <div class="col-md-3"><label>Бонусные очки</label><input class="form-control" type="number" min="0" name="required_bonus_points" value="{{ $level->required_bonus_points }}"></div>
                    <div class="col-md-3"><label>Выдаваемый маг. навык</label><select class="form-control" name="magic_skill_id"><option value="">—</option>@foreach($magicSkills as $magicSkill)<option value="{{ $magicSkill->id }}" @selected($level->magic_skill_id === $magicSkill->id)>{{ $magicSkill->name }}</option>@endforeach</select></div>
                    <div class="col-md-1"><button class="btn btn-primary btn-sm">Сохранить</button></div>
                </form>
                <div class="mt-3"><b>Требуемые предметы:</b>
                    @forelse($level->itemRequirements as $requirement)
                        <span class="badge badge-info ml-1">{{ $requirement->shareItem?->name }} × {{ $requirement->count }} <a class="text-white" href="{{ route('admin.clan_skill.item.delete', [$skill, $level, $requirement]) }}" onclick="return confirm('Удалить требование?')">×</a></span>
                    @empty <span class="text-muted">нет</span>
                    @endforelse
                </div>
                <form action="{{ route('admin.clan_skill.item.add', [$skill, $level]) }}" method="post" class="row align-items-end mt-2">@csrf
                    <div class="col-md-5"><label>Предмет</label><select class="form-control" name="share_item_id" required><option value="">Выберите предмет</option>@foreach($items as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach</select></div>
                    <div class="col-md-2"><label>Количество</label><input class="form-control" type="number" min="1" name="count" value="1"></div>
                    <div class="col-md-2"><button class="btn btn-success btn-sm">Добавить предмет</button></div>
                    <div class="col-md-2"><a class="btn btn-danger btn-sm" href="{{ route('admin.clan_skill.level.delete', [$skill, $level]) }}" onclick="return confirm('Удалить уровень и его требования?')">Удалить уровень</a></div>
                </form>
            </div>
        @endforeach
        <hr><h5>Добавить уровень</h5>
        <form action="{{ route('admin.clan_skill.level.add', $skill) }}" method="post" class="row align-items-end">@csrf
            <div class="col-md-2"><label>Уровень</label><input class="form-control" type="number" min="1" name="level" required></div>
            <div class="col-md-3"><label>Уровень клана</label><input class="form-control" type="number" min="1" name="required_clan_level" value="1" required></div>
            <div class="col-md-3"><label>Бонусные очки</label><input class="form-control" type="number" min="0" name="required_bonus_points" value="0" required></div>
            <div class="col-md-3"><label>Выдаваемый маг. навык</label><select class="form-control" name="magic_skill_id"><option value="">—</option>@foreach($magicSkills as $magicSkill)<option value="{{ $magicSkill->id }}">{{ $magicSkill->name }}</option>@endforeach</select></div>
            <div class="col-md-1"><button class="btn btn-success btn-sm">Добавить</button></div>
        </form>
    </div></section>
@endsection
