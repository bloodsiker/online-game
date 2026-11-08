{{-- Тег Google: подключается только на страницах верхнего уровня, не во фреймах игры. --}}
@if (config('services.google_tag.enabled') && config('services.google_tag.id'))
    <script async src="https://www.googletagmanager.com/gtm.js?id={{ config('services.google_tag.id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
    </script>
@endif
