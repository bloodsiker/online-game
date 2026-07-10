@include('post::partials.window-open', ['title' => 'Входящие сообщения'])

<div class="post-capacity tbl-sts_bg-light">
    Вместимость: <span class="rdd">{{ $inboxCount }}</span> /{{ $capacity }}
</div>

@if($letters->isEmpty())
    <div align="center" class="black" style="padding-top: 10px;"><b>У Вас нет писем!</b></div>
@else
    <form method="POST" action="{{ route('post.bulk') }}" id="bulk-form" style="margin: 0;">
        @csrf
        <input type="hidden" name="action" id="bulk-action" value="">
        <input type="hidden" name="from" value="inbox">

        <table class="coll w100 p10h p4v brd2-all" border="0" cellspacing="0" cellpadding="0">
            <col width="20"><col><col><col width="80"><col width="60">
            <tr class="bg_l black">
                <td class="brd2-top brd2-bt">&nbsp;</td>
                <td class="brd2-top brd2-bt"><b>Тема</b></td>
                <td class="brd2-top brd2-bt"><b>Отправитель</b></td>
                <td class="brd2-top brd2-bt"><b>Дата</b></td>
                <td class="brd2-top brd2-bt"><b>Хранение</b></td>
            </tr>
            @foreach($letters as $item)
                <tr class="bg_l {{ $item->isRead() ? 'post-read' : 'post-unread' }} {{ $letter && $letter->id === $item->id ? 'post-row-active' : '' }}">
                    <td class="brd2-top" align="center">
                        <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="bulk-check">
                    </td>
                    <td class="brd2-top">
                        @if($item->isRead())
                            <a href="{{ route('post.letter', $item->id) }}">{{ $item->subject }}</a>
                        @else
                            <a href="{{ route('post.letter', $item->id) }}" class="rdd"><b>{{ $item->subject }}</b></a>
                        @endif
                        @if($item->money > 0 && ! $item->money_claimed_at)
                            <span title="К письму приложены деньги"><img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle"></span>
                        @endif
                        @if($item->shareItem && ! $item->item_claimed_at)
                            <span title="К письму приложен предмет: {{ $item->shareItem->name }}{{ $item->item_amount > 1 ? ' ('.$item->item_amount.'шт)' : '' }}"><img src="{{ asset($item->shareItem->image) }}" width="14" height="14" align="absmiddle" style="object-fit: contain;"></span>
                        @endif
                    </td>
                    <td class="brd2-top">
                        @if($item->isSystem())
                            Системное сообщение
                        @else
                            {{ $item->sender?->name ?? '—' }}
                        @endif
                    </td>
                    <td class="brd2-top" nowrap>{{ $item->created_at->format('d.m.y H:i') }}</td>
                    <td class="brd2-top" nowrap>{{ $item->storageLeft() }}</td>
                </tr>
            @endforeach
        </table>

        <div style="padding: 4px 2px;">
            <label class="pointer"><input type="checkbox" id="bulk-check-all"> выделить все</label>
        </div>

        <div style="padding-top: 4px;">
            <b class="butt1 pointer"><b><input value="Забрать ценности" type="button" style="width: 120px;" onclick="submitBulk('claim')"></b></b>
            <b class="butt1 pointer"><b><input value="Удалить" type="button" style="width: 90px;" class="redd" onclick="submitBulk('delete')"></b></b>
            <b class="butt1 pointer"><b><input value="Забрать и удалить" type="button" style="width: 130px;" onclick="submitBulk('claim_delete')"></b></b>
        </div>
    </form>

    <script>
        document.getElementById('bulk-check-all').onclick = function () {
            document.querySelectorAll('.bulk-check').forEach(cb => { cb.checked = this.checked; });
        };
        function submitBulk(action) {
            if (! document.querySelector('.bulk-check:checked')) {
                alert('Выберите письма.');
                return;
            }
            if (action !== 'claim' && ! confirm('Удалить выбранные письма?')) return;
            document.getElementById('bulk-action').value = action;
            document.getElementById('bulk-form').submit();
        }
    </script>
@endif

@include('post::partials.window-close')