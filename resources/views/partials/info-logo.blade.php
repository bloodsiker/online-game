{{--
    Логотип ETERNUM над карточкой на публичных страницах инфо (игрок, монстр).
    Файлы: public/img/logo/Logoo330.webp и Logoo660.webp (прозрачные, 3:1; 1x/2x). Пока файла нет — прежний отступ 25px.
--}}
@php $infoLogo = 'img/logo/Logoo330.webp'; $infoLogo2x = 'img/logo/Logoo660.webp'; @endphp
@if (file_exists(public_path($infoLogo)))
    <tr>
        <td align="center" style="padding: 18px 0 4px;">
            <a href="{{ url('/') }}" title="{{ __('homepage.game_name') }}" style="display: inline-block; line-height: 0;">
                <img src="{{ asset($infoLogo) }}?v={{ filemtime(public_path($infoLogo)) }}"
                     srcset="{{ asset($infoLogo) }}?v={{ filemtime(public_path($infoLogo)) }} 1x, {{ asset($infoLogo2x) }}?v={{ filemtime(public_path($infoLogo2x)) }} 2x"
                     alt="ETERNUM"
                     style="display: block; width: auto; height: auto; max-width: 340px; max-height: 60px; filter: drop-shadow(0 2px 6px rgba(40, 27, 16, .35));">
            </a>
        </td>
    </tr>
@else
    <tr><td height="25">&nbsp;</td></tr>
@endif
