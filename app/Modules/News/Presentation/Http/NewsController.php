<?php

declare(strict_types=1);

namespace App\Modules\News\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Battle\Application\UseCases\GetHomepageBossBattles;
use App\Modules\Forum\Application\UseCases\GetHotForumTopics;
use App\Modules\Library\Presentation\Services\LibraryShortcodeRenderer;
use App\Modules\News\Application\UseCases\GetNewsFeed;
use App\Modules\News\Application\UseCases\GetPublicPricing;
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
            ...$this->layoutData($rankings, $forum, $bossBattles),
            'newsPosts' => $newsPosts,
            'selectedNews' => $selectedNews,
            'isNewsArchive' => $isNewsArchive,
            'newsItemTooltipScript' => $shortcodeRenderer->tooltipScript(),
        ]);
    }

    public function eula(GetHomepageRankings $rankings, GetHotForumTopics $forum, GetHomepageBossBattles $bossBattles): View
    {
        return $this->infoPage('eula', $rankings, $forum, $bossBattles);
    }

    public function pricing(GetHomepageRankings $rankings, GetHotForumTopics $forum, GetHomepageBossBattles $bossBattles, GetPublicPricing $pricing, LibraryShortcodeRenderer $shortcodeRenderer): View
    {
        $data = $pricing->execute();
        // Блоки предметов с картинкой и игровым тултипом — тем же рендером, что и [[item:ID]] в новостях.
        $card = fn (object $item): string => '<div class="pricing-item">[[item:'.$item->id.']]'
            .'<span class="pricing-item__name">'.e($item->name).'</span>'
            .'<span class="pricing-item__price"><img src="'.e(asset('img/icon/m_dmd.gif')).'" width="11" height="11" alt=""> '.e(format_money($item->diamonds)).'</span></div>';
        $cards = $data['categories']->map(fn (object $category): string => '<h3 class="pricing-category"><span>'.e($category->name).'</span></h3>'
            .'<div class="pricing-items">'.$category->items->map($card)->implode('').'</div>')->implode('');

        return $this->infoPage('pricing', $rankings, $forum, $bossBattles, [
            'pricing' => $data,
            'pricingItemsHtml' => $shortcodeRenderer->render($cards),
            'newsItemTooltipScript' => $shortcodeRenderer->tooltipScript(),
        ]);
    }

    public function contacts(GetHomepageRankings $rankings, GetHotForumTopics $forum, GetHomepageBossBattles $bossBattles): View
    {
        return $this->infoPage('contacts', $rankings, $forum, $bossBattles);
    }

    /** @param  array<string, mixed>  $extra */
    private function infoPage(string $page, GetHomepageRankings $rankings, GetHotForumTopics $forum, GetHomepageBossBattles $bossBattles, array $extra = []): View
    {
        return view('news::index', [
            ...$this->layoutData($rankings, $forum, $bossBattles),
            'newsPosts' => collect(),
            'selectedNews' => null,
            'isNewsArchive' => false,
            'isEulaPage' => $page === 'eula',
            'infoPage' => $page,
            'newsItemTooltipScript' => '',
            ...$extra,
        ]);
    }

    /** @return array<string, mixed> */
    private function layoutData(GetHomepageRankings $rankings, GetHotForumTopics $forum, GetHomepageBossBattles $bossBattles): array
    {
        return [
            ...$rankings->execute(),
            'hotForumTopics' => $forum->execute(),
            'homepageBossBattles' => $bossBattles->execute(),
            'onlineCount' => User::query()
                ->where('last_online_at', '>=', now()->subMinutes((int) config('game.online_timeout_minutes', 30)))
                ->count(),
        ];
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
