@php($gameClientTranslations = array_filter(app('translator')->getLoader()->load(app()->getLocale(), '*', '*'), static fn (string $key): bool => !str_contains($key, '<'), ARRAY_FILTER_USE_KEY))
<script>
    window.gameTranslations = @json($gameClientTranslations, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    window.gameTranslate = function (key, parameters) {
        let text = Object.prototype.hasOwnProperty.call(window.gameTranslations, key)
            ? window.gameTranslations[key] : key;
        Object.keys(parameters || {}).forEach(function (name) {
            text = text.split(':' + name).join(String(parameters[name]));
        });
        return text;
    };
</script>
