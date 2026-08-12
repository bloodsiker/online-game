@extends('admin.layout.base')

@section('title')
    НПС: {{ $npc->name }}
@endsection

@section('body')

    <div class="row">
        <div class="col-md-6">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.npc.info', $npc->id) }}" method="post" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label class="col-form-label">Имя</label>
                            <input type="text" class="form-control" name="name" value="{{ $npc->name }}">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Локация</label>
                            <select id="sel-location" name="location_id" class="form-control">
                                @if($npc->location)
                                    <option value="{{ $npc->location->id }}" selected>[{{ $npc->location->id }}] {{ $npc->location->name }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Описание</label>
                            <textarea class="form-control" name="description" rows="5">{{ $npc->description }}</textarea>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Изображение</label>
                            @if($npc->image)
                                <div class="mb-1">
                                    <img id="npc-preview" src="{{ $npc->image }}" alt=""
                                         style="width:64px;height:64px;object-fit:contain;border:1px solid #ddd;border-radius:4px;background:#f5f5f5;">
                                </div>
                            @else
                                <div class="mb-1">
                                    <img id="npc-preview" src="" alt=""
                                         style="width:64px;height:64px;object-fit:contain;border:1px solid #ddd;border-radius:4px;background:#f5f5f5;display:none;">
                                </div>
                            @endif
                            <input type="file" class="form-control mt-1" name="image" id="npc-image" accept="image/*">
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary">Сохранить</button>
                            <a href="{{ route('admin.npc') }}" class="btn btn-success">Назад</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
        <div class="col-md-6">
            <section class="card">
                <header class="card-header">
                    <h2 class="card-title">Новая ветка диалога</h2>
                </header>
                <div class="card-body">
                    <form action="{{ route('admin.npc.dialogue.node.add', $npc->id) }}" method="post">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label class="col-form-label">Заголовок</label>
                            <input type="text" class="form-control" name="title" value="{{ old('title') }}" required>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Описание</label>
                            <textarea class="form-control" name="description" rows="6" required>{{ old('description') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Порядок</label>
                            <input type="number" min="0" max="65535" class="form-control" name="sort_order" value="{{ old('sort_order', 0) }}">
                        </div>
                        <div class="checkbox-custom checkbox-default">
                            <input type="checkbox" id="new-node-active" name="is_active" value="1" checked>
                            <label for="new-node-active">Активна</label>
                        </div>
                        <div class="checkbox-custom checkbox-default mt-2">
                            <input type="checkbox" id="new-node-start" name="is_start" value="1" @if($npc->dialogueNodes->isEmpty()) checked @endif>
                            <label for="new-node-start">Стартовая ветка</label>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary">Добавить ветку</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <div class="row mt-4" id="dialogues">
        <div class="col-md-12">
            <section class="card">
                <header class="card-header">
                    <h2 class="card-title">Диалоговые ветки</h2>
                </header>
                <div class="card-body">
                    @forelse($npc->dialogueNodes as $node)
                        <div class="card mb-3">
                            <header class="card-header">
                                <h2 class="card-title">
                                    #{{ $node->id }} {{ $node->title }}
                                    @if($node->is_start)
                                        <span class="badge badge-success">старт</span>
                                    @endif
                                    @unless($node->is_active)
                                        <span class="badge badge-secondary">выключена</span>
                                    @endunless
                                </h2>
                            </header>
                            <div class="card-body">
                                <form action="{{ route('admin.npc.dialogue.node.update', [$npc->id, $node->id]) }}" method="post">
                                    {{ csrf_field() }}
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label class="col-form-label">Заголовок</label>
                                                <input type="text" class="form-control" name="title" value="{{ $node->title }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="col-form-label">Порядок</label>
                                                <input type="number" min="0" max="65535" class="form-control" name="sort_order" value="{{ $node->sort_order }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="col-form-label">&nbsp;</label>
                                            <div class="checkbox-custom checkbox-default">
                                                <input type="checkbox" id="node-active-{{ $node->id }}" name="is_active" value="1" @checked($node->is_active)>
                                                <label for="node-active-{{ $node->id }}">Активна</label>
                                            </div>
                                            <div class="checkbox-custom checkbox-default">
                                                <input type="checkbox" id="node-start-{{ $node->id }}" name="is_start" value="1" @checked($node->is_start)>
                                                <label for="node-start-{{ $node->id }}">Старт</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Описание</label>
                                        <textarea class="form-control" name="description" rows="5" required>{{ $node->description }}</textarea>
                                    </div>
                                    <button class="btn btn-primary btn-sm">Сохранить ветку</button>
                                    <a href="{{ route('admin.npc.dialogue.node.delete', [$npc->id, $node->id]) }}"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Удалить ветку диалога?')">Удалить</a>
                                </form>

                                <hr>
                                <h4>Кнопки переходов</h4>
                                @foreach($node->options as $option)
                                    <form id="option-update-{{ $option->id }}" action="{{ route('admin.npc.dialogue.option.update', [$npc->id, $node->id, $option->id]) }}" method="post">
                                        {{ csrf_field() }}
                                    </form>
                                @endforeach
                                <form id="option-add-{{ $node->id }}" action="{{ route('admin.npc.dialogue.option.add', [$npc->id, $node->id]) }}" method="post">
                                    {{ csrf_field() }}
                                </form>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                        <tr>
                                            <th>Текст кнопки</th>
                                            <th width="260">Ведет на ветку</th>
                                            <th width="90">Порядок</th>
                                            <th width="90">Активна</th>
                                            <th width="150"></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($node->options as $option)
                                            <tr>
                                                <td>
                                                    <input form="option-update-{{ $option->id }}" type="text" class="form-control" name="button_text" value="{{ $option->button_text }}" required>
                                                </td>
                                                <td>
                                                    <select form="option-update-{{ $option->id }}" name="to_node_id" class="form-control" required>
                                                        @foreach($npc->dialogueNodes as $targetNode)
                                                            <option value="{{ $targetNode->id }}" @selected($option->to_node_id === $targetNode->id)>
                                                                #{{ $targetNode->id }} {{ $targetNode->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input form="option-update-{{ $option->id }}" type="number" min="0" max="65535" class="form-control" name="sort_order" value="{{ $option->sort_order }}">
                                                </td>
                                                <td class="text-center">
                                                    <input form="option-update-{{ $option->id }}" type="checkbox" name="is_active" value="1" @checked($option->is_active)>
                                                </td>
                                                <td>
                                                    <button form="option-update-{{ $option->id }}" class="btn btn-primary btn-xs">Сохранить</button>
                                                    <a href="{{ route('admin.npc.dialogue.option.delete', [$npc->id, $node->id, $option->id]) }}"
                                                       class="btn btn-danger btn-xs"
                                                       onclick="return confirm('Удалить кнопку?')">Удалить</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-muted text-center">Кнопок пока нет</td></tr>
                                        @endforelse
                                        <tr>
                                            <td>
                                                <input form="option-add-{{ $node->id }}" type="text" class="form-control" name="button_text" placeholder="Например: Расскажи о городе" required>
                                            </td>
                                            <td>
                                                <select form="option-add-{{ $node->id }}" name="to_node_id" class="form-control" required>
                                                    @foreach($npc->dialogueNodes as $targetNode)
                                                        <option value="{{ $targetNode->id }}">#{{ $targetNode->id }} {{ $targetNode->title }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input form="option-add-{{ $node->id }}" type="number" min="0" max="65535" class="form-control" name="sort_order" value="0">
                                            </td>
                                            <td class="text-center">
                                                <input form="option-add-{{ $node->id }}" type="checkbox" name="is_active" value="1" checked>
                                            </td>
                                            <td>
                                                <button form="option-add-{{ $node->id }}" class="btn btn-success btn-xs">Добавить</button>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">У этого НПС еще нет диалоговых веток.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <script>
        document.getElementById('npc-image').addEventListener('change', function () {
            const preview = document.getElementById('npc-preview');
            if (this.files[0]) {
                preview.src = URL.createObjectURL(this.files[0]);
                preview.style.display = 'block';
            }
        });
    </script>

@push('footer_scripts')
<script>
    $('#sel-location').select2({
        theme: 'bootstrap',
        placeholder: 'Выберите локацию',
        allowClear: true,
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
        },
        minimumInputLength: 0
    });
</script>
@endpush

@endsection
