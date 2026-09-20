<li>
    <span class="library-map-tree__node-row">
        <a class="library-map-node {{ $node->isCurrent ? 'library-map-node--current' : '' }}"
           href="{{ route('map.public', ['slug' => $node->slug]) }}"
           target="_blank"
           rel="noopener"
           title="{{ $node->isCurrent ? 'Вы на этой карте' : 'Открыть карту для просмотра' }}">
            {{ $node->name }}
        </a>
        @if($bestiaryCategory)
            <a class="library-map-monsters"
               href="{{ route('library.index', ['category' => $bestiaryCategory->slug, 'map_id' => $node->id]) }}"
               title="Монстры карты {{ $node->name }}"
               aria-label="Монстры карты {{ $node->name }}">⚔</a>
        @endif
    </span>

    @if($node->children !== [])
        <ul>
            @foreach($node->children as $childNode)
                @include('library::partials.map-tree-node', ['node' => $childNode, 'bestiaryCategory' => $bestiaryCategory])
            @endforeach
        </ul>
    @endif
</li>
