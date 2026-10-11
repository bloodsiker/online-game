<?php

declare(strict_types=1);

namespace App\Modules\News\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Item\Presentation\Services\ItemShortcodeRenderer;
use App\Modules\News\Infrastructure\Persistence\Models\NewsComment;
use App\Modules\News\Infrastructure\Persistence\Models\NewsPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request, ItemShortcodeRenderer $shortcodeRenderer): View
    {
        $selectedNews = null;

        if ($request->filled('n')) {
            $selectedNews = NewsPost::query()
                ->where('is_active', true)
                ->with(['visibleComments.user.player'])
                ->find((int) $request->query('n'));

            if ($selectedNews) {
                $selectedNews->increment('views_count');
            }
        }

        $newsPosts = NewsPost::query()
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
            'newsPosts' => $newsPosts,
            'selectedNews' => $selectedNews,
            'newsItemTooltipScript' => $shortcodeRenderer->tooltipScript(),
        ]);
    }

    public function comment(Request $request, NewsPost $news): RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('index', ['n' => $news->id])->with('error', 'Комментарии доступны только авторизованным игрокам.');
        }

        if (! $news->is_active || ! $news->allow_comments) {
            return redirect()->route('index', ['n' => $news->id])->with('error', 'Комментарии к этой новости закрыты.');
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

        return redirect()->route('index', ['n' => $news->id])->with('success', 'Комментарий добавлен.');
    }
}
