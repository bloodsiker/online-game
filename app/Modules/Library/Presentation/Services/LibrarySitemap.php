<?php

declare(strict_types=1);

namespace App\Modules\Library\Presentation\Services;

use App\Modules\Library\Domain\Enums\LibraryCategoryContentType;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryCategory;

final readonly class LibrarySitemap
{
    public function __construct(private LibraryUrls $urls, private LibrarySeo $seo) {}

    /** @return list<array{url: string, lastmod: ?string}> */
    public function entries(): array
    {
        $entries = [];
        foreach (['ru', 'uk'] as $locale) {
            $entries[] = ['url' => $this->urls->index(locale: $locale), 'lastmod' => null];
        }

        // Ordinary categories currently redirect to their first article; only list actual pages.
        foreach (LibraryCategory::query()->where('is_active', true)
            ->whereIn('content_type', [LibraryCategoryContentType::MONSTERS->value, LibraryCategoryContentType::MAPS->value])
            ->orderBy('id')->get() as $category) {
            foreach (['ru', 'uk'] as $locale) {
                $entries[] = ['url' => $this->urls->index(parameters: ['category' => $category->slug], locale: $locale), 'lastmod' => null];
            }
        }

        foreach (LibraryArticle::query()->published()
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->with('category:id,slug')
            ->orderBy('id')->get(['id', 'category_id', 'slug', 'title', 'content', 'updated_at', 'published_at']) as $article) {
            foreach ($this->seo->articleLocales($article) as $locale) {
                $entries[] = [
                    'url' => $this->urls->article(slug: $article->slug, locale: $locale, categorySlug: $article->category->slug),
                    'lastmod' => $article->updated_at?->toAtomString(),
                ];
            }
        }

        return $entries;
    }
}
