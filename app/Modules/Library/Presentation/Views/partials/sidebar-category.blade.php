@php
    $isSelected = isset($selectedCategory) && $selectedCategory?->id === $item->id;
@endphp

@if($depth === 0)
    <div class="b-common-block__bgl library-nav-root {{ $isSelected ? 'active' : '' }}">
        <a class="library-nav-root-link" href="{{ route('library.index', ['category' => $item->slug]) }}" title="{{ $item->name }}"><u>{{ $item->name }}</u></a>
    </div>
    @if($item->children->isNotEmpty())
        <div class="b-common-block__cont">
            <div class="b-common-block__bgl">
                <div class="clearfix" style="display:block">
                    <ul class="b-common-block__bgr library-nav-sub">
                        @foreach($item->children as $child)
                            @include('library::partials.sidebar-category', ['item' => $child, 'depth' => 1])
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
@else
    <li class="library-nav-item">
        <div class="library-nav-link-wrapper {{ $isSelected ? 'active' : '' }}">
            <a class="library-nav-link" href="{{ route('library.index', ['category' => $item->slug]) }}" title="{{ $item->name }}">{{ $item->name }}</a>
        </div>
        @if($item->children->isNotEmpty())
            <ul class="library-nav-sub">
                @foreach($item->children as $child)
                    @include('library::partials.sidebar-category', ['item' => $child, 'depth' => $depth + 1])
                @endforeach
            </ul>
        @endif
    </li>
@endif
