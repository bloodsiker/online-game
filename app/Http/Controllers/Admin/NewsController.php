<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsComment;
use App\Models\NewsPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function list(): View
    {
        $news = NewsPost::query()
            ->withCount(['comments', 'visibleComments'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(50);

        return view('admin.news.list', compact('news'));
    }

    public function create(): View
    {
        return view('admin.news.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $news = new NewsPost;
        $this->fillNews($news, $request);
        $news->save();

        return redirect()->route('admin.news.info', $news->id)->with('success', 'Новость создана.');
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file' => ['required', 'image', 'max:4096'],
        ]);

        $file = $data['file'];
        $directory = 'news/'.date('Y/m');
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();

        $file->move(public_path($directory), $filename);

        return response()->json([
            'url' => asset($directory.'/'.$filename),
        ]);
    }

    public function info(Request $request, NewsPost $news): mixed
    {
        if ($request->isMethod('POST')) {
            $this->fillNews($news, $request);
            $news->save();

            return redirect()->back()->with('success', 'Новость сохранена.');
        }

        $news->load(['comments.user.player']);

        return view('admin.news.info', compact('news'));
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

    private function fillNews(NewsPost $news, Request $request): void
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
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
