@extends('admin.layout.base')

@section('title')
    События — Активности
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="mb-3">
                        <a href="{{ route('admin.event.activity.create') }}" class="btn btn-sm btn-primary">Создать активность</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th width="110">Период</th>
                                <th>Название</th>
                                <th>Монстр</th>
                                <th width="70">Кол-во</th>
                                <th>Награда</th>
                                <th>Бонус</th>
                                <th width="90">Порядок</th>
                                <th width="80">Активна</th>
                                <th width="180"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($activities as $item)
                                <tr style="vertical-align: middle" class="{{ $item->is_active ? '' : 'text-muted' }}">
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->period->label() }}</td>
                                    <td><a href="{{ route('admin.event.activity.edit', $item->id) }}"><b>{{ $item->title }}</b></a></td>
                                    <td>{{ $item->monster?->name ?? '—' }}</td>
                                    <td>{{ $item->required_count }}</td>
                                    <td>
                                        {{ $item->rewardItem?->name ?? '—' }}
                                        @if($item->reward_item_amount > 1)
                                            <span class="text-muted">{{ $item->reward_item_amount }}шт</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->bonus_reward_type === null)
                                            —
                                        @elseif($item->bonus_reward_type === \App\Modules\Event\Domain\Enums\ActivityBonusRewardType::ITEM)
                                            {{ $item->bonusRewardItem?->name ?? '—' }}
                                            @if($item->bonus_reward_amount > 1)
                                                <span class="text-muted">{{ $item->bonus_reward_amount }}шт</span>
                                            @endif
                                        @else
                                            {{ $item->bonus_reward_type->label() }}: {{ $item->bonus_reward_amount }}
                                        @endif
                                    </td>
                                    <td>{{ $item->sort_order }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $item->is_active ? 'badge-success' : 'badge-default' }}">
                                            {{ $item->is_active ? 'Да' : 'Нет' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.event.activity.edit', $item->id) }}" class="btn btn-xs btn-primary">Изменить</a>
                                        <a href="{{ route('admin.event.activity.toggle', $item->id) }}" class="btn btn-xs btn-warning">
                                            {{ $item->is_active ? 'Выкл' : 'Вкл' }}
                                        </a>
                                        <a href="{{ route('admin.event.activity.delete', $item->id) }}"
                                           class="btn btn-xs btn-danger"
                                           onclick="return confirm('Удалить активность «{{ $item->title }}»?')">Удалить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center text-muted">Нет активностей</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection