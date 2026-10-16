<?php

declare(strict_types=1);

namespace App\Modules\News\Presentation\Http\Admin;

use App\Http\Controllers\Controller;
use App\Modules\News\Infrastructure\Persistence\Models\NewsComment;
use App\Modules\News\Infrastructure\Persistence\Models\NewsPost;
use App\Modules\Share\Application\Services\ContentTranslationEditor;
use App\Services\Media\AdminImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function __construct(private readonly AdminImageStorage $imageStorage) {}

    public function list(): View
    {
        $news = NewsPost::query()
            ->withCount(['comments', 'visibleComments'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(50);

        return view('news::admin.news.list', compact('news'));
    }

    public function create(): View
    {
        return view('news::admin.news.create');
    }

    public function store(Request $request, ContentTranslationEditor $translationEditor): RedirectResponse
    {
        $news = new NewsPost;
        $this->fillNews($news, $request, $translationEditor);
        $news->save();

        return redirect()->route('admin.news.info', $news->id)->with('success', 'Новость создана.');
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file' => ['required', 'image', 'max:4096'],
        ]);

        $path = $this->imageStorage->storeInPublicDirectory($data['file'], 'news');

        return response()->json(['url' => asset($path)]);
    }

    public function info(Request $request, NewsPost $news, ContentTranslationEditor $translationEditor): View|RedirectResponse
    {
        if ($request->isMethod('POST')) {
            $this->fillNews($news, $request, $translationEditor);
            $news->save();

            return redirect()->back()->with('success', 'Новость сохранена.');
        }

        $news->load(['comments.user.player']);

        return view('news::admin.news.info', compact('news'));
    }

    public function delete(NewsPost $news): RedirectResponse
    {
        $news->delete();

        return redirect()->route('admin.news')->with('success', 'Новость удалена.');
    }

    public function deleteComment(NewsPost $news, NewsComment $comment): RedirectResponse
    {
        if ((int) $comment->news_post_id !== (int) $news->id) {
            abort(404);
        }

        $comment->delete();

        return redirect()->back()->with('success', 'Комментарий удален.');
    }

    public function toggleComment(NewsPost $news, NewsComment $comment): RedirectResponse
    {
        if ((int) $comment->news_post_id !== (int) $news->id) {
            abort(404);
        }

        $comment->update(['is_visible' => ! $comment->is_visible]);

        return redirect()->back()->with('success', 'Комментарий обновлен.');
    }

    private function fillNews(NewsPost $news, Request $request, ContentTranslationEditor $translationEditor): void
    {
        $translationEditor->fill($news, $request, ['title', 'description'], ['title', 'description']);
        $data = $request->validate([
            'views_count' => ['nullable', 'integer', 'min:0'],
            'created_at' => ['nullable', 'date'],
        ]);

        $news->title = $data['title'];
        $news->description = $data['description'];
        $news->views_count = (int) ($data['views_count'] ?? 0);
        $news->allow_comments = $request->boolean('allow_comments');
        $news->is_active = $request->boolean('is_active');

        if (! empty($data['created_at'])) {
            $news->created_at = $data['created_at'];
        }
    }
}
