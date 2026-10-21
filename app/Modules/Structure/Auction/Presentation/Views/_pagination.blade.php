@if($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $pageFrom = max(1, $currentPage - 5);
        $pageTo = min($lastPage, $currentPage + 5);
    @endphp
    <table class="pg w100 auction-item-pagination" border="0" cellpadding="0" cellspacing="0" aria-label="{{ __('Страницы') }}">
        <tbody><tr>
            <td width="1%" nowrap>
            <table class="auction-page-list" border="0" cellpadding="0" cellspacing="0"><tbody><tr>
            <td class="b" width="10" nowrap>{{ __('Страницы') }}:&nbsp;</td>
            @if($pageFrom > 1)
                <td class="pg-inact"><a href="{{ $paginator->url(1) }}" class="pg-inact_lnk">1</a></td>
                @if($pageFrom > 2)<td class="b" style="padding:0 2px;">…</td>@endif
            @endif
            @for($number = $pageFrom; $number <= $pageTo; $number++)
                <td class="{{ $number === $currentPage ? 'pg-act' : 'pg-inact' }}">
                    <a href="{{ $paginator->url($number) }}" class="{{ $number === $currentPage ? 'pg-act_lnk' : 'pg-inact_lnk' }}" @if($number === $currentPage) aria-current="page" @endif>{{ $number }}</a>
                </td>
            @endfor
            @if($pageTo < $lastPage)
                @if($pageTo < $lastPage - 1)<td class="b" style="padding:0 2px;">…</td>@endif
                <td class="pg-inact"><a href="{{ $paginator->url($lastPage) }}" class="pg-inact_lnk">{{ $lastPage }}</a></td>
            @endif
            </tr></tbody></table>
            </td>
            <td width="100%" align="right" nowrap>
                @if($paginator->onFirstPage())
                    <img src="{{ asset('img/bg/p-left-gray.gif') }}" border="0" width="29" height="17" title="{{ __('Предыдущая') }}">
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev"><img src="{{ asset('img/bg/p-left-red.gif') }}" border="0" width="29" height="17" title="{{ __('Предыдущая') }}"></a>
                @endif
                <img src="{{ asset('img/bg/pg-act.gif') }}" border="0" width="17" height="17" alt="">
                @if($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next"><img src="{{ asset('img/bg/p-right-red.gif') }}" border="0" width="29" height="17" title="{{ __('Следующая') }}"></a>
                @else
                    <img src="{{ asset('img/bg/p-right-gray.gif') }}" border="0" width="29" height="17" title="{{ __('Следующая') }}">
                @endif
            </td>
        </tr></tbody>
    </table>
@endif
