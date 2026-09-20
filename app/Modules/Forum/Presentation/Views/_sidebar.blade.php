@php
    $activeForumSection = $section ?? ($topic ?? null)?->section ?? ($post ?? null)?->topic?->section ?? null;
@endphp

@forelse($forumSections ?? [] as $forumSection)
    <div class="b-common-block__bgl forum-nav-group">
        @if($forumSection->children->isNotEmpty())
            <span class="forum-nav-group__title forum-nav-group__title--parent" title="{{ $forumSection->name }}">{{ $forumSection->name }}</span>
        @else
            <a class="forum-nav-group__title" href="{{ route('forum.section', ['slug' => $forumSection->slug]) }}" title="{{ $forumSection->name }}">
                <u>{{ $forumSection->name }}</u>
            </a>
        @endif
    </div>

    @if($forumSection->children->isNotEmpty())
        <div class="b-common-block__cont forum-nav-children">
            <div class="b-common-block__bgl">
                <div class="clearfix">
                    <ul class="b-common-block__bgr forum-nav-list">
                        @foreach($forumSection->children as $childSection)
                            <li class="forum-nav-item">
                                <div class="forum-nav-link-wrapper {{ (int) $activeForumSection?->id === (int) $childSection->id ? 'active' : '' }}">
                                    <a class="forum-nav-link" href="{{ route('forum.section', ['slug' => $childSection->slug]) }}">{{ $childSection->name }}</a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
@empty
    <div class="forum-empty">Разделы пока не добавлены.</div>
@endforelse
