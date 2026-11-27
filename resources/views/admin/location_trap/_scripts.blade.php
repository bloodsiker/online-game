<script>
    $('.trap-location-select').select2({
        theme: 'bootstrap',
        placeholder: 'Выберите локацию',
        allowClear: true,
        minimumInputLength: 0,
        ajax: {
            url: '{{ route('admin.api.locations') }}',
            dataType: 'json',
            delay: 250,
            data: function (p) { return { q: p.term, page: p.page || 1 }; },
            processResults: function (data, p) {
                p.page = p.page || 1;
                return { results: data.results, pagination: { more: data.pagination.more } };
            },
            cache: true
        }
    });

    // Поля config показываем только для выбранного типа; остальные отключаем, чтобы не уходили в форму.
    function syncTrapType() {
        var type = $('#trap-type').val();
        $('.trap-config').each(function () {
            var active = $(this).data('type') === type;
            $(this).toggle(active).find('[data-type-field]').prop('disabled', !active);
        });
    }
    $('#trap-type').on('change', syncTrapType);
    syncTrapType();
</script>
