<nav class="mb-3" aria-label="Разделы чата">
    <a href="{{ route('admin.chat.index') }}"
       class="btn btn-sm {{ request()->routeIs('admin.chat.index') ? 'btn-primary' : 'btn-default' }}"
       @if(request()->routeIs('admin.chat.index')) aria-current="page" @endif>Системные сообщения</a>
    <a href="{{ route('admin.chat.messages') }}"
       class="btn btn-sm {{ request()->routeIs('admin.chat.messages') ? 'btn-primary' : 'btn-default' }}"
       @if(request()->routeIs('admin.chat.messages')) aria-current="page" @endif>Сообщения игроков</a>
</nav>
