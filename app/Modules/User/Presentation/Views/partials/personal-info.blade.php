<tr class="bg_l">
    <td class="brd2-top brd2-bt b" nowrap>Никнейм:</td>
    <td class="brd2-top brd2-bt b redd" style="overflow-wrap:anywhere">{{ $nickname }}</td>
</tr>
<tr>
    <td class="brd2-top brd2-bt b" nowrap>В игре</td>
    <td class="brd2-top brd2-bt b redd">{{ $age }}</td>
</tr>
@php
    $fields = [
        'Имя:' => $profile?->real_name,
        'Дата рождения:' => $profile?->birth_date?->format('d.m.Y'),
        'Место проживания:' => $profile?->residence,
        'Девиз:' => $profile?->motto,
        'О себе:' => $profile?->about,
    ];
    $row = 0;
@endphp
@foreach($fields as $label => $value)
    @continue($value === null || trim($value) === '')
    <tr @class(['bg_l' => $row++ % 2 === 0])>
        <td class="brd2-top brd2-bt b" nowrap valign="top">{{ $label }}</td>
        <td class="brd2-top brd2-bt b redd" style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $value }}</td>
    </tr>
@endforeach
