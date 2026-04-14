@php
    $cur    = $paginator->currentPage();
    $last   = $paginator->lastPage();
    $window = 5;
    $pgFrom = max(1, $cur - $window);
    $pgTo   = min($last, $cur + $window);
@endphp

<table border="0" cellpadding="0" cellspacing="0" class="pg" style="margin: 4px 0; width: 60%">
    <tbody>
    <tr>
        <td class="b" width="10"><nobr>Страницы:&nbsp;</nobr></td>

        @if($pgFrom > 1)
            <td class="pg-inact"><a href="{{ $paginator->url(1) }}" class="pg-inact_lnk">1</a></td>
            @if($pgFrom > 2)<td class="b" style="padding:0 2px">…</td>@endif
        @endif

        @for($p = $pgFrom; $p <= $pgTo; $p++)
            <td class="{{ $p === $cur ? 'pg-act' : 'pg-inact' }}">
                <a href="{{ $paginator->url($p) }}" class="{{ $p === $cur ? 'pg-act_lnk' : 'pg-inact_lnk' }}">{{ $p }}</a>
            </td>
        @endfor

        @if($pgTo < $last)
            @if($pgTo < $last - 1)<td class="b" style="padding:0 2px">…</td>@endif
            <td class="pg-inact"><a href="{{ $paginator->url($last) }}" class="pg-inact_lnk">{{ $last }}</a></td>
        @endif

        <td style="text-align:left">&nbsp;&nbsp;</td>

        <td width="1%" style="text-align:right" nowrap="">
            @if($paginator->onFirstPage())
                <img src="{{ asset('img/bg/p-left-gray.gif') }}" border="0" width="29" height="17" title="Предыдущая">
            @else
                <a href="{{ $paginator->previousPageUrl() }}">
                    <img src="{{ asset('img/bg/p-left-red.gif') }}" border="0" width="29" height="17" title="Предыдущая">
                </a>
            @endif
            <img src="{{ asset('img/bg/pg-act.gif') }}" border="0" width="17" height="17">
            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}">
                    <img src="{{ asset('img/bg/p-right-red.gif') }}" border="0" width="29" height="17" title="Следующая">
                </a>
            @else
                <img src="{{ asset('img/bg/p-right-gray.gif') }}" border="0" width="29" height="17" title="Следующая">
            @endif
        </td>
    </tr>
    </tbody>
</table>