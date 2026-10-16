{{-- $prefix must be unique on the page, including inside repeated medal forms. --}}
@php
    $fields = $fields ?? ['name' => ['label' => 'Название'], 'description' => ['label' => 'Описание', 'multiline' => true]];
    $content = $content ?? null;
@endphp
<div class="content-translations mb-3">
    <input type="hidden" name="_translation_form" value="{{ $prefix }}" @isset($formId) form="{{ $formId }}" @endisset>
    <ul class="nav nav-tabs" role="tablist" aria-label="Язык перевода">
        @foreach(['ru' => 'Русский', 'uk' => 'Українська'] as $locale => $language)
            <li class="nav-item"><button class="nav-link {{ $locale === 'ru' ? 'active' : '' }}" id="{{ $prefix }}-{{ $locale }}-tab" data-bs-toggle="tab" data-bs-target="#{{ $prefix }}-{{ $locale }}" type="button" role="tab" aria-controls="{{ $prefix }}-{{ $locale }}" aria-selected="{{ $locale === 'ru' ? 'true' : 'false' }}">{{ $language }}</button></li>
        @endforeach
    </ul>
    <div class="tab-content">
        @foreach(['ru', 'uk'] as $locale)
            <div id="{{ $prefix }}-{{ $locale }}" class="tab-pane {{ $locale === 'ru' ? 'active' : '' }}" role="tabpanel" aria-labelledby="{{ $prefix }}-{{ $locale }}-tab">
                @foreach($fields as $field => $options)
                    @php
                        $inputName = $locale === 'ru' ? $field : 'translations[uk]['.$field.']';
                        $value = $content?->getTranslation($field, $locale, false) ?? '';
                        if (old('_translation_form') === $prefix) {
                            $value = old($locale === 'ru' ? $field : 'translations.uk.'.$field, $value);
                        }
                    @endphp
                    <div class="form-group">
                        <label class="col-form-label" for="{{ $prefix }}-{{ $locale }}-{{ $field }}">{{ $options['label'] }}</label>
                        @if($options['multiline'] ?? false)
                            <textarea id="{{ $prefix }}-{{ $locale }}-{{ $field }}" class="form-control {{ ($options['html_editor'] ?? false) ? 'translation-html-editor' : '' }}" name="{{ $inputName }}" @isset($formId) form="{{ $formId }}" @endisset rows="{{ $options['rows'] ?? 4 }}">{{ $value }}</textarea>
                        @else
                            <input id="{{ $prefix }}-{{ $locale }}-{{ $field }}" class="form-control" type="text" maxlength="255" name="{{ $inputName }}" @isset($formId) form="{{ $formId }}" @endisset value="{{ $value }}">
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
