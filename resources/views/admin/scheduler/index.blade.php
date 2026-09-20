@extends('admin.layout.base')

@section('title', 'Планировщик задач')

@section('body')
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <section class="card card-featured card-featured-primary">
                <header class="card-header">
                    <h2 class="card-title">Игровые задачи</h2>
                    <p class="card-subtitle">В админке доступны только заранее разрешённые операции. Произвольные команды выполнить нельзя.</p>
                </header>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0">
                            <thead>
                            <tr>
                                <th>Задача</th>
                                <th width="125">Состояние</th>
                                <th width="255">Расписание</th>
                                <th width="185">Последний запуск</th>
                                <th width="155">Следующий запуск</th>
                                <th width="145">Действия</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($definitions as $definition)
                                @php
                                    $setting = $settings->get($definition->key);
                                    $status = $setting->last_status;
                                    $statusClass = match($status) {
                                        'success' => 'badge-success',
                                        'failed' => 'badge-danger',
                                        'running' => 'badge-warning',
                                        default => 'badge-secondary',
                                    };
                                    $statusLabel = match($status) {
                                        'success' => 'Успешно',
                                        'failed' => 'Ошибка',
                                        'running' => 'Выполняется',
                                        default => 'Не запускалась',
                                    };
                                    $enabled = $definition->settingsMutable ? $setting->enabled : true;
                                    $frequency = $definition->allows($setting->frequency)
                                        ? $setting->frequency
                                        : $definition->defaultFrequency;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $definition->name }}</strong>
                                        @if(!$definition->settingsMutable)
                                            <span class="badge badge-info ml-1">Критическая</span>
                                        @endif
                                        <div class="small text-muted mt-1">{{ $definition->description }}</div>
                                        <code class="small">{{ $definition->key }}</code>
                                    </td>
                                    <td>
                                        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                        <div class="small mt-2">
                                            <span class="text-{{ $enabled ? 'success' : 'muted' }}">
                                                {{ $enabled ? 'Включена' : 'Отключена' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($definition->settingsMutable)
                                            <form method="post" action="{{ route('admin.scheduled_tasks.update', $definition->key) }}">
                                                @csrf
                                                <input type="hidden" name="enabled" value="0">
                                                <div class="form-group mb-2">
                                                    <select name="frequency" class="form-control form-control-sm">
                                                        @foreach($definition->allowedFrequencies as $availableFrequency)
                                                            <option value="{{ $availableFrequency->value }}" @selected($frequency === $availableFrequency)>
                                                                {{ $availableFrequency->label() }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <label class="mb-0">
                                                        <input type="checkbox" name="enabled" value="1" @checked($enabled)> Включена
                                                    </label>
                                                    <button type="submit" class="btn btn-primary btn-sm">Сохранить</button>
                                                </div>
                                                @if($setting->updatedBy)
                                                    <div class="small text-muted mt-2">
                                                        Изменил: {{ $setting->updatedBy->name }}, {{ $setting->updated_at?->format('d.m.Y H:i') }}
                                                    </div>
                                                @endif
                                            </form>
                                        @else
                                            <strong>{{ $frequency->label() }}</strong>
                                            <div class="small text-muted mt-1">Интервал защищён игровой механикой.</div>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $setting->last_started_at?->format('d.m.Y H:i:s') ?? '—' }}
                                        @if($setting->last_duration_ms !== null)
                                            <div class="small text-muted">{{ number_format($setting->last_duration_ms) }} мс</div>
                                        @endif
                                        @if($setting->last_error)
                                            <div class="small text-danger mt-1" title="{{ $setting->last_error }}">
                                                {{ \Illuminate\Support\Str::limit($setting->last_error, 80) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($enabled)
                                            {{ $frequency->nextRunAt(now())->format('d.m.Y H:i:s') }}
                                        @else
                                            <span class="text-muted">Отключена</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form method="post" action="{{ route('admin.scheduled_tasks.run', $definition->key) }}"
                                              onsubmit="return confirm('Запустить задачу «{{ addslashes($definition->name) }}» сейчас?')">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm btn-block">
                                                <i class="fas fa-play mr-1"></i> Запустить
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <section class="card">
                <header class="card-header">
                    <h2 class="card-title">История запусков</h2>
                    <p class="card-subtitle">Ошибки и ручные запуски записываются всегда, успешные автоматические — не чаще одного раза в 5 минут для каждой задачи.</p>
                </header>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0">
                            <thead>
                            <tr>
                                <th width="150">Начало</th>
                                <th>Задача</th>
                                <th width="105">Источник</th>
                                <th width="105">Статус</th>
                                <th width="100">Время</th>
                                <th width="150">Администратор</th>
                                <th>Результат</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($runs as $run)
                                @php $definition = $definitions[$run->task_key] ?? null; @endphp
                                <tr>
                                    <td>{{ $run->started_at?->format('d.m.Y H:i:s') }}</td>
                                    <td>{{ $definition?->name ?? $run->task_key }}</td>
                                    <td>{{ $run->trigger === 'manual' ? 'Вручную' : 'Планировщик' }}</td>
                                    <td>
                                        <span class="badge {{ $run->status === 'success' ? 'badge-success' : 'badge-danger' }}">
                                            {{ $run->status === 'success' ? 'Успешно' : 'Ошибка' }}
                                        </span>
                                    </td>
                                    <td>{{ $run->duration_ms !== null ? number_format($run->duration_ms).' мс' : '—' }}</td>
                                    <td>{{ $run->initiator?->name ?? '—' }}</td>
                                    <td class="small">
                                        @if($run->error)
                                            <span class="text-danger">{{ $run->error }}</span>
                                        @else
                                            {{ $run->output ?: '—' }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">Запусков пока нет.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($runs->hasPages())
                        <div class="d-flex justify-content-center p-3">
                            {{ $runs->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection
