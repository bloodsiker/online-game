{{-- WYSIWYG-редактор Summernote для textarea. Использование:
     @include('admin.layout.summernote', ['selector' => 'textarea[name=description]']) --}}
@push('styles')
<link rel="stylesheet" href="{{ asset('admin/vendor/summernote/summernote-bs5.min.css') }}" />
@endpush

@push('footer_scripts')
@php
    $toolbar = [
        ['history', ['undo', 'redo']],
        ['font', ['bold', 'italic', 'underline', 'clear']],
        ['color', ['forecolor']],
        ['insert', ! empty($imageUploadUrl) ? ['link', 'picture'] : ['link']],
        ['view', ['codeview']],
    ];
@endphp
<script src="{{ asset('admin/vendor/summernote/summernote-bs5.min.js') }}"></script>
<script>
    (function () {
        var summernoteOptions = {
        height: {{ $height ?? 110 }},
        tooltip: false,
        toolbar: @json($toolbar),
        placeholder: @json($placeholder ?? ''),
        };

        @if(! empty($imageUploadUrl))
            summernoteOptions.callbacks = {
                onImageUpload: function (files) {
                    if (!files || !files[0]) {
                        return;
                    }

                    var formData = new FormData();
                    formData.append('file', files[0]);
                    formData.append('_token', @json(csrf_token()));

                    $.ajax({
                        url: @json($imageUploadUrl),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            if (response && response.url) {
                                $('{{ $selector }}').summernote('insertImage', response.url);
                            }
                        }
                    });
                }
            };
        @endif

        $('{{ $selector }}').summernote(summernoteOptions);
    })();
</script>
@endpush
