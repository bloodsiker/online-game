@extends('admin.layout.base')

@section('title', 'Уровни клана')

@section('body')
    <section class="card">
        <div class="card-body">
            <p class="text-muted">Клан получает уровень автоматически, когда его общий опыт достигает указанного порога. После изменения порогов уровни существующих кланов пересчитываются.</p>

            @foreach($levels as $clanLevel)
                <form id="update-clan-level-{{ $clanLevel->id }}" method="post" action="{{ route('admin.clan_level.update', $clanLevel) }}">
                    @csrf
                </form>
                @if($clanLevel->level > 1)
                    <form id="delete-clan-level-{{ $clanLevel->id }}" method="post" action="{{ route('admin.clan_level.delete', $clanLevel) }}">
                        @csrf
                    </form>
                @endif
            @endforeach

            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-4">
                    <thead>
                    <tr>
                        <th width="110">Уровень</th>
                        <th>Общий опыт для уровня</th>
                        <th width="220"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($levels as $clanLevel)
                        <tr>
                            <td><input form="update-clan-level-{{ $clanLevel->id }}" class="form-control" type="number" name="level" min="1" max="999" value="{{ $clanLevel->level }}" @readonly($clanLevel->level === 1) required></td>
                            <td><input form="update-clan-level-{{ $clanLevel->id }}" class="form-control" type="number" name="experience_required" min="0" step="0.01" value="{{ $clanLevel->experience_required }}" @readonly($clanLevel->level === 1) required></td>
                            <td class="text-right">
                                <button type="submit" form="update-clan-level-{{ $clanLevel->id }}" class="btn btn-xs btn-primary">Сохранить</button>
                                @if($clanLevel->level > 1)
                                    <button type="submit" form="delete-clan-level-{{ $clanLevel->id }}" class="btn btn-xs btn-danger">Удалить</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <h4 class="mb-3">Добавить уровень</h4>
            <form method="post" action="{{ route('admin.clan_level.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Уровень</label>
                        <input class="form-control" type="number" name="level" min="2" max="999" required>
                    </div>
                    <div class="col-md-5 form-group">
                        <label>Общий опыт для уровня</label>
                        <input class="form-control" type="number" name="experience_required" min="0" step="0.01" required>
                    </div>
                    <div class="col-md-2 form-group d-flex align-items-end">
                        <button type="submit" class="btn btn-success">Добавить</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection
