@extends('admin.layout.base')

@section('title')
    Клан: {{ $clan->name }}
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="tabs">
                        <ul class="nav nav-tabs">
                            <li class="nav-item active">
                                <a class="nav-link active" data-bs-target="#tab-info" href="#tab-info" data-bs-toggle="tab">Информация</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-members" href="#tab-members" data-bs-toggle="tab">
                                    Члены <span class="badge badge-primary">{{ $clan->members->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-skills" href="#tab-skills" data-bs-toggle="tab">
                                    Скилы <span class="badge badge-primary">{{ $clan->learnedSkills->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-treasury" href="#tab-treasury" data-bs-toggle="tab">
                                    Клан-банк <span class="badge badge-primary">{{ $treasuryLogs->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-warehouse" href="#tab-warehouse" data-bs-toggle="tab">
                                    Хранилище <span class="badge badge-primary">{{ $warehouse->count() }}</span>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">

                            {{-- ИНФОРМАЦИЯ --}}
                            <div id="tab-info" class="tab-pane active">
                                <div class="row pt-3">
                                    <div class="col-md-2 text-center">
                                        @if($clan->icon)
                                            <img src="{{ $clan->icon }}" style="width:80px;height:80px;object-fit:contain;" alt="">
                                        @endif
                                    </div>
                                    <div class="col-md-5">
                                        <table class="table table-sm table-bordered">
                                            <tr><th width="160">Название</th><td>{{ $clan->name }}</td></tr>
                                            <tr><th>Владелец</th><td>{{ $clan->owner?->name ?? '—' }}</td></tr>
                                            <tr><th>Уровень</th><td>{{ $clan->lvl }}</td></tr>
                                            <tr><th>Очки клана</th><td>{{ number_format($clan->points, 0, '', ' ') }}</td></tr>
                                            <tr><th>Казна (монеты)</th><td>{{ number_format($clan->treasury, 0, '', ' ') }}</td></tr>
                                            <tr><th>Вместимость хранилища</th><td>{{ $clan->warehouse_capacity }}</td></tr>
                                        </table>
                                    </div>
                                    <div class="col-md-5">
                                        @if($clan->description)
                                            <p><strong>Описание:</strong></p>
                                            <p class="text-muted">{{ $clan->description }}</p>
                                        @endif
                                        @foreach(['news_1' => 'Новость 1', 'news_2' => 'Новость 2', 'news_3' => 'Новость 3'] as $field => $label)
                                            @if($clan->$field)
                                                <p class="mb-1"><strong>{{ $label }}:</strong> {{ $clan->$field }}</p>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                <div class="mt-2 mb-3">
                                    <a href="{{ route('admin.clans') }}" class="btn btn-success btn-sm">Назад</a>
                                </div>
                            </div>

                            {{-- ЧЛЕНЫ --}}
                            <div id="tab-members" class="tab-pane">
                                <div class="table-responsive pt-3">
                                    <table class="table table-hover table-bordered mb-none">
                                        <thead>
                                        <tr>
                                            <th width="50">ID</th>
                                            <th>Игрок</th>
                                            <th>Email</th>
                                            <th width="150">Роль</th>
                                            <th width="110">Очки в клане</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($clan->members as $member)
                                            <tr style="vertical-align: middle">
                                                <td>{{ $member->id }}</td>
                                                <td>
                                                    @if($member->user)
                                                        <a href="{{ route('admin.player.info', $member->user->player?->id ?? 0) }}" target="_blank">
                                                            {{ $member->user->name }}
                                                        </a>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td class="text-muted small">{{ $member->user?->email ?? '—' }}</td>
                                                <td>
                                                    @if($member->role)
                                                        @if($member->role->is_leader)
                                                            <span class="badge badge-warning">{{ $member->role->name }}</span>
                                                        @else
                                                            <span class="badge badge-default">{{ $member->role->name }}</span>
                                                        @endif
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td>{{ number_format($member->points, 0, '', ' ') }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted">Нет членов</td></tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- СКИЛЫ --}}
                            <div id="tab-skills" class="tab-pane">
                                <div class="table-responsive pt-3">
                                    <table class="table table-hover table-bordered mb-none">
                                        <thead>
                                        <tr>
                                            <th width="45"></th>
                                            <th>Скил</th>
                                            <th>Описание</th>
                                            <th width="120">Текущий уровень</th>
                                            <th width="100">Макс. уровень</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($clan->learnedSkills as $skill)
                                            <tr style="vertical-align: middle">
                                                <td>
                                                    @if($skill->definition?->icon)
                                                        <img src="{{ $skill->definition->icon }}" style="width:32px;height:32px;object-fit:contain;" alt="">
                                                    @endif
                                                </td>
                                                <td>{{ $skill->definition?->name ?? '—' }}</td>
                                                <td class="text-muted small">{{ $skill->definition?->description }}</td>
                                                <td>
                                                    <span class="badge badge-primary">{{ $skill->current_level }}</span>
                                                    @if($skill->definition)
                                                        / {{ $skill->definition->max_level }}
                                                    @endif
                                                </td>
                                                <td>{{ $skill->definition?->max_level ?? '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted">Нет изученных скилов</td></tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- КЛАН-БАНК --}}
                            <div id="tab-treasury" class="tab-pane">
                                <div class="pt-3">
                                    <div class="alert alert-info mb-3 py-2">
                                        <strong>Текущий баланс казны:</strong> {{ number_format($clan->treasury, 0, '', ' ') }} монет
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-none">
                                            <thead>
                                            <tr>
                                                <th width="50">ID</th>
                                                <th width="160">Дата</th>
                                                <th>Игрок</th>
                                                <th width="100">Действие</th>
                                                <th width="110">Сумма</th>
                                                <th width="120">Баланс после</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($treasuryLogs as $log)
                                                <tr style="vertical-align: middle">
                                                    <td>{{ $log->id }}</td>
                                                    <td class="small text-muted">{{ $log->created_at?->format('d.m.Y H:i') }}</td>
                                                    <td>{{ $log->user?->name ?? '—' }}</td>
                                                    <td>
                                                        @if($log->action === 'deposit')
                                                            <span class="badge badge-success">Пополнение</span>
                                                        @elseif($log->action === 'withdraw')
                                                            <span class="badge badge-danger">Снятие</span>
                                                        @else
                                                            <span class="badge badge-info">{{ $log->action }}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ number_format($log->amount, 0, '', ' ') }}</td>
                                                    <td>{{ number_format($log->balance_after, 0, '', ' ') }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="6" class="text-center text-muted">Нет записей</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- ХРАНИЛИЩЕ --}}
                            <div id="tab-warehouse" class="tab-pane">
                                <div class="pt-3">

                                    {{-- Текущее содержимое --}}
                                    <h6 class="text-muted mb-2">Содержимое хранилища</h6>
                                    <div class="table-responsive mb-4">
                                        <table class="table table-hover table-bordered mb-none">
                                            <thead>
                                            <tr>
                                                <th width="45"></th>
                                                <th>Предмет</th>
                                                <th width="80">Кол-во</th>
                                                <th>Положил</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($warehouse as $slot)
                                                <tr style="vertical-align: middle">
                                                    <td>
                                                        @if($slot->item?->itemInfo?->image)
                                                            <img src="{{ $slot->item->itemInfo->image }}" style="width:32px;" alt="">
                                                        @endif
                                                    </td>
                                                    <td>{{ $slot->item?->itemInfo?->name ?? '—' }}</td>
                                                    <td>{{ $slot->count }}</td>
                                                    <td>{{ $slot->depositor?->name ?? '—' }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="text-center text-muted">Хранилище пусто</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    {{-- Логи --}}
                                    <h6 class="text-muted mb-2">Лог операций</h6>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-none">
                                            <thead>
                                            <tr>
                                                <th width="50">ID</th>
                                                <th width="160">Дата</th>
                                                <th>Игрок</th>
                                                <th width="45"></th>
                                                <th>Предмет</th>
                                                <th width="100">Действие</th>
                                                <th width="80">Кол-во</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($warehouseLogs as $log)
                                                <tr style="vertical-align: middle">
                                                    <td>{{ $log->id }}</td>
                                                    <td class="small text-muted">{{ $log->created_at?->format('d.m.Y H:i') }}</td>
                                                    <td>{{ $log->user?->name ?? '—' }}</td>
                                                    <td>
                                                        @if($log->item?->itemInfo?->image)
                                                            <img src="{{ $log->item->itemInfo->image }}" style="width:28px;" alt="">
                                                        @endif
                                                    </td>
                                                    <td>{{ $log->item?->itemInfo?->name ?? '—' }}</td>
                                                    <td>
                                                        @if($log->action?->isPut())
                                                            <span class="badge badge-success">{{ $log->action->label() }}</span>
                                                        @else
                                                            <span class="badge badge-danger">{{ $log->action?->label() ?? $log->action }}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $log->count }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="7" class="text-center text-muted">Нет записей</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection