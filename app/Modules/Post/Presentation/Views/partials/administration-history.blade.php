@include('post::partials.window-open', ['title' => __('post.history')])
@if($administrationThread)
    <b>{{ $administrationThread->subject }}</b>
    @foreach($administrationMessages as $message)
        <p><b>{{ $message->is_to_administration ? $message->sender?->name : __('post.administration') }}</b> — {{ $message->created_at->format('d.m.Y H:i') }}</p>
        <div class="post-letter-text black" style="min-height:0">{{ $message->text }}</div>
    @endforeach
@else
    <table class="coll w100 p10h p4v brd2-all" cellspacing="0" cellpadding="0">
        @forelse($administrationThreads as $thread)
            <tr><td><a href="{{ route('post', ['mode' => 'administration', 'reply_to' => $thread->id]) }}"><b>{{ $thread->subject }}</b></a></td>
                <td>{{ __('post.'.$thread->administration_status) }}<br>{{ $thread->administration_last_message_at?->format('d.m.Y H:i') }}</td></tr>
        @empty
            <tr><td>{{ __('post.empty') }}</td></tr>
        @endforelse
    </table>
    {{ $administrationThreads->links() }}
@endif
@include('post::partials.window-close')
