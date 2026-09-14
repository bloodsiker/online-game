@extends('admin.layout.base')

@section('title', $article ? 'Статья: '.$article->title : 'Новая статья Библиотеки')

@section('body')
    <section class="card">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            @if($categories->isEmpty())
                <div class="alert alert-warning">Сначала <a href="{{ route('admin.library.categories.create') }}">создайте категорию</a>.</div>
            @endif

            <form method="post" enctype="multipart/form-data" action="{{ $article ? route('admin.library.articles.update', $article) : route('admin.library.articles.store') }}" data-floating-save-form>
                @csrf
                @if($article) @method('PUT') @endif

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="col-form-label">Название</label>
                            <input class="form-control" name="title" value="{{ old('title', $article?->title) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Адрес (slug)</label>
                            <input class="form-control" name="slug" value="{{ old('slug', $article?->slug) }}" placeholder="Создастся автоматически">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Краткое описание</label>
                            <textarea class="form-control" name="excerpt" rows="3" maxlength="2000">{{ old('excerpt', $article?->excerpt) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Содержание</label>
                            <textarea id="library-content-editor" class="form-control" name="content" rows="16" required>{{ old('content', $article?->content) }}</textarea>
                            <div class="library-editor-tools mt-2">
                                <div class="mb-2">
                                    <span class="text-muted me-2">Готовые блоки:</span>
                                    <button type="button" class="btn btn-sm btn-outline-danger library-block-insert" data-kind="important" data-title="Важно">Важно</button>
                                    <button type="button" class="btn btn-sm btn-outline-success library-block-insert" data-kind="tip" data-title="Совет">Совет</button>
                                    <button type="button" class="btn btn-sm btn-outline-warning library-block-insert" data-kind="warning" data-title="Предупреждение">Предупреждение</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary library-game-frame-insert" title="Вставить игровую рамку с редактируемым содержимым">
                                        <i class="fas fa-border-all" aria-hidden="true"></i> Игровая рамка
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary library-game-frame-title-insert" title="Вставить игровую рамку с редактируемым заголовком">
                                        <i class="fas fa-heading" aria-hidden="true"></i> Рамка с заголовком
                                    </button>
                                    <button type="button" class="btn btn-sm btn-default library-currency-insert" data-title="Серебро" data-src="{{ asset('img/icon/m_game.gif') }}" title="Вставить серебро">
                                        <img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" alt="Серебро"> Серебро
                                    </button>
                                    <button type="button" class="btn btn-sm btn-default library-currency-insert" data-title="Бриллианты" data-src="{{ asset('img/icon/m_dmd.gif') }}" title="Вставить бриллианты">
                                        <img src="{{ asset('img/icon/m_dmd.gif') }}" width="11" height="11" alt="Бриллианты"> Бриллианты
                                    </button>
                                </div>
                                <div>
                                    <span class="text-muted me-2">Спецсимволы:</span>
                                    @foreach(['•', '→', '←', '±', '×', '÷', '≤', '≥', '—', '«', '»', '✓', '⚔', '★', '©', '®', '™'] as $symbol)
                                        <button type="button" class="btn btn-sm btn-default library-symbol-insert" data-symbol="{{ $symbol }}">{{ $symbol }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Вставить предмет в статью</label>
                            <div class="row g-2 align-items-end">
                                <div class="col-md-6"><select id="library-item-shortcode-id" class="form-control"></select></div>
                                <div class="col-md-3"><input type="number" min="1" class="form-control" id="library-item-shortcode-count" placeholder="Количество"></div>
                                <div class="col-md-3"><button type="button" class="btn btn-default w-100" id="library-item-shortcode-insert">Вставить</button></div>
                            </div>
                            <small class="text-muted">Предмет будет кликабельным и получит игровой тултип.</small>
                        </div>

                        <div class="form-group">
                            <label class="col-form-label">Вставить игровую карточку</label>
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <select id="library-entity-shortcode-type" class="form-control">
                                        <option value="monster">Монстр</option>
                                        <option value="npc">НПС</option>
                                        <option value="reputation">Репутация</option>
                                        <option value="map">Карта</option>
                                        <option value="location">Локация</option>
                                    </select>
                                </div>
                                <div class="col-md-4"><select id="library-entity-shortcode-id" class="form-control"></select></div>
                                <div class="col-md-2">
                                    <select id="library-entity-shortcode-display" class="form-control" title="Способ отображения">
                                        <option value="block">Блок</option>
                                        <option value="inline">Строка</option>
                                    </select>
                                </div>
                                <div class="col-md-3"><button type="button" class="btn btn-default w-100" id="library-entity-shortcode-insert">Вставить</button></div>
                            </div>
                            <small class="text-muted">В статью вставится карточка с актуальным названием, изображением и характеристиками объекта.</small>
                        </div>

                        <div class="card mt-3">
                            <header class="card-header d-flex justify-content-between align-items-center">
                                <h2 class="card-title">Связанные игровые объекты</h2>
                                <button type="button" id="add-library-link" class="btn btn-sm btn-default">Добавить связь</button>
                            </header>
                            <div class="card-body">
                                <p class="text-muted">Связь позволяет прикрепить статью к монстру, профессии, репутации, предмету и другим объектам. Характеристики остаются в игровых таблицах.</p>
                                <div id="library-links">
                                    @php
                                        $formLinks = old('links', $article?->links?->map(fn ($link) => [
                                            'entity_type' => $link->entity_type,
                                            'entity_id' => $link->entity_id,
                                            'custom_label' => $link->custom_label,
                                            '_id' => $link->id,
                                        ])->all() ?? []);
                                    @endphp
                                    @foreach($formLinks as $index => $link)
                                        <div class="row g-2 align-items-end mb-2 library-link-row">
                                            <div class="col-md-3">
                                                <label class="col-form-label">Тип</label>
                                                <select class="form-control" name="links[{{ $index }}][entity_type]">
                                                    <option value="">Выберите</option>
                                                    @foreach($entityTypes as $value => $label)<option value="{{ $value }}" @selected(($link['entity_type'] ?? '') === $value)>{{ $label }}</option>@endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="col-form-label">ID</label>
                                                <input type="number" min="1" class="form-control" name="links[{{ $index }}][entity_id]" value="{{ $link['entity_id'] ?? '' }}">
                                            </div>
                                            <div class="col-md-5">
                                                <label class="col-form-label">Своя подпись</label>
                                                <input class="form-control" name="links[{{ $index }}][custom_label]" value="{{ $link['custom_label'] ?? '' }}" placeholder="Необязательно">
                                                @if(!empty($link['_id']) && !empty($linkLabels[$link['_id']]))<small class="text-muted">Сейчас: {{ $linkLabels[$link['_id']] }}</small>@endif
                                            </div>
                                            <div class="col-md-2"><button type="button" class="btn btn-danger remove-library-link">Удалить</button></div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-form-label">Категория</label>
                            <select class="form-control" name="category_id" required>
                                <option value="">Выберите категорию</option>
                                @foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $article?->category_id) === (string) $category->id)>{{ $category->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Статус</label>
                            <select class="form-control" name="status">@foreach($statuses as $value => $label)<option value="{{ $value }}" @selected(old('status', $article?->status ?? 'draft') === $value)>{{ $label }}</option>@endforeach</select>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Дата публикации</label>
                            <input type="datetime-local" class="form-control" name="published_at" value="{{ old('published_at', $article?->published_at?->format('Y-m-d\TH:i')) }}">
                            <small class="text-muted">Для публикации сейчас можно оставить пустой.</small>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Порядок</label>
                            <input type="number" min="0" class="form-control" name="sort_order" value="{{ old('sort_order', $article?->sort_order ?? 0) }}">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Обложка</label>
                            <input type="file" class="form-control" name="cover_image" accept="image/*">
                            @if($article?->cover_image)<img src="{{ asset($article->cover_image) }}" alt="" class="mt-2" style="max-width:100%;max-height:180px">@endif
                        </div>
                        @if($article)
                            <div class="form-group"><label class="col-form-label">Просмотры</label><input class="form-control" value="{{ $article->views_count }}" disabled></div>
                        @endif
                    </div>
                </div>

                <div class="text-end mt-3">
                    <a href="{{ route('admin.library.articles.index') }}" class="btn btn-default">Назад</a>
                    <button class="btn btn-primary" @disabled($categories->isEmpty())>{{ $article ? 'Сохранить' : 'Создать' }}</button>
                </div>
            </form>
        </div>
    </section>

    <template id="library-link-template">
        <div class="row g-2 align-items-end mb-2 library-link-row">
            <div class="col-md-3"><label class="col-form-label">Тип</label><select class="form-control" data-name="entity_type"><option value="">Выберите</option>@foreach($entityTypes as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="col-form-label">ID</label><input type="number" min="1" class="form-control" data-name="entity_id"></div>
            <div class="col-md-5"><label class="col-form-label">Своя подпись</label><input class="form-control" data-name="custom_label" placeholder="Необязательно"></div>
            <div class="col-md-2"><button type="button" class="btn btn-danger remove-library-link">Удалить</button></div>
        </div>
    </template>

    @include('admin.layout.summernote', [
        'selector' => '#library-content-editor',
        'height' => 360,
        'placeholder' => 'Введите содержание статьи',
        'imageUploadUrl' => route('admin.library.articles.upload-image'),
        'toolbarPreset' => 'extended',
    ])
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/library_game_frame.css') }}?v={{ filemtime(public_path('css/library_game_frame.css')) }}">
<style>
    .library-editor-tools { padding: 9px; border: 1px solid #ddd; border-radius: 4px; background: #f8f9fa; }
    .library-editor-tools .btn { min-width: 31px; margin: 1px; }
    .note-editable .library-info-block { margin: 10px 0; padding: 10px 12px; border-left: 4px solid #888; background: #f7f7f7; }
    .note-editable .library-info-block--important { border-color: #a62f2f; background: #fff0f0; }
    .note-editable .library-info-block--tip { border-color: #3d8751; background: #effaf2; }
    .note-editable .library-info-block--warning { border-color: #bd8425; background: #fff8e5; }
</style>
@endpush

@push('footer_scripts')
<script>
    (function () {
        var container = document.getElementById('library-links');
        var template = document.getElementById('library-link-template');
        var nextIndex = container.querySelectorAll('.library-link-row').length;

        document.getElementById('add-library-link').addEventListener('click', function () {
            var row = template.content.firstElementChild.cloneNode(true);
            row.querySelectorAll('[data-name]').forEach(function (field) {
                field.name = 'links[' + nextIndex + '][' + field.dataset.name + ']';
            });
            nextIndex++;
            container.appendChild(row);
        });

        container.addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-library-link')) {
                event.target.closest('.library-link-row').remove();
            }
        });

        function formatItem(item) {
            if (!item.id) return item.text;
            var image = item.image
                ? '<img src="' + item.image + '" style="width:22px;height:22px;object-fit:contain;margin-right:6px;vertical-align:middle">'
                : '';

            return $('<span>' + image + item.text + '</span>');
        }

        $('#library-item-shortcode-id').select2({
            theme: 'bootstrap',
            placeholder: 'Выберите предмет',
            allowClear: true,
            templateResult: formatItem,
            templateSelection: formatItem,
            ajax: {
                url: '{{ route('admin.api.items') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) { return {q: params.term, page: params.page || 1}; },
                processResults: function (data) { return data; },
                cache: true
            }
        });

        $('#library-item-shortcode-insert').on('click', function () {
            var itemId = parseInt($('#library-item-shortcode-id').val(), 10);
            var count = parseInt($('#library-item-shortcode-count').val(), 10);
            if (!itemId) return;

            var shortcode = count > 1
                ? '[[item:' + itemId + '; count:' + count + ']]'
                : '[[item:' + itemId + ']]';
            $('#library-content-editor').summernote('insertText', shortcode);
        });

        $('.library-symbol-insert').on('click', function () {
            $('#library-content-editor').summernote('insertText', $(this).data('symbol'));
        });

        $('.library-block-insert').on('click', function () {
            var kind = $(this).data('kind');
            var title = $(this).data('title');
            var html = '<div class="library-info-block library-info-block--' + kind + '"><strong>'
                + title + ':</strong> Текст блока</div><p><br></p>';

            $('#library-content-editor').summernote('pasteHTML', html);
        });

        function insertGameFrame(withTitle) {
            var title = withTitle
                ? '<table class="library-game-frame__title" border="0" cellspacing="0" cellpadding="0">'
                    + '<tbody><tr height="22">'
                    + '<td width="27"><img src="/img/bg/info/tbl-usi_label-left.gif" width="27" height="22" alt=""></td>'
                    + '<td align="center" class="tbl-usi_label-center">Заголовок блока</td>'
                    + '<td width="27"><img src="/img/bg/info/tbl-usi_label-right.gif" width="27" height="22" alt=""></td>'
                    + '</tr></tbody></table>'
                : '';
            var contentPadding = withTitle ? '8px 10px' : '4px 0 14px';
            var contentMargin = withTitle ? '' : ' style="margin:5px"';
            var html = '<table class="library-game-frame" border="0" cellspacing="0" cellpadding="0">'
                + '<tbody>'
                + '<tr height="22">'
                + '<td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>'
                + '<td class="tbl-shp-sml tt" valign="top" align="center">' + title + '</td>'
                + '<td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>'
                + '</tr>'
                + '<tr>'
                + '<td class="tbl-shp-sides ls">&nbsp;</td>'
                + '<td class="tbl-usi_bg" valign="top" style="padding:' + contentPadding + '">'
                + '<div class="structures"' + contentMargin + '>Текст блока</div>'
                + '</td>'
                + '<td class="tbl-shp-sides rs">&nbsp;</td>'
                + '</tr>'
                + '<tr height="18">'
                + '<td width="20" align="right" valign="top" class="tbl-shp-sml lb"><b></b></td>'
                + '<td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td>'
                + '<td width="20" align="left" valign="top" class="tbl-shp-sml rb"><b></b></td>'
                + '</tr>'
                + '</tbody>'
                + '</table><p><br></p>';

            $('#library-content-editor').summernote('pasteHTML', html);
        }

        $('.library-game-frame-insert').on('click', function () {
            insertGameFrame(false);
        });

        $('.library-game-frame-title-insert').on('click', function () {
            insertGameFrame(true);
        });

        $('.library-currency-insert').on('click', function () {
            var title = $(this).data('title');
            var src = $(this).data('src');
            var html = '<img src="' + src + '" width="11" height="11" alt="' + title
                + '" title="' + title + '" style="vertical-align:middle">&nbsp;';

            $('#library-content-editor').summernote('pasteHTML', html);
        });

        var entityEndpoints = {
            monster: @json(route('admin.api.monsters')),
            npc: @json(route('admin.api.npcs')),
            reputation: @json(route('admin.api.reputations')),
            map: @json(route('admin.api.maps')),
            location: @json(route('admin.api.locations'))
        };

        function initEntitySelect() {
            var $select = $('#library-entity-shortcode-id');
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy').empty();
            }

            $select.select2({
                theme: 'bootstrap',
                placeholder: 'Начните вводить название',
                allowClear: true,
                templateResult: formatItem,
                templateSelection: formatItem,
                ajax: {
                    url: entityEndpoints[$('#library-entity-shortcode-type').val()],
                    dataType: 'json',
                    delay: 250,
                    data: function (params) { return {q: params.term, page: params.page || 1}; },
                    processResults: function (data) { return data; },
                    cache: true
                }
            });
        }

        $('#library-entity-shortcode-type').on('change', initEntitySelect);
        initEntitySelect();

        $('#library-entity-shortcode-insert').on('click', function () {
            var type = $('#library-entity-shortcode-type').val();
            var entityId = parseInt($('#library-entity-shortcode-id').val(), 10);
            var display = $('#library-entity-shortcode-display').val();
            if (!entityId) return;

            $('#library-content-editor').summernote('insertText', '[[' + type + ':' + entityId + '; display:' + display + ']]');
        });
    })();
</script>
@endpush
