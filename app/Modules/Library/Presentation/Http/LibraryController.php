<?php

declare(strict_types=1);

namespace App\Modules\Library\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Library\Application\Services\BestiaryCatalogService;
use App\Modules\Library\Domain\Enums\LibraryCategoryContentType;
use App\Modules\Library\Domain\Services\LibraryEntityRegistry;
use App\Modules\Library\Domain\Services\LibraryShortcodeRenderer;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryCategory;
use App\Modules\Location\Application\UseCases\GetMapsPage;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LibraryController extends Controller
{
    public function index(
        Request $request,
        BestiaryCatalogService $bestiary,
        GetMapsPage $mapsPage,
    ): View|RedirectResponse {
        $selectedCategory = null;
        if ($request->filled('category')) {
            $selectedCategory = LibraryCategory::query()->where('is_active', true)->where('slug', $request->input('category'))->firstOrFail();
        }

        $categories = $this->navigationCategories();

        if ($selectedCategory?->content_type === LibraryCategoryContentType::MONSTERS) {
            $filters = $request->validate([
                'q' => ['nullable', 'string', 'max:100'],
                'level_from' => ['nullable', 'integer', 'min:1', 'max:10000'],
                'level_to' => ['nullable', 'integer', 'min:1', 'max:10000', 'gte:level_from'],
                'map_id' => ['nullable', 'integer', 'exists:maps,id'],
                'location_id' => ['nullable', 'integer', 'exists:locations,id'],
                'boss' => ['nullable', Rule::in(['all', '0', '1'])],
            ]);

            return view('library::bestiary', [
                'categories' => $categories,
                'selectedCategory' => $selectedCategory,
                'filters' => $filters,
                ...$bestiary->get($filters),
            ]);
        }

        if ($selectedCategory?->content_type === LibraryCategoryContentType::MAPS) {
            $filters = $request->validate([
                'location_id' => ['nullable', 'integer', 'min:1'],
            ]);
            $user = $request->user();
            $currentMapId = $user?->loadMissing('currentLocation')->currentLocation?->map_id;
            $locationSearchId = $request->filled('location_id')
                ? (int) $filters['location_id']
                : null;
            $searchedLocation = $locationSearchId !== null
                ? Location::query()->with('map')->find($locationSearchId)
                : null;

            return view('library::maps', [
                'categories' => $categories,
                'selectedCategory' => $selectedCategory,
                'page' => $mapsPage->execute($currentMapId !== null ? (int) $currentMapId : null),
                'locationSearchId' => $locationSearchId,
                'searchedLocation' => $searchedLocation,
                'bestiaryCategory' => LibraryCategory::query()
                    ->where('is_active', true)
                    ->where('content_type', LibraryCategoryContentType::MONSTERS->value)
                    ->first(),
            ]);
        }

        $categoryIds = $selectedCategory
            ? $this->activeCategoryIds($selectedCategory)
            : [];

        $articlesQuery = LibraryArticle::query()
            ->published()
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->with('category')
            ->when($categoryIds !== [], fn ($query) => $query->whereIn('category_id', $categoryIds))
            ->when($request->filled('q'), fn ($query) => $query->where(function ($query) use ($request): void {
                $search = '%'.$request->input('q').'%';
                $query->where('title', 'like', $search)->orWhere('excerpt', 'like', $search)->orWhere('content', 'like', $search);
            }))
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->orderBy('id');

        if ($selectedCategory && ! $request->filled('q')) {
            $firstArticle = $articlesQuery->first();

            if ($firstArticle) {
                return redirect()->route('library.show', [
                    'slug' => $firstArticle->slug,
                    'category' => $selectedCategory->slug,
                ]);
            }
        }

        $articles = $articlesQuery
            ->paginate(20)
            ->withQueryString();

        return view('library::index', compact('categories', 'articles', 'selectedCategory'));
    }

    public function show(
        string $slug,
        Request $request,
        LibraryShortcodeRenderer $renderer,
        LibraryEntityRegistry $entities,
    ): View {
        $article = LibraryArticle::query()
            ->published()
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->with(['category', 'links'])
            ->where('slug', $slug)
            ->firstOrFail();
        $article->increment('views_count');
        $article->rendered_content = $renderer->render($article->content);
        $categories = $this->navigationCategories();
        $navigationCategory = $article->category;

        if ($request->filled('category')) {
            $requestedCategory = LibraryCategory::query()
                ->where('is_active', true)
                ->where('slug', $request->input('category'))
                ->first();

            if ($requestedCategory && in_array((int) $article->category_id, $this->activeCategoryIds($requestedCategory), true)) {
                $navigationCategory = $requestedCategory;
            }
        }

        $articleNavigation = LibraryArticle::query()
            ->published()
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->with('category')
            ->whereIn('category_id', $this->activeCategoryIds($navigationCategory))
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->orderBy('id')
            ->get();

        $linkedEntities = $article->links->map(fn ($link): array => [
            'type' => $entities->typeLabel($link->entity_type),
            'label' => $link->custom_label ?: ($entities->label($link->entity_type, (int) $link->entity_id) ?? '#'.$link->entity_id),
        ]);

        return view('library::show', [
            'article' => $article,
            'selectedCategory' => $navigationCategory,
            'linkedEntities' => $linkedEntities,
            'itemTooltipScript' => $renderer->tooltipScript(),
            'articleNavigation' => $articleNavigation,
            'categories' => $categories,
        ]);
    }

    /** @return Collection<int, LibraryCategory> */
    private function navigationCategories(): Collection
    {
        $categories = LibraryCategory::query()
            ->where('is_active', true)
            ->withCount(['articles' => fn ($query) => $query->published()])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->categoryTree($categories);
    }

    /**
     * @param  Collection<int, LibraryCategory>  $categories
     * @return Collection<int, LibraryCategory>
     */
    private function categoryTree(Collection $categories): Collection
    {
        $grouped = $categories->groupBy(fn (LibraryCategory $category): int => (int) ($category->parent_id ?? 0));

        $build = function (int $parentId) use (&$build, $grouped): Collection {
            $children = new Collection($grouped->get($parentId, collect())->values()->all());
            foreach ($children as $child) {
                $child->setRelation('children', $build((int) $child->id));
            }

            return $children;
        };

        return $build(0);
    }

    /** @return array<int, int> */
    private function activeCategoryIds(LibraryCategory $category): array
    {
        $ids = [(int) $category->id];
        $pending = [(int) $category->id];

        while ($pending !== []) {
            $children = LibraryCategory::query()
                ->where('is_active', true)
                ->whereIn('parent_id', $pending)
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all();
            $ids = [...$ids, ...$children];
            $pending = $children;
        }

        return array_values(array_unique($ids));
    }
}
