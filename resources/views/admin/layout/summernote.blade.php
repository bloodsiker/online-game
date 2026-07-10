{{-- WYSIWYG-редактор Summernote для textarea. Использование:
     @include('admin.layout.summernote', ['selector' => 'textarea[name=description]']) --}}
@push('styles')
<link rel="stylesheet" href="{{ asset('admin/vendor/summernote/summernote-bs5.min.css') }}" />
@endpush

@push('footer_scripts')
<script src="{{ asset('admin/vendor/summernote/summernote-bs5.min.js') }}"></script>
<script>
    $('{{ $selector }}').summernote({
        height: {{ $height ?? 110 }},
        tooltip: false,
        toolbar: [
            ['history', ['undo', 'redo']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['color', ['forecolor']],
            ['view', ['codeview']],
        ],
        placeholder: @json($placeholder ?? ''),
    });
</script>
@endpush