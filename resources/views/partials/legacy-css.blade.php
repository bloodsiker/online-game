{{--
    Склеенный CSS страницы (scripts/bundle-legacy-css.mjs, собирается в npm run build).
    Если сборки нет (локально без build) — подключаем исходные файлы по отдельности, как раньше.

    @include('partials.legacy-css', ['bundle' => 'homepage'])
--}}
@php
    $legacyManifestPath = public_path('build/legacy/legacy-manifest.json');
    $legacyManifest = is_file($legacyManifestPath) ? (json_decode((string) file_get_contents($legacyManifestPath), true) ?: []) : [];
    $legacyBundle = $legacyManifest[$bundle] ?? null;
@endphp
@if ($legacyBundle && is_file(public_path($legacyBundle['file'])))
    <link rel="stylesheet" href="{{ asset($legacyBundle['file']) }}">
@else
    @foreach ((json_decode((string) file_get_contents(resource_path('legacy-css.json')), true)[$bundle] ?? []) as $legacyFile)
        <link rel="stylesheet" href="{{ asset($legacyFile) }}">
    @endforeach
@endif
