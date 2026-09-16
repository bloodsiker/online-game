<?php

declare(strict_types=1);

namespace App\Modules\Library\Presentation\Http\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Library\Domain\Services\LibraryEntityRegistry;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryCategory;
use App\Services\Media\AdminImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LibraryArticleController extends Controller
{
    private readonly AdminImageStorage $imageStorage;

    public function __construct(
        private readonly LibraryEntityRegistry $entities,
        ?AdminImageStorage $imageStorage = null,
    ) {
        $this->imageStorage = $imageStorage ?? new AdminImageStorage;
    }

    public function index(Request $request): View
    {
        $articles = LibraryArticle::query()
            ->with('category')
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', (int) $request->input('category_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('q'), fn ($query) => $query->where('title', 'like', '%'.$request->input('q').'%'))
            ->orderByDesc('updated_at')
            ->paginate(50)
            ->withQueryString();

        return view('admin.library.articles.index', [
            'articles' => $articles,
            'categories' => LibraryCategory::query()->orderBy('name')->get(),
            'statuses' => LibraryArticle::STATUSES,
        ]);
    }

    public function create(): View
    {
        return $this->formView(null);
    }

    public function store(Request $request): RedirectResponse
    {
        $article = new LibraryArticle;
        $data = $this->validated($request, $article);

        DB::transaction(function () use ($article, $data, $request): void {
            $this->fill($article, $data, $request);
            $article->author_id = auth()->id();
            $article->save();
            $this->syncLinks($article, $data['links'] ?? []);
        });

        return redirect()->route('admin.library.articles.edit', $article)->with('success', 'Статья создана.');
    }

    public function edit(LibraryArticle $article): View
    {
        $article->load('links');

        return $this->formView($article);
    }

    public function update(Request $request, LibraryArticle $article): RedirectResponse
    {
        $data = $this->validated($request, $article);

        DB::transaction(function () use ($article, $data, $request): void {
            $this->fill($article, $data, $request);
            $article->save();
            $this->syncLinks($article, $data['links'] ?? []);
        });

        return redirect()->back()->with('success', 'Статья сохранена.');
    }

    public function destroy(LibraryArticle $article): RedirectResponse
    {
        $article->delete();

        return redirect()->route('admin.library.articles.index')->with('success', 'Статья удалена.');
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $data = $request->validate(['file' => ['required', 'image', 'max:4096']]);

        return response()->json([
            'url' => asset($this->imageStorage->storeInPublicDirectory($data['file'], 'library/content')),
        ]);
    }

    private function formView(?LibraryArticle $article): View
    {
        $linkLabels = [];
        foreach ($article?->links ?? [] as $link) {
            $linkLabels[$link->id] = $this->entities->label($link->entity_type, (int) $link->entity_id);
        }

        return view('admin.library.articles.form', [
            'article' => $article,
            'categories' => LibraryCategory::query()->orderBy('name')->get(),
            'statuses' => LibraryArticle::STATUSES,
            'entityTypes' => $this->entities->options(),
            'linkLabels' => $linkLabels,
        ]);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, LibraryArticle $article): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:library_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('library_articles', 'slug')->ignore($article->id)],
            'excerpt' => ['nullable', 'string', 'max:2000'],
            'content' => ['required', 'string'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
            'status' => ['required', Rule::in(array_keys(LibraryArticle::STATUSES))],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'links' => ['nullable', 'array'],
            'links.*.entity_type' => ['nullable', 'string', Rule::in(array_keys($this->entities->options()))],
            'links.*.entity_id' => ['nullable', 'integer', 'min:1'],
            'links.*.custom_label' => ['nullable', 'string', 'max:255'],
        ]);

        $seenLinks = [];
        foreach ($data['links'] ?? [] as $index => $link) {
            if (empty($link['entity_type']) && empty($link['entity_id'])) {
                continue;
            }

            if (empty($link['entity_type']) || empty($link['entity_id'])) {
                throw ValidationException::withMessages(["links.$index.entity_id" => 'Выберите тип и укажите ID объекта.']);
            }

            if (! $this->entities->exists($link['entity_type'], (int) $link['entity_id'])) {
                throw ValidationException::withMessages(["links.$index.entity_id" => 'Игровой объект с таким ID не найден.']);
            }

            $key = $link['entity_type'].':'.(int) $link['entity_id'];
            if (isset($seenLinks[$key])) {
                throw ValidationException::withMessages(["links.$index.entity_id" => 'Этот игровой объект уже привязан к статье.']);
            }
            $seenLinks[$key] = true;
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    private function fill(LibraryArticle $article, array $data, Request $request): void
    {
        $status = $data['status'];
        $article->fill([
            'category_id' => (int) $data['category_id'],
            'title' => $data['title'],
            'slug' => $data['slug'] ?: $this->uniqueSlug($data['title'], $article),
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'],
            'status' => $status,
            'published_at' => $status === LibraryArticle::STATUS_PUBLISHED
                ? ($data['published_at'] ?? $article->published_at ?? now())
                : ($data['published_at'] ?? null),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        if ($request->hasFile('cover_image')) {
            $article->cover_image = $this->imageStorage->storeInPublicDirectory(
                $request->file('cover_image'),
                'library/covers',
            );
        }
    }

    /** @param array<int, array<string, mixed>> $links */
    private function syncLinks(LibraryArticle $article, array $links): void
    {
        $article->links()->delete();

        foreach (array_values($links) as $index => $link) {
            if (empty($link['entity_type']) || empty($link['entity_id'])) {
                continue;
            }

            $article->links()->create([
                'entity_type' => $link['entity_type'],
                'entity_id' => (int) $link['entity_id'],
                'custom_label' => $link['custom_label'] ?? null,
                'sort_order' => $index,
            ]);
        }
    }

    private function uniqueSlug(string $title, LibraryArticle $article): string
    {
        $base = Str::slug($title) ?: 'article';
        $slug = $base;
        $suffix = 2;

        while (LibraryArticle::withTrashed()->where('slug', $slug)->when($article->exists, fn ($query) => $query->where('id', '!=', $article->id))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
