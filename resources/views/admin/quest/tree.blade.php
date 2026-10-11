@extends('admin.layout.base')

@section('title', 'Квесты — связи')

@section('body')
    <section class="card">
        <div class="card-body">
            @include('admin.quest._tabs')
            <form method="get" action="{{ route('admin.quests.tree') }}" class="mb-3">
                <div class="row align-items-end">
                    <div class="col-md-4 form-group">
                        <label for="quest-tree-relation">Вид связей</label>
                        <select name="relation" id="quest-tree-relation" class="form-control">
                            <option value="after_quest_id" @selected($relation === 'after_quest_id')>Цепочки: предыдущий → следующий</option>
                            <option value="parent_quest_id" @selected($relation === 'parent_quest_id')>Группировка: родитель → дочерние</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="quest-tree-search">Поиск квеста</label>
                        <input id="quest-tree-search" name="q" value="{{ $search }}" class="form-control" placeholder="ID или название">
                    </div>
                    <div class="col-md-4 form-group">
                        <button class="btn btn-primary">Показать</button>
                        <a href="{{ route('admin.quests.tree') }}" class="btn btn-default">Сбросить</a>
                    </div>
                </div>
            </form>
            <p class="text-muted">
                Квестов: {{ $questCount }} · Связей: {{ $linkCount }}.
                {{ $relation === 'after_quest_id' ? 'Стрелки показывают порядок прохождения обязательных квестов.' : 'Родительский квест задаёт группировку, а не обязательный порядок прохождения.' }}
                Поиск оставляет всю ветку найденного квеста. Нажмите на название для редактирования.
            </p>
            <div class="mb-3">
                <button type="button" class="btn btn-default btn-sm" data-quest-tree-toggle="open">Развернуть всё</button>
                <button type="button" class="btn btn-default btn-sm" data-quest-tree-toggle="close">Свернуть всё</button>
            </div>
            <div class="quest-tree-board">
                @forelse($trees as $node)
                    <div class="quest-tree-chain">
                        <ul class="quest-tree-root">
                            @include('admin.quest._tree_node', ['node' => $node])
                        </ul>
                    </div>
                @empty
                    <p class="text-muted">Квесты не найдены.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <style>
        .quest-tree-board { overflow-x: auto; }
        .quest-tree-chain { padding: 18px; margin-bottom: 16px; background: #f8fafc; border: 1px solid #dce3ea; border-radius: 6px; }
        .quest-tree-root, .quest-tree-children { list-style: none; margin: 0; padding: 0; }
        .quest-tree-children { margin-left: 22px; padding-left: 28px; border-left: 2px solid #bccbd9; }
        .quest-tree-children > li { position: relative; padding-top: 12px; }
        .quest-tree-children > li::before { content: '→'; position: absolute; left: -29px; top: 23px; width: 27px; color: #6687a5; background: #f8fafc; }
        .quest-tree-node summary { cursor: pointer; }
        .quest-tree-card { display: inline-block; vertical-align: middle; padding: 10px 14px; background: #fff; border: 1px solid #bccbd9; border-left: 4px solid #0088cc; border-radius: 4px; max-width: 600px; }
        .quest-tree-card--inactive { border-left-color: #aab2bb; }
        .quest-tree-card--warning { border-color: #d99a37; }
        .quest-tree-card--match { background: #fff6d8; }
        .quest-tree-card a { font-weight: 600; }
        .quest-tree-meta { margin-top: 4px; font-size: 12px; color: #667484; }
        .quest-tree-warning { margin-top: 4px; color: #a85b00; font-size: 12px; }
    </style>
@endpush

@push('footer_scripts')
    <script>
        document.querySelectorAll('[data-quest-tree-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                document.querySelectorAll('.quest-tree-node').forEach(function (node) {
                    node.open = button.dataset.questTreeToggle === 'open';
                });
            });
        });
    </script>
@endpush
