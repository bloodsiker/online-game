<li>
    <a class="map-node {{ $node->isCurrent ? 'map-node--current' : '' }}" href="{{ route('map.public', ['slug' => $node->slug]) }}" target="_blank" title="{{ $node->isCurrent ? 'Вы на этой карте' : 'Открыть карту для просмотра' }}">
        {{ $node->name }}
    </a>
    <button class="map-monsters-button" type="button" title="Монстры карты" aria-label="Монстры карты {{ $node->name }}" data-map-name="{{ $node->name }}" data-monsters-url="{{ route('map.monsters', ['map' => $node->id]) }}">⚔</button>
    <button class="map-resources-button" type="button" title="Ресурсы карты" aria-label="Ресурсы карты {{ $node->name }}" data-map-name="{{ $node->name }}" data-resources-url="{{ route('map.resources', ['map' => $node->id]) }}"><img src="{{ asset('data/canvas/ui/mining.webp') }}" alt=""></button>

    @if($node->children !== [])
        <ul>
            @foreach($node->children as $node)
                @include('location::partials.map-tree-node', ['node' => $node])
            @endforeach
        </ul>
    @endif
</li>
