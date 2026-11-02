<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex">
    <title>{{ config('app.name') }}</title>
</head>
<body>
<script>
    try { window.top.location.replace(@json($homeUrl)); }
    catch (error) { window.location.replace(@json($homeUrl)); }
</script>
<noscript><a href="{{ $homeUrl }}" target="_top">{{ __('Главная') }}</a></noscript>
</body>
</html>
