@include('post::partials.window-open', ['title' => 'Письмо'])

@php($isRecipient = $letter->recipient_user_id === auth()->id())

@if($letter->money > 0 || $letter->shareItem)
    <div class="post-capacity tbl-sts_bg-light">
        @if($letter->money > 0)
            Деньги: {{ $letter->money }}
            <span title="Монеты"><img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle"></span>
            @if($letter->money_claimed_at)
                <span class="grn">— получены{{ $isRecipient ? '' : ' адресатом' }}</span>
            @elseif(! $isRecipient)
                — ещё не получены адресатом
            @endif
        @endif

        @if($letter->shareItem)
            {{ $letter->money > 0 ? ' | ' : '' }}Предмет: <b>{{ $letter->shareItem->name }}</b>{{ $letter->item_amount > 1 ? ' '.$letter->item_amount.'шт' : '' }}
            @if($letter->item_claimed_at)
                <span class="grn">— получен{{ $isRecipient ? '' : ' адресатом' }}</span>
            @elseif(! $isRecipient)
                — ещё не получен адресатом
            @endif
        @endif

        @if($isRecipient && $letter->hasUnclaimedAttachments())
            <form method="post" action="{{ route('post.claim', $letter->id) }}" style="display:inline;">
                @csrf
                &nbsp;<b class="butt2 pointer"><b><input value="Забрать" type="submit" style="width: 70px;"></b></b>
            </form>
        @endif
    </div>
@endif

<table class="coll w100 p10h p4v brd2-all" border="0" cellspacing="0" cellpadding="0">
    <col width="70"><col><col width="70"><col>
    <tr class="bg_l">
        <td class="brd2-top"><b class="rdd">Тема:</b></td>
        <td class="brd2-top black">{{ $letter->subject }}</td>
        <td class="brd2-top"><b class="rdd">{{ $isRecipient ? 'От кого:' : 'Кому:' }}</b></td>
        <td class="brd2-top black">
            @if($isRecipient)
                @if($letter->isAdministrationReply())
                    {{ __('post.administration') }}
                @elseif($letter->isSystem())
                    Системное сообщение
                @else
                    {{ $letter->sender?->name ?? '—' }}
                @endif
            @else
                {{ $letter->is_to_administration ? __('post.administration') : ($letter->recipient?->name ?? '—') }}
            @endif
        </td>
    </tr>
</table>

<div class="post-letter-text black">{{ $letter->text }}</div>

@if($letter->shareItem)
    {{-- Слот вложенного предмета, как на проде --}}
    <div style="padding-top: 8px;">
        <table width="60" height="60" cellpadding="0" cellspacing="0" border="0"
               style="float: left; margin: 1px;" background="{{ asset('img/bg/empty_slot.gif') }}">
            <tr>
                @if(! $letter->item_claimed_at || ! $isRecipient)
                    <td valign="bottom" align="left"
                        style="background: url('{{ asset($letter->shareItem->image) }}') center no-repeat; background-size: cover;"
                        data-id="{{ $letter->shareItem->id }}"
                        onmouseover="showItemInfo(this,event,2)"
                        onmouseout="showItemInfo(this,event,0)">
                        @if($letter->item_amount > 1)
                            <div class="bpdig">{{ $letter->item_amount }}</div>
                        @endif
                    </td>
                @else
                    <td valign="bottom">&nbsp;</td>
                @endif
            </tr>
        </table>
        <div style="clear: both;"></div>
    </div>
@endif

<div align="center" style="padding-top: 10px;">
    @if($letter->isAdministrationReply() || $letter->is_to_administration)
        <b class="butt1 pointer"><b><input value="{{ __('post.reply') }}" type="button" style="width:100px"
            onclick="location.href='{{ route('post', ['mode' => 'administration', 'reply_to' => $letter->administration_thread_id ?? $letter->id]) }}'"></b></b>
    @elseif($isRecipient && ! $letter->isSystem())
        <b class="butt1 pointer"><b><input value="Ответить" type="button" style="width: 100px;"
            onclick="location.href='{{ route('post', ['mode' => 'outbox', 'to' => $letter->sender?->name]) }}'"></b></b>
    @endif
    <form method="post" action="{{ route('post.delete', $letter->id) }}" style="display:inline;"
          onsubmit='return confirm(@json("Удалить письмо «".$letter->subject."»?"));'>
        @csrf
        <input type="hidden" name="from" value="{{ $isRecipient ? 'inbox' : 'outpost' }}">
        <b class="butt1 pointer"><b><input value="Удалить" type="submit" style="width: 100px;" class="redd"></b></b>
    </form>
</div>

@include('post::partials.window-close')
