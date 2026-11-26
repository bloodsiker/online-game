@extends('admin.layout.base')

@section('title')
    Навыки игрока: {{ $player->user?->name }}
@endsection

@section('body')

    @include('admin.player._nav', ['active' => 'skills'])

    <section class="card">
        <div class="card-body">
                <div class="table-responsive pt-3">
                    <table class="table table-hover table-bordered mb-none">
                        <thead>
                        <tr>
                            <th width="50">ID</th>
                            <th>Навык</th>
                            <th width="80">Уровень</th>
                            <th width="120">Опыт</th>
                            <th width="120">До след. ур.</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($player->skills as $playerSkill)
                            <tr style="vertical-align: middle">
                                <td>{{ $playerSkill->id }}</td>
                                <td>{{ $playerSkill->skill?->name ?? '—' }}</td>
                                <td>{{ $playerSkill->lvl }}</td>
                                <td>{{ $playerSkill->exp }} / {{ $playerSkill->exp_up }}</td>
                                <td>
                                    @php $pct = $playerSkill->exp_diff > 0 ? round($playerSkill->exp * 100 / $playerSkill->exp_up) : 100; @endphp
                                    <div class="progress" style="margin-bottom:0;height:16px;">
                                        <div class="progress-bar" style="width:{{ $pct }}%">{{ $pct }}%</div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Нет навыков</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
        </div>
    </section>

@endsection
