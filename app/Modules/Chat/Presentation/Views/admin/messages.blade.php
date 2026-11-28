@extends('admin.layout.base')

@section('title', 'Сообщения игроков')

@section('body')
    @include('chat::admin.nav')

    <section class="card">
        <header class="card-header">
            <h2 class="card-title" id="chat-player-messages-title">Сообщения игроков</h2>
        </header>
        <div class="card-body">
            <div class="table-responsive" tabindex="0" role="region" aria-labelledby="chat-player-messages-title">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                    <tr>
                        <th scope="col">Время</th>
                        <th scope="col">Канал</th>
                        <th scope="col">Отправитель</th>
                        <th scope="col">Получатель</th>
                        <th scope="col">Сообщение</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($messages as $message)
                        @php
                            [$label, $badge] = match ($message->channel) {
                                \App\Modules\Chat\Domain\Enums\ChatChannel::Clan => ['Клан', 'success'],
                                \App\Modules\Chat\Domain\Enums\ChatChannel::Private => ['Приват', 'warning'],
                                default => $message->type === \App\Modules\Chat\Domain\Enums\ChatMessageType::Mention
                                    ? ['Личное', 'info'] : ['Общий', 'primary'],
                            };
                        @endphp
                        <tr>
                            <td class="text-nowrap">{{ $message->created_at?->format('d.m.Y H:i:s') ?? '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $badge }}">{{ $label }}</span>
                                @if($message->clan !== null)
                                    <span class="d-block small">{{ $message->clan->name }}</span>
                                @endif
                            </td>
                            <td>{{ $message->sender?->name ?? '—' }}</td>
                            <td>{{ $message->targetName() ?? '—' }}</td>
                            <td style="width:50%;white-space:pre-wrap;overflow-wrap:anywhere;">{{ $message->message }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Сообщений нет.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($messages->hasPages())
                <div class="mt-3">{{ $messages->onEachSide(2)->links('admin.pagination') }}</div>
            @endif
        </div>
    </section>
@endsection
