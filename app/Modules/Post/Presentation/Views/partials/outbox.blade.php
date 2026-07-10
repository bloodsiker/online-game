@include('post::partials.window-open', ['title' => 'Новое письмо'])

<form method="POST" action="{{ route('post.send') }}" style="margin: 0;">
    @csrf
    <table class="coll w100" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td width="50" class="black" nowrap><b>Тема:</b></td>
            <td style="padding: 2px 4px;">
                <input type="text" name="subject" maxlength="64" class="post-input" style="width: 96%;" value="{{ old('subject') }}">
            </td>
            <td width="50" class="black" nowrap><b>Кому:</b></td>
            <td style="padding: 2px 4px;">
                <input type="text" name="nick" class="post-input" style="width: 96%;" value="{{ old('nick', request('to')) }}">
            </td>
        </tr>
    </table>

    <div style="padding: 6px 0;">
        <textarea name="text" rows="12" class="post-input" style="width: 98%; resize: vertical;">{{ old('text') }}</textarea>
    </div>

    <table class="coll w100 p10h p4v brd2-all" border="0" cellspacing="0" cellpadding="0">
        <col width="150"><col>
        <tr class="bg_l">
            <td class="brd2-top brd2-rgt black" nowrap><b>Отправляемая сумма:</b></td>
            <td class="brd2-top" align="right" nowrap>
                <input type="text" name="money" maxlength="6" class="post-input" style="width: 50px; text-align: right;" value="{{ old('money', 0) }}">
                <span title="Монеты"><img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle"></span>
            </td>
        </tr>
        <tr class="bg_l">
            <td class="brd2-top brd2-rgt black" nowrap><b>Налог:</b></td>
            <td class="brd2-top" align="right" nowrap>
                {{ $tax }} <span title="Монеты"><img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle"></span>
            </td>
        </tr>
    </table>

    <div align="center" style="padding: 10px 0 6px;">
        <b class="butt1 pointer"><b><input value="Отправить" type="submit" style="width: 110px;" class="redd"></b></b>
    </div>
</form>

<div style="padding-top: 6px;">
    Вы можете отправить письмо любому персонажу, если его почтовый ящик не переполнен.
    К письму можно приложить деньги.<br>
    Стоимость отправки письма фиксированная — {{ $tax }}
    <span title="Монеты"><img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle"></span>.
    Приложенная сумма списывается при отправке; получатель забирает её кнопкой «Забрать» в письме.<br>
    <b class="rdd">Внимание!</b> Если получатель не заберёт деньги из письма до окончания срока его
    хранения — письмо будет удалено вместе с приложенной суммой.
</div>

@include('post::partials.window-close')
