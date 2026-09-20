<?php

declare(strict_types=1);

namespace App\Modules\Forum\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Forum\Application\Services\ForumService;
use App\Modules\Forum\Application\Services\ForumWriter;
use App\Modules\Forum\Infrastructure\Persistence\Models\ForumPost;
use App\Modules\Moderation\Application\Services\CommunicationMuteService;
use App\Modules\Moderation\Domain\Enums\CommunicationScope;
use App\Modules\Moderation\Domain\Exceptions\UserMutedException;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ForumController extends Controller
{
    public function __construct(
        private readonly ForumService $forum,
        private readonly ForumWriter $writer,
        private readonly CommunicationMuteService $communicationMuteService,
    ) {}

    public function index(): View
    {
        $sections = $this->forum->sectionsTree();

        return view('forum::index', [
            'sections' => $sections,
            'forumSections' => $sections,
        ]);
    }

    public function section(string $slug): View
    {
        $section = $this->forum->findSection($slug);
        /** @var ?User $user */
        $user = Auth::user();

        return view('forum::section', [
            'section' => $section,
            'topics' => $this->forum->sectionTopics($section),
            'forumSections' => $this->forum->sectionsTree(),
            'forumMute' => $user === null
                ? null
                : $this->communicationMuteService->activeMute($user, CommunicationScope::Forum),
            'canCreateTopic' => $user !== null && $this->writer->canCreateTopic($user, $section),
        ]);
    }

    public function search(Request $request): View|RedirectResponse
    {
        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 2) {
            return redirect()
                ->route('forum.index')
                ->with('message', 'Введите для поиска минимум 2 символа.');
        }

        $sections = $this->forum->sectionsTree();

        return view('forum::search', [
            'query' => $query,
            'topics' => $this->forum->searchTopics($query),
            'forumSections' => $sections,
        ]);
    }

    public function topic(int $id): View
    {
        $topic = $this->forum->findTopic($id);

        $viewedKey = 'forum_viewed_'.$topic->id;
        if (! session()->has($viewedKey)) {
            $topic->increment('views_count');
            session()->put($viewedKey, true);
        }

        /** @var ?User $user */
        $user = Auth::user();

        return view('forum::topic', [
            'topic' => $topic,
            'posts' => $this->forum->topicPosts($topic),
            'userVote' => $this->forum->userVote($topic->id, $user?->id),
            'canModerate' => $user !== null && $this->writer->canModerate($user),
            'canComment' => $user !== null && $this->writer->canComment($user, $topic->section),
            'forumSections' => $this->forum->sectionsTree(),
            'forumMute' => $user === null
                ? null
                : $this->communicationMuteService->activeMute($user, CommunicationScope::Forum),
        ]);
    }

    public function storeTopic(Request $request, string $slug): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $section = $this->forum->findSection($slug);
        $data = $request->validate([
            'title' => ['required', 'string', 'min:5', 'max:150'],
            'body' => ['required', 'string', 'max:30000'],
        ]);

        try {
            $topic = $this->writer->createTopic($user, $section, $data['title'], $data['body']);
        } catch (UserMutedException $exception) {
            return redirect()->back()->withInput()->withErrors(['body' => $exception->getMessage()]);
        }

        return redirect()->route('forum.topic', ['id' => $topic->id])->with('message', 'Тема создана.');
    }

    public function storePost(Request $request, int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $topic = $this->forum->findTopic($id);
        $data = $request->validate(['body' => ['required', 'string', 'max:30000']]);

        try {
            $post = $this->writer->createPost($user, $topic, $data['body']);
        } catch (UserMutedException $exception) {
            return redirect()->back()->withInput()->withErrors(['body' => $exception->getMessage()]);
        }
        $page = (int) ceil($topic->fresh()->posts_count / 15);
        $params = ['id' => $topic->id];
        if ($page > 1) {
            $params['page'] = $page;
        }

        return redirect(route('forum.topic', $params).'#post-'.$post->id)->with('message', 'Сообщение добавлено.');
    }

    public function editPost(int $id): View
    {
        /** @var User $user */
        $user = Auth::user();
        $post = ForumPost::query()->with('topic.section')->findOrFail($id);
        abort_unless($this->writer->canEdit($user, $post), 403);

        return view('forum::post-edit', [
            'post' => $post,
            'forumSections' => $this->forum->sectionsTree(),
            'forumMute' => $this->communicationMuteService->activeMute($user, CommunicationScope::Forum),
        ]);
    }

    public function updatePost(Request $request, int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $post = ForumPost::query()->findOrFail($id);
        $data = $request->validate(['body' => ['required', 'string', 'max:30000']]);

        try {
            $this->writer->updatePost($user, $post, $data['body']);
        } catch (UserMutedException $exception) {
            return redirect()->back()->withInput()->withErrors(['body' => $exception->getMessage()]);
        }

        return redirect()->route('forum.topic', ['id' => $post->topic_id])->with('message', 'Сообщение обновлено.');
    }

    public function destroyPost(int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $post = ForumPost::query()->findOrFail($id);
        $topicId = $post->topic_id;
        $topic = $this->writer->deletePost($user, $post);

        if ($topic === null) {
            return redirect()->route('forum.index')->with('message', 'Тема удалена.');
        }

        return redirect()->route('forum.topic', ['id' => $topicId])->with('message', 'Сообщение удалено.');
    }

    public function vote(Request $request, int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $topic = $this->forum->findTopic($id);
        $data = $request->validate(['value' => ['required', 'integer', 'in:1,-1']]);

        $this->writer->vote($user, $topic, (int) $data['value']);

        return redirect()->back();
    }

    public function pin(int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $topic = $this->forum->findTopic($id);
        $this->writer->setPinned($user, $topic, ! $topic->is_pinned);

        return redirect()->back()->with('message', $topic->is_pinned ? 'Тема откреплена.' : 'Тема закреплена.');
    }

    public function lock(int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $topic = $this->forum->findTopic($id);
        $this->writer->setLocked($user, $topic, ! $topic->is_locked);

        return redirect()->back()->with('message', $topic->is_locked ? 'Тема разблокирована.' : 'Тема заблокирована.');
    }

    public function destroyTopic(int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $topic = $this->forum->findTopic($id);
        $sectionSlug = $topic->section->slug;
        $this->writer->deleteTopic($user, $topic);

        return redirect()->route('forum.section', ['slug' => $sectionSlug])->with('message', 'Тема удалена.');
    }
}
