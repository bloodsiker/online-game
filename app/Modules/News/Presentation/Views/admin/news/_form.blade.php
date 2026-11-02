@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        @include('admin.partials.content-translations', ['prefix' => 'news-post', 'content' => $news, 'fields' => [
            'title' => ['label' => 'Заголовок'],
            'description' => ['label' => 'Описание', 'multiline' => true],
        ]])
        <small class="text-muted">Можно использовать HTML для форматирования новости.</small>
        <div class="mt-2 mb-3">
            <button type="button" class="btn btn-default" id="news-image-upload">Загрузить изображение</button>
            <small class="text-muted d-block mt-1">JPG, PNG, GIF или WebP до 4 МБ. Изображение вставляется в текст выбранного языка.</small>
        </div>
        <div class="form-group">
            <label class="col-form-label">Вставить игровую карточку</label>
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select id="news-shortcode-type" class="form-control">
                        <option value="item">Предмет</option>
                        <option value="monster">Монстр</option>
                        <option value="npc">NPC</option>
                        <option value="skill">Навык</option>
                        <option value="magic_skill">Заклинание</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <select id="news-item-shortcode-id" class="form-control"></select>
                </div>
                <div class="col-md-2">
                    <select id="news-shortcode-display" class="form-control" hidden>
                        <option value="block">Блок</option>
                        <option value="inline">Строка</option>
                    </select>
                    <input type="number" min="1" class="form-control" id="news-item-shortcode-count" placeholder="Количество">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-default w-100" id="news-item-shortcode-insert">Вставить</button>
                </div>
            </div>
            <small class="text-muted">Предмет: [[item:ID; count:5]]. Остальные: [[monster:ID]], [[npc:ID]], [[skill:ID]], [[magic_skill:ID]]. Для строки: [[npc:ID; display:inline]].</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label class="col-form-label">Количество просмотров</label>
            <input type="number" min="0" class="form-control" name="views_count" value="{{ old('views_count', $news?->views_count ?? 0) }}">
        </div>
        <div class="form-group">
            <label class="col-form-label">Дата создания</label>
            <input type="datetime-local"
                   class="form-control"
                   name="created_at"
                   value="{{ old('created_at', $news?->created_at?->format('Y-m-d\TH:i')) }}">
        </div>
        <div class="checkbox-custom checkbox-default mt-3">
            <input type="checkbox" id="allow-comments" name="allow_comments" value="1" @checked(old('allow_comments', $news?->allow_comments ?? true))>
            <label for="allow-comments">Разрешить комментарии игроков</label>
        </div>
        <div class="checkbox-custom checkbox-default mt-2">
            <input type="checkbox" id="is-active" name="is_active" value="1" @checked(old('is_active', $news?->is_active ?? true))>
            <label for="is-active">Активна на главной</label>
        </div>
    </div>
</div>

@include('admin.layout.summernote', [
    'selector' => '#news-post-ru-description, #news-post-uk-description',
    'height' => 260,
    'placeholder' => 'Введите текст новости',
    'imageUploadUrl' => route('admin.news.upload-image'),
])

@push('footer_scripts')
<script>
    (function () {
        $('#news-image-upload').on('click', function () {
            var $editor = $('.content-translations .tab-pane.active textarea').first();
            if ($editor.length) {
                $editor.summernote('imageDialog.show');
            }
        });

        function formatNewsItemOption(item) {
            if (!item.id) {
                return item.text;
            }

            var $option = $('<span>');
            if (item.image) {
                $option.append($('<img>').attr('src', item.image).css({width:22, height:22, objectFit:'contain', marginRight:6, verticalAlign:'middle'}));
            }
            return $option.append(document.createTextNode(item.text));
        }

        $('#news-item-shortcode-id').select2({
            theme: 'bootstrap',
            placeholder: 'Выберите запись',
            allowClear: true,
            templateResult: formatNewsItemOption,
            templateSelection: formatNewsItemOption,
            ajax: {
                url: '{{ route('admin.news.shortcode-entities') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        type: $('#news-shortcode-type').val(),
                        q: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 0
        });

        $('#news-shortcode-type').on('change', function () {
            $('#news-item-shortcode-id').val(null).empty().trigger('change');
            var isItem = this.value === 'item';
            $('#news-item-shortcode-count').prop('hidden', !isItem);
            $('#news-shortcode-display').prop('hidden', isItem);
        });

        $('#news-item-shortcode-insert').on('click', function () {
            var itemId = parseInt($('#news-item-shortcode-id').val(), 10);
            var count = parseInt($('#news-item-shortcode-count').val(), 10);

            if (!itemId || itemId < 1) {
                $('#news-item-shortcode-id').focus();
                return;
            }

            var type = $('#news-shortcode-type').val();
            var shortcode = '[[' + type + ':' + itemId;
            if (type === 'item' && count > 1) {
                shortcode += '; count:' + count;
            } else if (type !== 'item') {
                shortcode += '; display:' + $('#news-shortcode-display').val();
            }
            shortcode += ']]';

            var $editor = $('.content-translations .tab-pane.active textarea').first();
            if ($editor.length) {
                $editor.summernote('insertText', shortcode);
            }
        });
    })();
</script>
@endpush
