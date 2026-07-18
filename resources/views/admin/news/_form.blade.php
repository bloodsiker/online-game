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
        <div class="form-group">
            <label class="col-form-label">Заголовок</label>
            <input type="text" class="form-control" name="title" value="{{ old('title', $news?->title) }}" required>
        </div>
        <div class="form-group">
            <label class="col-form-label">Описание</label>
            <textarea id="news-description-editor" class="form-control" name="description" rows="12" required>{{ old('description', $news?->description) }}</textarea>
            <small class="text-muted">Можно использовать HTML для форматирования новости.</small>
        </div>
        <div class="form-group">
            <label class="col-form-label">Шорткод предмета</label>
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <select id="news-item-shortcode-id" class="form-control"></select>
                </div>
                <div class="col-md-3">
                    <input type="number" min="1" class="form-control" id="news-item-shortcode-count" placeholder="Количество">
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-default w-100" id="news-item-shortcode-insert">Вставить предмет</button>
                </div>
            </div>
            <small class="text-muted">Будет вставлен формат [[item:ID]] или [[item:ID; count:5]].</small>
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
    'selector' => '#news-description-editor',
    'height' => 260,
    'placeholder' => 'Введите текст новости',
    'imageUploadUrl' => route('admin.news.upload-image'),
])

@push('footer_scripts')
<script>
    (function () {
        function formatNewsItemOption(item) {
            if (!item.id) {
                return item.text;
            }

            var img = item.image
                ? '<img src="' + item.image + '" style="width:22px;height:22px;object-fit:contain;margin-right:6px;vertical-align:middle;">'
                : '';

            return $('<span>' + img + item.text + '</span>');
        }

        $('#news-item-shortcode-id').select2({
            theme: 'bootstrap',
            placeholder: 'Выберите предмет',
            allowClear: true,
            templateResult: formatNewsItemOption,
            templateSelection: formatNewsItemOption,
            ajax: {
                url: '{{ route('admin.api.items') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
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

        $('#news-item-shortcode-insert').on('click', function () {
            var itemId = parseInt($('#news-item-shortcode-id').val(), 10);
            var count = parseInt($('#news-item-shortcode-count').val(), 10);

            if (!itemId || itemId < 1) {
                $('#news-item-shortcode-id').focus();
                return;
            }

            var shortcode = count && count > 1
                ? '[[item:' + itemId + '; count:' + count + ']]'
                : '[[item:' + itemId + ']]';

            $('#news-description-editor').summernote('insertText', shortcode);
        });
    })();
</script>
@endpush
