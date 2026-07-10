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
            &nbsp;<b class="butt2 pointer"><b><input value="Забрать" type="button" style="width: 70px;"
                onclick="location.href='{{ route('post.claim', $letter->id) }}'"></b></b>
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
                @if($letter->isSystem())
                    Системное сообщение
                @else
                    {{ $letter->sender?->name ?? '—' }}
                @endif
            @else
                {{ $letter->recipient?->name ?? '—' }}
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
    @if($isRecipient && ! $letter->isSystem())
        <b class="butt1 pointer"><b><input value="Ответить" type="button" style="width: 100px;"
            onclick="location.href='{{ route('post', ['mode' => 'outbox', 'to' => $letter->sender?->name]) }}'"></b></b>
    @endif
    <b class="butt1 pointer"><b><input value="Удалить" type="button" style="width: 100px;" class="redd"
        onclick="if (confirm('Удалить письмо «{{ $letter->subject }}»?')) location.href='{{ route('post.delete', ['id' => $letter->id, 'from' => $isRecipient ? 'inbox' : 'outpost']) }}'"></b></b>
</div>

@include('post::partials.window-close')