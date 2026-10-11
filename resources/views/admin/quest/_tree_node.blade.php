@php
    $quest = $node['quest'];
    $isMatch = $search !== '' && ((string) $quest->id === mb_strtolower($search) || str_contains(mb_strtolower($quest->title), mb_strtolower($search)));
@endphp
<li>
    @if($node['children'])
        <details class="quest-tree-node" open>
            <summary>
    @endif
    <div class="quest-tree-card {{ !$quest->is_active ? 'quest-tree-card--inactive' : '' }} {{ $node['cycle'] || $node['missing_parent_id'] ? 'quest-tree-card--warning' : '' }} {{ $isMatch ? 'quest-tree-card--match' : '' }}">
        <a href="{{ route('admin.quest.info', $quest->id) }}">#{{ $quest->id }} — {{ $quest->title }}</a>
        <div class="quest-tree-meta">
            {{ $quest->type->label() }} · {{ $quest->is_active ? 'Активен' : 'Неактивен' }}{{ $quest->is_finish ? ' · Финальный' : '' }}
            @if($quest->startNpc)
                · NPC: {{ $quest->startNpc->name }}
            @endif
        </div>
        @if($node['cycle'])
            <div class="quest-tree-warning">Циклическая связь — квест уже встречается в этой ветке.</div>
        @endif
        @if($node['missing_parent_id'])
            <div class="quest-tree-warning">Связанный квест #{{ $node['missing_parent_id'] }} не найден.</div>
        @endif
    </div>
    @if($node['children'])
            </summary>
            <ul class="quest-tree-children">
                @foreach($node['children'] as $child)
                    @include('admin.quest._tree_node', ['node' => $child])
                @endforeach
            </ul>
        </details>
    @endif
</li>
