<script>
    var gateLocationAjax = {
        url: '{{ route('admin.api.locations') }}',
        dataType: 'json',
        delay: 250,
        data: function (p) { return { q: p.term, page: p.page || 1 }; },
        processResults: function (data, p) {
            p.page = p.page || 1;
            return { results: data.results, pagination: { more: data.pagination.more } };
        },
        cache: true
    };

    $('.gate-location-select').each(function () {
        $(this).select2({
            theme: 'bootstrap',
            placeholder: 'Выберите локацию',
            allowClear: true,
            ajax: gateLocationAjax,
            minimumInputLength: 0
        });
    });

    function formatGateItemOption(item) {
        if (!item.id) return item.text;
        var img = item.image ? '<img src="' + item.image + '" style="width:22px;height:22px;object-fit:contain;margin-right:6px;vertical-align:middle;">' : '';
        return $('<span>' + img + item.text + '</span>');
    }

    $('#gate-item-select').select2({
        theme: 'bootstrap',
        placeholder: 'Выберите предмет',
        allowClear: true,
        templateResult: formatGateItemOption,
        templateSelection: formatGateItemOption,
        ajax: {
            url: '{{ route('admin.api.items') }}',
            dataType: 'json',
            delay: 250,
            data: function (p) { return { q: p.term, page: p.page || 1 }; },
            processResults: function (data, p) {
                p.page = p.page || 1;
                return { results: data.results, pagination: { more: data.pagination.more } };
            },
            cache: true
        },
        minimumInputLength: 0
    });
</script>