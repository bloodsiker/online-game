<div id="tab-translations" class="tab-pane{{ ($active ?? false) ? ' active' : '' }}">
    <div class="pt-3 pb-3">
        <p class="text-muted">Русское название обязательно. Если украинский перевод не заполнен, игрок увидит русский текст.</p>
        <ul class="nav nav-tabs" role="tablist" aria-label="Язык перевода">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="item-language-ru-tab" data-bs-toggle="tab" data-bs-target="#item-language-ru" type="button" role="tab" aria-controls="item-language-ru" aria-selected="true">Русский</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="item-language-uk-tab" data-bs-toggle="tab" data-bs-target="#item-language-uk" type="button" role="tab" aria-controls="item-language-uk" aria-selected="false">Українська</button>
            </li>
        </ul>
        <div class="tab-content">
            <div id="item-language-ru" class="tab-pane active" role="tabpanel" aria-labelledby="item-language-ru-tab">
                <div class="form-group">
                    <label class="col-form-label" for="item-name-ru">Название</label>
                    <input id="item-name-ru" type="text" maxlength="255" @if(isset($formId)) form="{{ $formId }}" @endif class="form-control" name="name" value="{{ old('name', isset($item) ? $item->getTranslation('name', 'ru') : '') }}">
                    @error('name')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="col-form-label" for="item-description-ru">Описание</label>
                    <textarea id="item-description-ru" @if(isset($formId)) form="{{ $formId }}" @endif class="form-control" name="description" rows="8">{{ old('description', isset($item) ? $item->getTranslation('description', 'ru') : '') }}</textarea>
                    @error('description')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>
            <div id="item-language-uk" class="tab-pane" role="tabpanel" aria-labelledby="item-language-uk-tab">
                <div class="form-group">
                    <label class="col-form-label" for="item-name-uk">Назва</label>
                    <input id="item-name-uk" type="text" maxlength="255" @if(isset($formId)) form="{{ $formId }}" @endif class="form-control" name="translations[uk][name]" value="{{ old('translations.uk.name', isset($item) ? $item->getTranslation('name', 'uk', false) : '') }}">
                    @error('translations.uk.name')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="col-form-label" for="item-description-uk">Опис</label>
                    <textarea id="item-description-uk" @if(isset($formId)) form="{{ $formId }}" @endif class="form-control item-translation-description" name="translations[uk][description]" rows="8">{{ old('translations.uk.description', isset($item) ? $item->getTranslation('description', 'uk', false) : '') }}</textarea>
                    @error('translations.uk.description')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>
