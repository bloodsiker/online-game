@extends('admin.layout.base')

@section('title')
    Квесты игрока: {{ $player->user?->name }}
@endsection

@section('body')

    @include('admin.player._nav', ['active' => 'quests'])

    <section class="card">
        <header class="card-header">
            <h2 class="card-title">Активные квесты ({{ $quests->count() }})</h2>
            <p class="text-muted mb-0">Выполнено квестов: {{ $completedCount }}</p>
        </header>
        <div class="card-body">
            @forelse($quests as $quest)
                <div class="mb-4">
                    <h5 class="mb-1"><a href="{{ route('admin.quest.info', $quest->quest_id) }}">{{ $quest->quest_title }}</a> <span class="badge badge-secondary">{{ $quest->type }}</span></h5>
                    <p class="mb-1 text-muted">
                        Этап {{ $quest->stage_order }}@if($quest->stage_title): {{ $quest->stage_title }}@endif
                        · взят {{ \Illuminate\Support\Carbon::parse($quest->created_at)->format('d.m.Y H:i') }}
                    </p>
                    @if($quest->objectives->isNotEmpty())
                        <table class="table table-sm table-bordered mb-0">
                            <thead><tr><th>Цель</th><th width="130">Прогресс</th></tr></thead>
                            <tbody>
                            @foreach($quest->objectives as $objective)
                                <tr>
                                    <td>{{ $objective->text ?: $objective->type.' → '.$objective->target_type.' #'.$objective->target_id }}</td>
                                    <td>{{ min($objective->current, $objective->required_amount) }} / {{ $objective->required_amount }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            @empty
                <p class="text-muted mb-0">Активных квестов нет.</p>
            @endforelse
        </div>
    </section>

@endsection
