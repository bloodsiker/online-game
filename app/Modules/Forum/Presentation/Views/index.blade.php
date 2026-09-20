@extends('forum::layout')

@section('title', 'Форум')
@section('panel-title', 'Форум')

@section('content')
    <div class="forum-page">
        @if(session('message'))
            <div class="forum-flash">{{ session('message') }}</div>
        @endif

        @include('forum::_heading')

        <div class="forum-line2-separator" aria-hidden="true">
            <img src="{{ asset('main/images/theme_old/line2_left.gif') }}" width="197" height="13" alt="">
            <img src="{{ asset('main/images/theme_old/line2_right.gif') }}" width="197" height="13" alt="">
        </div>
        <div class="forum-home-breadcrumb">Форум</div>
        <div class="forum-line2-separator forum-line2-separator--bottom" aria-hidden="true">
            <img src="{{ asset('main/images/theme_old/line2_left.gif') }}" width="197" height="13" alt="">
            <img src="{{ asset('main/images/theme_old/line2_right.gif') }}" width="197" height="13" alt="">
        </div>

        @php
            $leafSections = $sections->flatMap(
                fn ($rootSection) => $rootSection->children->isEmpty()
                    ? collect([$rootSection])
                    : $rootSection->children
            );
        @endphp

        @if($leafSections->isEmpty())
            <div class="forum-empty">Разделы пока не добавлены.</div>
        @else
            <table class="forum-home-sections">
                <tbody>
                    @foreach($leafSections as $forumSection)
                        @include('forum::_section-row', ['forumSection' => $forumSection])
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="forum-news-separator" aria-hidden="true">
            <img src="{{ asset('main/images/theme_old/tbl-mn_news-sep-l.gif') }}" width="130" height="26" alt="">
            <img src="{{ asset('main/images/theme_old/tbl-mn_news-sep-r.gif') }}" width="130" height="26" alt="">
        </div>
    </div>
@endsection
