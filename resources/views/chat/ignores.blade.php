<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Список игнора</title>
    <style>
        body { font-family: Tahoma; font-size: 12px; background: #ffe4aa; margin: 5px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 4px 8px; text-align: left; border-bottom: 1px solid #c8a060; }
        th { background: #c8a060; color: #fff; }
        .btn-remove { color: red; cursor: pointer; text-decoration: none; font-size: 11px; }
        .btn-remove:hover { text-decoration: underline; }
        h3 { margin: 0 0 8px 0; color: #5B4736; }
    </style>
</head>
<body>
    <h3>Список игнора</h3>

    @if ($ignores->isEmpty())
        <p>Список пуст.</p>
    @else
        <table>
            <thead>
                <tr><th>Игрок</th><th></th></tr>
            </thead>
            <tbody>
                @foreach ($ignores as $ignore)
                    <tr id="ignore-row-{{ $ignore->ignored_user_id }}">
                        <td>{{ $ignore->ignoredUser?->name ?? '—' }}</td>
                        <td>
                            <a class="btn-remove"
                               href="#"
                               onclick="removeIgnore({{ $ignore->ignored_user_id }}); return false;">
                                удалить
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

<script>
    var csrfToken = '{{ csrf_token() }}';

    function removeIgnore(userId) {
        fetch('/chat/ignore/' + userId, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.ok) {
                var row = document.getElementById('ignore-row-' + userId);
                if (row) row.remove();
            }
        });
    }
</script>
</body>
</html>