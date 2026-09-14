<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LibraryCategory;
use App\Services\Media\AdminImageStorage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LibraryCategoryController extends Controller
{
    private readonly AdminImageStorage $imageStorage;

    public function __construct(?AdminImageStorage $imageStorage = null)
    {
        $this->imageStorage = $imageStorage ?? new AdminImageStorage;
    }

    public function index(): View
    {
        $categories = LibraryCategory::query()
            ->with(['parent'])
            ->withCount(['children', 'articles'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.library.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.library.categories.form', [
            'category' => null,
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $category = new LibraryCategory;
        $this->fill($category, $request);
        $category->save();

        return redirect()->route('admin.library.categories.index')->with('success', 'Категория создана.');
    }

    public function edit(LibraryCategory $category): View
    {
        return view('admin.library.categories.form', [
            'category' => $category,
            'categories' => $this->categoryOptions($category),
        ]);
    }

    public function update(Request $request, LibraryCategory $category): RedirectResponse
    {
        $this->fill($category, $request);
        $category->save();

        return redirect()->route('admin.library.categories.index')->with('success', 'Категория сохранена.');
    }

    public function destroy(LibraryCategory $category): RedirectResponse
    {
        if ($category->children()->exists() || $category->articles()->exists()) {
            return redirect()->back()->with('error', 'Нельзя удалить категорию с подразделами или статьями.');
        }

        // Мягко удалённые статьи не видны через articles()->exists() (SoftDeletes-скоуп),
        // но физически всё ещё ссылаются на category_id — без этого DELETE падает
        // с ограничением внешнего ключа library_articles_category_id_foreign.
        $category->articles()->onlyTrashed()->get()->each->forceDelete();

        $category->delete();

        return redirect()->route('admin.library.categories.index')->with('success', 'Категория удалена.');
    }

    private function fill(LibraryCategory $category, Request $request): void
    {
        $data = $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:library_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('library_categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:4096'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $parentId = isset($data['parent_id']) ? (int) $data['parent_id'] : null;
        if ($category->exists && $parentId !== null && in_array($parentId, $this->descendantIds($category), true)) {
            throw ValidationException::withMessages([
                'parent_id' => 'Категорию нельзя вложить саму в себя или в её подраздел.',
            ]);
        }

        $category->fill([
            'parent_id' => $parentId,
            'name' => $data['name'],
            'slug' => $data['slug'] ?: $this->uniqueSlug($data['name'], $category),
            'description' => $data['description'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->hasFile('image')) {
            $category->image = $this->imageStorage->storeInPublicDirectory(
                $request->file('image'),
                'library/categories',
            );
        }
    }

    private function uniqueSlug(string $name, LibraryCategory $category): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $suffix = 2;

        while (LibraryCategory::query()->where('slug', $slug)->when($category->exists, fn ($query) => $query->where('id', '!=', $category->id))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /** @return Collection<int, LibraryCategory> */
    private function categoryOptions(?LibraryCategory $excluded = null)
    {
        $excludedIds = $excluded ? $this->descendantIds($excluded) : [];

        return LibraryCategory::query()
            ->when($excludedIds !== [], fn ($query) => $query->whereNotIn('id', $excludedIds))
            ->orderBy('name')
            ->get();
    }

    /** @return array<int, int> */
    private function descendantIds(LibraryCategory $category): array
    {
        $ids = [$category->id];
        $pending = [$category->id];

        while ($pending !== []) {
            $children = LibraryCategory::query()->whereIn('parent_id', $pending)->pluck('id')->map(fn ($id): int => (int) $id)->all();
            $ids = [...$ids, ...$children];
            $pending = $children;
        }

        return array_values(array_unique($ids));
    }
}
