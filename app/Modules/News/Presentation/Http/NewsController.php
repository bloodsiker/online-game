<?php

declare(strict_types=1);

namespace App\Modules\News\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Battle\Application\UseCases\GetHomepageBossBattles;
use App\Modules\Forum\Application\UseCases\GetHotForumTopics;
use App\Modules\Library\Presentation\Services\LibraryShortcodeRenderer;
use App\Modules\News\Application\UseCases\GetNewsFeed;
use App\Modules\News\Infrastructure\Persistence\Models\NewsComment;
use App\Modules\News\Infrastructure\Persistence\Models\NewsPost;
use App\Modules\Rating\Application\UseCases\GetHomepageRankings;
use App\Modules\User\Application\Services\GameSettingsService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request, LibraryShortcodeRenderer $shortcodeRenderer, GetHomepageRankings $rankings, GetHotForumTopics $forum, GetNewsFeed $newsFeed, GetHomepageBossBattles $bossBattles): View
    {
        $selectedNews = null;
        $isNewsArchive = $request->routeIs('news.index');
        $input = $isNewsArchive ? $request->validate(['page' => ['sometimes', 'integer', 'min:1', 'max:100000']]) : [];

        if ($request->filled('n')) {
            $selectedNews = NewsPost::query()
                ->where('is_active', true)
                ->with(['visibleComments.user.player'])
                ->findOrFail((int) $request->query('n'));

            if ($selectedNews) {
                $selectedNews->increment('views_count');
            }
        }

        $newsPosts = $selectedNews ? collect() : $newsFeed->execute(archive: $isNewsArchive, page: (int) ($input['page'] ?? 1));

        if ($selectedNews) {
            $selectedNews->rendered_description = $shortcodeRenderer->render($selectedNews->description);
        }

        if (! $isNewsArchive) {
            foreach ($newsPosts as $newsPost) {
                $newsPost->rendered_description = $shortcodeRenderer->render($newsPost->description);
            }
        }

        return view('news::index', [
            ...$rankings->execute(),
            'hotForumTopics' => $forum->execute(),
            'homepageBossBattles' => $bossBattles->execute(),
            'newsPosts' => $newsPosts,
            'selectedNews' => $selectedNews,
            'isNewsArchive' => $isNewsArchive,
            'newsItemTooltipScript' => $shortcodeRenderer->tooltipScript(),
            'onlineCount' => User::query()
                ->where('last_online_at', '>=', now()->subMinutes((int) config('game.online_timeout_minutes', 30)))
                ->count(),
        ]);
    }

    public function comment(Request $request, NewsPost $news): RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('index', ['n' => $news->id])->with('error', __('homepage.comments_auth_error'));
        }

        if (! $news->is_active || ! $news->allow_comments) {
            return redirect()->route('index', ['n' => $news->id])->with('error', __('homepage.comments_closed'));
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        NewsComment::query()->create([
            'news_post_id' => $news->id,
            'user_id' => auth()->id(),
            'message' => $data['message'],
            'is_visible' => true,
        ]);

        return redirect()->route('index', ['n' => $news->id])->with('success', __('homepage.comment_added'));
    }

    public function language(Request $request, GameSettingsService $settings): RedirectResponse
    {
        $data = $request->validate([
            'language' => ['required', 'in:ru,uk'],
            'news_id' => ['nullable', 'integer', 'min:1'],
            'archive_page' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);
        if ($request->user()) {
            $settings->update((int) $request->user()->id, ['language' => $data['language']]);
        }
        $request->session()->put('language', $data['language']);

        if (isset($data['news_id'])) {
            return redirect()->route('index', ['n' => $data['news_id']]);
        }

        return isset($data['archive_page'])
            ? redirect()->route('news.index', $data['archive_page'] > 1 ? ['page' => $data['archive_page']] : [])
            : redirect()->route('index');
    }
}
