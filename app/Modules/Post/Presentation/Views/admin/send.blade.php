@extends('admin.layout.base')

@section('title')
    Почта — Системное сообщение
@endsection

@section('body')

    <div class="row">
        <div class="col-md-5">
            <section class="card">
                <header class="card-header">
                    <h2 class="card-title">Отправить системное сообщение</h2>
                </header>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.post.send.store') }}">
                        @csrf

                        <div class="form-group mb-3">
                            <label class="form-label d-block">Получатель</label>
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="to-all" name="to_all" value="1" @checked(old('to_all'))>
                                <label class="form-check-label" for="to-all">Отправить всем игрокам</label>
                            </div>
                        </div>

                        <div class="form-group mb-3" id="nick-group">
                            <label class="form-label">Ник игрока</label>
                            <input type="text" name="nick" class="form-control" value="{{ old('nick') }}" placeholder="Точное имя персонажа">
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Тема</label>
                            <input type="text" name="subject" maxlength="64" class="form-control" value="{{ old('subject') }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Текст письма</label>
                            <textarea name="text" rows="5" class="form-control" required>{{ old('text') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-8">
                                <div class="form-group mb-3">
                                    <label class="form-label">Вложенный предмет</label>
                                    <select id="sel-letter-item" name="share_item_id" class="form-control">
                                        @if($selected = old('share_item_id'))
                                            @php($selectedItem = \App\Modules\Share\Infrastructure\Persistence\Models\ShareItem::find($selected))
                                            @if($selectedItem)
                                                <option value="{{ $selectedItem->id }}" selected>[{{ $selectedItem->id }}] {{ $selectedItem->name }}</option>
                                            @endif
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Количество</label>
                                    <input type="number" name="item_amount" min="1" class="form-control" value="{{ old('item_amount', 1) }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Деньги (монеты)</label>
                            <input type="number" name="money" min="0" class="form-control" value="{{ old('money', 0) }}">
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary"
                                    onclick="return document.getElementById('to-all').checked ? confirm('Отправить письмо ВСЕМ игрокам?') : true;">
                                Отправить
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>

        <div class="col-md-7">
            <section class="card">
                <header class="card-header">
                    <h2 class="card-title">Последние системные письма</h2>
                </header>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th width="130">Дата</th>
                                <th>Получатель</th>
                                <th>Тема</th>
                                <th>Вложения</th>
                                <th width="110">Статус</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($letters as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->created_at->format('d.m.Y H:i') }}</td>
                                    <td>{{ $item->recipient?->name ?? '—' }}</td>
                                    <td>{{ $item->subject }}</td>
                                    <td>
                                        @if($item->shareItem)
                                            {{ $item->shareItem->name }}{{ $item->item_amount > 1 ? ' ×'.$item->item_amount : '' }}
                                            <span class="text-muted">{{ $item->item_claimed_at ? '(забран)' : '' }}</span>
                                        @endif
                                        @if($item->money > 0)
                                            {{ $item->shareItem ? ', ' : '' }}{{ $item->money }} монет
                                            <span class="text-muted">{{ $item->money_claimed_at ? '(забраны)' : '' }}</span>
                                        @endif
                                        @if(! $item->shareItem && $item->money === 0)
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $item->isRead() ? 'badge-success' : 'badge-default' }}">
                                            {{ $item->isRead() ? 'Прочитано' : 'Не прочитано' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">Системных писем нет</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@push('footer_scripts')
<script>
    function formatItemOption(item) {
        if (!item.id) return item.text;
        var img = item.image
            ? '<img src="' + item.image + '" style="width:22px;height:22px;object-fit:contain;margin-right:6px;vertical-align:middle;">'
            : '';
        return $('<span>' + img + item.text + '</span>');
    }

    $('#sel-letter-item').select2({
        theme: 'bootstrap',
        placeholder: '— без предмета —',
        allowClear: true,
        templateResult: formatItemOption,
        templateSelection: formatItemOption,
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

    // Ник не нужен при отправке всем
    function toggleNick() {
        var all = document.getElementById('to-all').checked;
        document.querySelector('#nick-group input').disabled = all;
        document.getElementById('nick-group').style.opacity = all ? .5 : 1;
    }
    document.getElementById('to-all').addEventListener('change', toggleNick);
    toggleNick();
</script>
@endpush

@endsection