@extends('admin.layout.base')

@section('title', 'Подписки на открытие')

@section('body')
    <section class="card">
        <div class="card-body">
            <form method="get" action="{{ route('admin.launch-subscriptions.index') }}" class="row g-3 align-items-end mb-3">
                <div class="col-md-5">
                    <label class="form-label" for="subscription-search">Email</label>
                    <input class="form-control" id="subscription-search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="254" placeholder="Поиск по email">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="subscription-status">Статус уведомления</label>
                    <select class="form-control" id="subscription-status" name="status">
                        <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>Все</option>
                        <option value="pending" @selected(($filters['status'] ?? 'all') === 'pending')>Ожидает отправки</option>
                        <option value="sent" @selected(($filters['status'] ?? 'all') === 'sent')>Отправлено</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="subscription-locale">Язык</label>
                    <select class="form-control" id="subscription-locale" name="locale">
                        <option value="all" @selected(($filters['locale'] ?? 'all') === 'all')>Все</option>
                        <option value="ru" @selected(($filters['locale'] ?? 'all') === 'ru')>Русский</option>
                        <option value="uk" @selected(($filters['locale'] ?? 'all') === 'uk')>Українська</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Найти</button>
                    <a class="btn btn-default" href="{{ route('admin.launch-subscriptions.index') }}">Сбросить</a>
                </div>
            </form>

            <p class="text-muted">Найдено подписчиков: <strong>{{ $subscribers->total() }}</strong>. Новые подписки отображаются первыми.</p>
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-none">
                    <thead>
                    <tr>
                        <th style="width:70px">ID</th>
                        <th>Email</th>
                        <th style="width:130px">Язык</th>
                        <th style="width:170px">Дата подписки</th>
                        <th style="width:180px">Статус уведомления</th>
                        <th style="width:170px">Дата отправки</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($subscribers as $subscriber)
                        <tr>
                            <td>{{ $subscriber->id }}</td>
                            <td>{{ $subscriber->email }}</td>
                            <td>{{ $subscriber->locale === 'uk' ? 'Українська' : 'Русский' }}</td>
                            <td>{{ $subscriber->created_at?->format('d.m.Y H:i') }}</td>
                            <td>
                                @if($subscriber->notified_at)
                                    <span class="badge badge-success">Отправлено</span>
                                @else
                                    <span class="badge badge-default">Ожидает отправки</span>
                                @endif
                            </td>
                            <td>{{ $subscriber->notified_at?->format('d.m.Y H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Подписки не найдены.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $subscribers->onEachSide(2)->links('admin.pagination') }}</div>
        </div>
    </section>
@endsection
