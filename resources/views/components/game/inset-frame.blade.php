<table {{ $attributes->merge(['class' => 'w100']) }} border="0" cellpadding="0" cellspacing="0">
    <tbody>
    <tr>
        <td width="12" height="12" class="common-inset-tl"></td>
        <td height="12" class="common-inset-t"></td>
        <td width="12" height="12" class="common-inset-tr"></td>
    </tr>
    <tr>
        <td width="12" class="common-inset-l"><img src="{{ asset('main/images/blank.gif') }}" width="12" alt=""></td>
        <td class="common-inset-bg" valign="top" style="padding: 0">{{ $slot }}</td>
        <td width="12" class="common-inset-r"><img src="{{ asset('main/images/blank.gif') }}" width="12" alt=""></td>
    </tr>
    <tr>
        <td width="12" height="12" class="common-inset-bl"></td>
        <td height="12" class="common-inset-b"></td>
        <td width="12" height="12" class="common-inset-br"></td>
    </tr>
    </tbody>
</table>
