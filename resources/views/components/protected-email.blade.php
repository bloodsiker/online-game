{{--
    E-mail, скрытый от сборщиков адресов: в HTML нет ни адреса, ни «@».
    Имя и домен лежат в base64 по отдельности, ссылку mailto: и текст собирает JS в браузере.
    Без JS виден запасной вариант «имя [at] домен».

    <x-protected-email :email="config('game.support_email')" class="b-aside__support-link">Email:<br></x-protected-email>
--}}
@props(['email'])
@php
    [$emailUser, $emailDomain] = array_pad(explode('@', (string) $email, 2), 2, '');
@endphp
<a {{ $attributes->merge(['href' => '#']) }}
   data-pe-u="{{ base64_encode($emailUser) }}"
   data-pe-d="{{ base64_encode($emailDomain) }}">{{ $slot }}<span data-pe-text>{{ $emailUser }} [at] {{ $emailDomain }}</span></a>
@once
    <script>
        (function () {
            function reveal() {
                document.querySelectorAll('a[data-pe-u]').forEach(function (link) {
                    try {
                        var address = atob(link.getAttribute('data-pe-u')) + String.fromCharCode(64) + atob(link.getAttribute('data-pe-d'));
                        link.setAttribute('href', 'mai' + 'lto:' + address);
                        var text = link.querySelector('[data-pe-text]');
                        if (text) text.textContent = address;
                        link.removeAttribute('data-pe-u');
                        link.removeAttribute('data-pe-d');
                    } catch (e) { /* оставляем «имя [at] домен» */ }
                });
            }
            document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', reveal) : reveal();
        })();
    </script>
@endonce
