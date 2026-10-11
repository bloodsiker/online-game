@php
    $chance = (float) $chance;
    $frequency = $chance > 0
        ? '1/'.rtrim(rtrim(number_format(100 / $chance, 2, '.', ''), '0'), '.')
        : 'Не выпадает';
@endphp
<span title="Средняя частота выпадения, не гарантированный дроп">{{ $frequency }}</span>
