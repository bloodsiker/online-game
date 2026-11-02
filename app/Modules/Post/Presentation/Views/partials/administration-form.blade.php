@include('post::partials.window-open', ['title' => __('post.write_administration')])
<p>{{ __('post.hint') }}</p>
<p><a href="{{ route('post', ['mode' => 'administration']) }}"><b>{{ __('post.new') }}</b></a></p>
<form method="post" action="{{ route('post.administration.send') }}">
    @csrf
    @if($administrationThread)
        <input type="hidden" name="thread_id" value="{{ $administrationThread->id }}">
    @endif
    <label for="administration-subject"><b>{{ __('post.subject') }}</b></label><br>
    <input id="administration-subject" class="post-input" name="subject" maxlength="64" required style="width:95%;box-sizing:border-box" value="{{ old('subject', $administrationThread?->subject) }}" @readonly($administrationThread)><br><br>
    <label for="administration-text"><b>{{ __('post.text') }}</b></label><br>
    <textarea id="administration-text" name="text" class="post-input" rows="12" maxlength="10000" required style="width:95%;box-sizing:border-box">{{ old('text') }}</textarea>
    <div style="text-align:center;padding:12px">
        <b class="butt1 pointer"><b><input type="submit" value="{{ __('post.send') }}" style="width:120px"></b></b>
    </div>
</form>
@include('post::partials.window-close')
