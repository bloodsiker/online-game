{{--
    Логотип ETERNUM над карточкой на публичных страницах инфо (игрок, монстр).
    Файл: public/img/logo/Logoo500.webp (прозрачный, 500×167). Пока файла нет — прежний отступ 25px.
--}}
@php $infoLogo = 'img/logo/Logoo500.webp'; @endphp
@if (file_exists(public_path($infoLogo)))
    <tr>
        <td align="center" style="padding: 18px 0 4px;">
            <a href="{{ url('/') }}" title="{{ __('homepage.game_name') }}" style="display: inline-block; line-height: 0;">
                <img src="{{ asset($infoLogo) }}?v={{ filemtime(public_path($infoLogo)) }}"
                     alt="ETERNUM"
                     style="display: block; width: auto; height: auto; max-width: 340px; max-height: 60px; filter: drop-shadow(0 2px 6px rgba(40, 27, 16, .35));">
            </a>
        </td>
    </tr>
@else
    <tr><td height="25">&nbsp;</td></tr>
@endif
