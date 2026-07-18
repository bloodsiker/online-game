<?php

namespace App\Http\Controllers;

use App\Models\NewsComment;
use App\Models\NewsPost;
use App\Services\News\NewsShortcodeRenderer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MainController extends Controller
{
    public function index(Request $request)
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

        $shortcodeRenderer = app(NewsShortcodeRenderer::class);

        if ($selectedNews) {
            $selectedNews->rendered_description = $shortcodeRenderer->render($selectedNews->description);
        }

        foreach ($newsPosts as $newsPost) {
            $newsPost->rendered_description = $shortcodeRenderer->render($newsPost->description);
        }

        $newsItemTooltipScript = $shortcodeRenderer->tooltipScript();

        return view('main.index', compact('newsPosts', 'selectedNews', 'newsItemTooltipScript'));
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

        NewsComment::create([
            'news_post_id' => $news->id,
            'user_id' => auth()->id(),
            'message' => $data['message'],
            'is_visible' => true,
        ]);

        return redirect()->route('index', ['n' => $news->id])->with('success', 'Комментарий добавлен.');
    }
}
