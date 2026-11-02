<?php

declare(strict_types=1);

namespace App\Modules\News\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Forum\Application\UseCases\GetHotForumTopics;
use App\Modules\Library\Presentation\Services\LibraryShortcodeRenderer;
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
    public function index(Request $request, LibraryShortcodeRenderer $shortcodeRenderer, GetHomepageRankings $rankings, GetHotForumTopics $forum): View
    {
        $selectedNews = null;

        if ($request->filled('n')) {
            $selectedNews = NewsPost::query()
                ->where('is_active', true)
                ->with(['visibleComments.user.player'])
                ->findOrFail((int) $request->query('n'));

            if ($selectedNews) {
                $selectedNews->increment('views_count');
            }
        }

        $newsPosts = $selectedNews ? collect() : NewsPost::query()
            ->where('is_active', true)
            ->withCount('visibleComments')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        if ($selectedNews) {
            $selectedNews->rendered_description = $shortcodeRenderer->render($selectedNews->description);
        }

        foreach ($newsPosts as $newsPost) {
            $newsPost->rendered_description = $shortcodeRenderer->render($newsPost->description);
        }

        return view('news::index', [
            ...$rankings->execute(),
            'hotForumTopics' => $forum->execute(),
            'newsPosts' => $newsPosts,
            'selectedNews' => $selectedNews,
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
        ]);
        if ($request->user()) {
            $settings->update((int) $request->user()->id, ['language' => $data['language']]);
        }
        $request->session()->put('language', $data['language']);

        return redirect()->route('index', isset($data['news_id']) ? ['n' => $data['news_id']] : []);
    }
}
