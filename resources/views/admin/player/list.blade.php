@extends('admin.layout.base')

@section('title')
    Игроки
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Игрок</th>
                                <th>Email</th>
                                <th>Раса</th>
                                <th width="60">Ур.</th>
                                <th width="90">HP</th>
                                <th width="90">MP</th>
                                <th width="60">Сила</th>
                                <th width="60">Ловк.</th>
                                <th width="60">Разум</th>
                                <th width="60">Инт.</th>
                                <th width="80">Монеты</th>
                                <th width="80">Алмазы</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($players as $player)
                                <tr style="vertical-align: middle">
                                    <td>{{ $player->id }}</td>
                                    <td>{{ $player->user?->name ?? '—' }}</td>
                                    <td>{{ $player->user?->email ?? '—' }}</td>
                                    <td>{{ $player->race?->name ?? '—' }}</td>
                                    <td>{{ $player->lvl }}</td>
                                    <td>{{ $player->hp_now }} / {{ $player->hp_max }}</td>
                                    <td>{{ $player->mp_now }} / {{ $player->mp_max }}</td>
                                    <td>{{ $player->str }}</td>
                                    <td>{{ $player->agil }}</td>
                                    <td>{{ $player->mud }}</td>
                                    <td>{{ $player->intel }}</td>
                                    <td>{{ $player->user?->money ?? 0 }}</td>
                                    <td>{{ $player->user?->diamond ?? 0 }}</td>
                                    <td><a href="{{ route('admin.player.info', $player->id) }}" class="btn btn-xs btn-primary">Детали</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection