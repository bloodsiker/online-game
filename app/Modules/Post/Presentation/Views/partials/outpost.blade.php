@include('post::partials.window-open', ['title' => 'Отправленные сообщения'])

@if($letters->isEmpty())
    <div align="center" class="black" style="padding-top: 10px;"><b>У Вас нет отправленных писем!</b></div>
@else
    <form method="POST" action="{{ route('post.bulk') }}" id="bulk-form" style="margin: 0;">
        @csrf
        <input type="hidden" name="action" id="bulk-action" value="">
        <input type="hidden" name="from" value="outpost">

        <table class="coll w100 p10h p4v brd2-all" border="0" cellspacing="0" cellpadding="0">
            <col width="20"><col><col><col width="80">
            <tr class="bg_l black">
                <td class="brd2-top brd2-bt">&nbsp;</td>
                <td class="brd2-top brd2-bt"><b>Тема</b></td>
                <td class="brd2-top brd2-bt"><b>Получатель</b></td>
                <td class="brd2-top brd2-bt"><b>Дата</b></td>
            </tr>
            @foreach($letters as $item)
                <tr class="bg_l {{ $item->isRead() ? 'post-read' : '' }} {{ $letter && $letter->id === $item->id ? 'post-row-active' : '' }}">
                    <td class="brd2-top" align="center">
                        <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="bulk-check">
                    </td>
                    <td class="brd2-top">
                        @if($item->isRead())
                            <a href="{{ route('post.letter', $item->id) }}">{{ $item->subject }}</a>
                        @else
                            <a href="{{ route('post.letter', $item->id) }}"><b>{{ $item->subject }}</b></a>
                        @endif
                        @if($item->money > 0)
                            <span title="Приложено монет: {{ $item->money }}"><img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle"></span>
                        @endif
                        @if($item->isRead())
                            <span class="grn" title="Прочитано получателем">✓</span>
                        @endif
                    </td>
                    <td class="brd2-top">{{ $item->recipient?->name ?? '—' }}</td>
                    <td class="brd2-top" nowrap>{{ $item->created_at->format('d.m.y H:i') }}</td>
                </tr>
            @endforeach
        </table>

        <div style="padding: 4px 2px;">
            <label class="pointer"><input type="checkbox" id="bulk-check-all"> выделить все</label>
        </div>

        <div style="padding-top: 4px;">
            <b class="butt1 pointer"><b><input value="Удалить" type="button" style="width: 90px;" class="redd" onclick="submitBulk('delete')"></b></b>
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
            if (! confirm('Удалить выбранные письма?')) return;
            document.getElementById('bulk-action').value = action;
            document.getElementById('bulk-form').submit();
        }
    </script>
@endif

@include('post::partials.window-close')