@extends('admin.layout.base')

@section('title')
    Реферальная система — Статистика
@endsection

@section('body')

<div class="row">
    <div class="col-md-12">
        <section class="card">
            <header class="card-header">
                <h2 class="card-title">
                    Рефералы
                    <a href="{{ route('admin.referral.stages') }}" class="btn btn-sm btn-default pull-right">← Этапы наград</a>
                </h2>
            </header>
            <div class="card-body">
                <table class="table table-bordered table-hover mb-none">
                    <thead>
                        <tr>
                            <th>Пригласил</th>
                            <th>Новый игрок</th>
                            <th width="80">Уровень</th>
                            <th width="150">Дата</th>
                            <th>Выданные награды</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($referrals as $ref)
                        <tr>
                            <td>{{ $ref->referrer?->name ?? '—' }}</td>
                            <td>{{ $ref->referred?->name ?? '—' }}</td>
                            <td>{{ $ref->referred?->player?->lvl ?? '—' }}</td>
                            <td>{{ $ref->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                @forelse($ref->claims as $claim)
                                    <span class="badge badge-success">
                                        Ур.{{ $claim->stage->level_threshold }}: {{ $claim->stage->description() }}
                                    </span>
                                @empty
                                    <span class="text-muted">—</span>
                                @endforelse
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Нет данных</td></tr>
                    @endforelse
                    </tbody>
                </table>
                <div class="mt-3">{{ $referrals->links() }}</div>
            </div>
        </section>
    </div>
</div>

@endsection
