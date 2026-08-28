@extends('admin.layout.base')

@section('title')
    Квесты предмета: {{ $item->name }}
@endsection

@section('body')

    @include('admin.item.navigation', ['item' => $item])

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <header class="card-header">
                    <h2 class="card-title">Используется в целях квестов: {{ $item->name }}</h2>
                    <p class="card-subtitle text-muted">Сбор квестового предмета и передача предмета НПС</p>
                </header>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="70">ID</th>
                                <th>Квест</th>
                                <th width="160">Роль предмета</th>
                                <th width="170">Этап</th>
                                <th>Описание цели</th>
                                <th width="110">Количество</th>
                                <th width="110">Шанс</th>
                                <th width="100"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($objectiveUsages as $objective)
                                <tr style="vertical-align: middle">
                                    <td>{{ $objective->quest_id }}</td>
                                    <td>
                                        <strong>{{ $objective->quest?->title ?? 'Квест удалён' }}</strong>
                                        @if($objective->quest)
                                            <br><small class="text-muted">{{ $objective->quest->getTypeLabel() }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($objective->type === 'collect')
                                            <span class="badge badge-info">Сбор</span>
                                        @elseif($objective->type === 'deliver')
                                            <span class="badge badge-warning">Передача</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $objective->type }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($objective->stage)
                                            {{ $objective->stage->title ?: 'Этап '.$objective->stage->order }}
                                        @else
                                            <span class="text-muted">Без этапа</span>
                                        @endif
                                    </td>
                                    <td>{{ $objective->description ?: '—' }}</td>
                                    <td>{{ $objective->required_amount }}</td>
                                    <td>
                                        @if($objective->drop_chance !== null)
                                            {{ rtrim(rtrim(number_format((float) $objective->drop_chance, 2, '.', ''), '0'), '.') }}%
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        @if($objective->quest)
                                            <a href="{{ route('admin.quest.info', $objective->quest_id) }}" class="btn btn-xs btn-primary">Открыть</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Предмет не используется в целях квестов.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <header class="card-header">
                    <h2 class="card-title">Выдаётся в награду за квесты</h2>
                    <p class="card-subtitle text-muted">Квесты, после завершения которых игрок получает этот предмет</p>
                </header>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="70">ID</th>
                                <th>Квест</th>
                                <th width="160">Тип квеста</th>
                                <th width="130">Количество</th>
                                <th width="100"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($rewardUsages as $reward)
                                <tr style="vertical-align: middle">
                                    <td>{{ $reward->quest_id }}</td>
                                    <td><strong>{{ $reward->quest?->title ?? 'Квест удалён' }}</strong></td>
                                    <td>{{ $reward->quest?->getTypeLabel() ?? '—' }}</td>
                                    <td>{{ $reward->amount }}</td>
                                    <td>
                                        @if($reward->quest)
                                            <a href="{{ route('admin.quest.info', $reward->quest_id) }}" class="btn btn-xs btn-primary">Открыть</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Предмет не выдаётся в награду за квесты.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection
