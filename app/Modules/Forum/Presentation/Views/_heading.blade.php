<div class="forum-home-heading">
    <h1>Форум игры</h1>
    <form class="forum-search" action="{{ route('forum.search') }}" method="get">
        <input type="search" name="q" value="{{ $searchQuery ?? '' }}" minlength="2" maxlength="100" aria-label="Поиск по форуму">
        <button type="submit" title="Найти" aria-label="Найти">
            <img src="{{ asset('main/images/theme_old/search.gif') }}" width="63" height="24" alt="">
        </button>
    </form>
</div>
