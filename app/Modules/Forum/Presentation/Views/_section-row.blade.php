@php
    $latestTopic = $forumSection->latestTopic;
    $lastAuthor = $latestTopic?->lastPostAuthor ?? $latestTopic?->author;
    $lastAuthorClan = $lastAuthor?->clanMembership?->clan;
    $replyCount = max(0, (int) ($forumSection->posts_total ?? 0) - (int) $forumSection->topics_count);
    $pluralize = static function (int $number, string $one, string $few, string $many): string {
        $lastTwoDigits = $number % 100;

        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 14) {
            return $many;
        }

        return match ($number % 10) {
            1 => $one,
            2, 3, 4 => $few,
            default => $many,
        };
    };
    $lastPostAt = $latestTopic?->last_post_at ?? $latestTopic?->created_at;
    $lastPostDate = $lastPostAt?->isToday()
        ? 'Сегодня '.$lastPostAt->format('H:i:s')
        : $lastPostAt?->format('d.m.Y H:i:s');
@endphp

<tr class="forum-home-section-row">
    <td class="forum-home-section-icon" aria-hidden="true">
        <i></i>
    </td>
    <td class="forum-home-section-name">
        <a href="{{ route('forum.section', ['slug' => $forumSection->slug]) }}"><b>{{ $forumSection->name }}</b></a>
        @if($forumSection->description)
            <div>{{ $forumSection->description }}</div>
        @endif
    </td>
    <td class="forum-home-section-stats">
        <b>{{ $forumSection->topics_count }}</b> {{ $pluralize((int) $forumSection->topics_count, 'тема', 'темы', 'тем') }}<br>
        <b>{{ $replyCount }}</b> {{ $pluralize($replyCount, 'ответ', 'ответа', 'ответов') }}
    </td>
    <td class="forum-home-section-latest">
        @if($latestTopic)
            @if($lastAuthor)
                <div class="forum-home-last-author">
                    @if($lastAuthorClan?->icon)
                        <a href="{{ route('clan.public', ['clan' => $lastAuthorClan->id]) }}" target="_blank" title="Информация о клане {{ $lastAuthorClan->name }}">
                            <img class="forum-home-clan-icon" src="{{ Storage::disk('public')->url($lastAuthorClan->icon) }}" width="13" height="13" alt="{{ $lastAuthorClan->name }}">
                        </a>
                    @endif
                    <b>{{ $lastAuthor->name }}@if($lastAuthor->player?->lvl) [{{ $lastAuthor->player->lvl }}]@endif</b>
                    <a href="{{ route('info.user', ['id' => $lastAuthor->id]) }}" target="_blank" title="Информация о персонаже">
                        <img class="forum-user-info-icon" src="{{ asset('main/images/player_info.gif') }}" width="10" height="10" alt="i">
                    </a>
                </div>
            @endif
            <a class="forum-home-last-topic" href="{{ route('forum.topic', ['id' => $latestTopic->id]) }}" title="{{ $latestTopic->title }}">{{ $latestTopic->title }}</a>
            @if($lastPostDate)
                <div class="forum-home-last-date">{{ $lastPostDate }}</div>
            @endif
        @else
            <span class="forum-home-no-topics">Тем пока нет</span>
        @endif
    </td>
</tr>
@unless($loop->last)
    <tr class="forum-home-section-divider" aria-hidden="true">
        <td colspan="4"><div></div></td>
    </tr>
@endunless
