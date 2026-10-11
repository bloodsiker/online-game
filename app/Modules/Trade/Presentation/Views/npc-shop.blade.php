<tr class="bg_l"
    onclick="if(document._submit)return false;document._submit=true;location.href=@js(route('trade.show', $post->id));"
    onmouseover="this.className='bg_l2'" onmouseout="this.className='bg_l'">
    <td class="brd2-top brd2-bt" width="1%">
        <img src="{{ asset('img/icon/qst_store.gif') }}" alt="Магазин" width="46" height="28">
    </td>
    <td class="brd2-top brd2-bt">Вольный торг: {{ $post->name }}</td>
    <td class="brd2-top brd2-bt" align="right">
        <b class="butt2 pointer"><b>
            <input value="Далее" type="button" style="width:60px">
        </b></b>
    </td>
</tr>
