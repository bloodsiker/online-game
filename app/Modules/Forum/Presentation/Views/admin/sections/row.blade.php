<tr style="vertical-align: middle">
    <td>{{ $forumSection->id }}</td>
    <td>
        @if($isChild)<span class="text-muted">&mdash;&nbsp;</span>@endif
        <a href="{{ route('admin.forum.sections.edit', $forumSection) }}">{{ $forumSection->name }}</a>
        <small class="text-muted d-block">/forum/section/{{ $forumSection->slug }}</small>
    </td>
    <td>{{ $isChild ? ($forumSection->parent?->name ?? '—') : 'Корневая категория' }}</td>
    <td>{{ $forumSection->sort_order }}</td>
    <td>{{ $forumSection->topics_count }}</td>
    <td>
        <span class="badge badge-{{ $forumSection->is_active ? 'success' : 'default' }}">
            {{ $forumSection->is_active ? 'Да' : 'Нет' }}
        </span>
    </td>
    <td>
        <small class="d-block">Темы: <b>{{ $forumSection->allow_topics ? 'да' : 'нет' }}</b></small>
        <small class="d-block">Комментарии: <b>{{ $forumSection->allow_comments ? 'да' : 'нет' }}</b></small>
    </td>
    <td class="text-end">
        <a href="{{ route('admin.forum.sections.edit', $forumSection) }}" class="btn btn-xs btn-primary">Изменить</a>
        <form action="{{ route('admin.forum.sections.destroy', $forumSection) }}" method="post" class="d-inline" onsubmit="return confirm('Удалить категорию «{{ $forumSection->name }}»?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-xs btn-danger">Удалить</button>
        </form>
    </td>
</tr>
