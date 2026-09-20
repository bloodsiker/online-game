<div class="forum-mute-notice" role="status">
    <strong>Вам запрещено писать на форуме.</strong>
    <span>Молчание действует до <b>{{ $forumMute->expires_at->format('d.m.Y H:i') }}</b>.</span>
    @if($forumMute->reason)
        <span>Причина: {{ $forumMute->reason }}</span>
    @endif
</div>
