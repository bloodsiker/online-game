<?php

declare(strict_types=1);

namespace App\Modules\Library\Presentation\Services;

use App\Modules\Library\Domain\Enums\LibraryCategoryContentType;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryCategory;
use App\Modules\Library\Presentation\DTOs\LibrarySeoData;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final readonly class LibrarySeo
{
    public function __construct(private LibraryUrls $urls) {}

    /** @return list<string> */
    public function articleLocales(LibraryArticle $article): array
    {
        return array_values(array_filter(['ru', 'uk'], fn (string $locale): bool => trim($article->getTranslation('title', $locale, false)) !== ''
            && trim($article->getTranslation('content', $locale, false)) !== ''
        ));
    }

    public function article(LibraryArticle $article): LibrarySeoData
    {
        $alternates = [];
        foreach ($this->articleLocales($article) as $locale) {
            $alternates[$locale] = $this->urls->article(slug: $article->slug, locale: $locale, categorySlug: $article->category->slug);
        }
        $description = $this->plainText($article->getTranslation('excerpt', app()->getLocale(), false));
        if ($description === '') {
            $description = $this->plainText($article->content);
        }

        return new LibrarySeoData(
            heading: $article->title,
            title: $article->title.' — '.__('library.name').' — '.__('library.seo_brand'),
            description: $this->withGame($description !== '' ? $description : __('library.article_description', ['title' => $article->title])),
            canonical: $this->urls->article(slug: $article->slug, categorySlug: $article->category->slug),
            alternates: $alternates,
            image: asset($article->cover_image ?: ($article->category?->image ?: 'data/canvas/ui/library.webp')),
            type: 'article',
            publishedAt: $article->published_at?->toAtomString(),
            modifiedAt: $article->updated_at?->toAtomString(),
        );
    }

    public function index(Request $request, ?LibraryCategory $category): LibrarySeoData
    {
        $parameters = $category ? ['category' => $category->slug] : [];
        $filtered = false;
        foreach (['q', 'level_from', 'level_to', 'map_id', 'location_id', 'boss'] as $filter) {
            if ($request->filled($filter) && is_scalar($request->query($filter))
                && ! ($filter === 'boss' && $request->query($filter) === 'all')) {
                $parameters[$filter] = (string) $request->query($filter);
                $filtered = true;
            }
        }
        $paginated = $request->filled('q') || $category?->content_type === LibraryCategoryContentType::MONSTERS;
        $page = $paginated ? filter_var($request->query('page'), FILTER_VALIDATE_INT) : false;
        if ($page !== false && $page > 1) {
            $parameters['page'] = $page;
        }

        $heading = $request->filled('q') ? __('library.search') : ($category?->name ?? __('library.name'));
        $title = $heading.' — '.($category || $request->filled('q') ? __('library.name').' — ' : '').__('library.seo_brand');
        if ($page !== false && $page > 1) {
            $title .= ' — '.__('library.page', ['number' => $page]);
        }
        $alternates = [];
        foreach (['ru', 'uk'] as $locale) {
            $alternates[$locale] = $this->urls->index(parameters: $parameters, locale: $locale);
        }

        return new LibrarySeoData(
            heading: $heading,
            title: $title,
            description: $this->withGame($this->plainText($category?->description ?? '') ?: ($category
                ? __('library.category_description', ['category' => $category->name])
                : __('library.description'))),
            canonical: $this->urls->index(parameters: $parameters),
            alternates: $alternates,
            image: asset($category?->image ?: 'data/canvas/ui/library.webp'),
            robots: $filtered ? 'noindex, follow' : 'index, follow',
        );
    }

    /**
     * Описание с названием игры, чтобы поисковик понимал, к какой игре относится страница.
     * Итог — до 160 символов.
     */
    public function withGame(string $description): string
    {
        $description = Str::squish($description);
        if (Str::contains($description, ['«'.__('library.brand').'»', 'Eternum'], ignoreCase: true)) {
            return Str::limit($description, 157, '…');
        }
        $suffix = __('library.seo_suffix');
        $room = 160 - mb_strlen($suffix) - 1;

        if ($description === '') {
            return $suffix;
        }
        $body = Str::limit($description, $room - 1, '…');
        $body = str_ends_with($body, '…') ? $body : rtrim($body, '.!?;:,').'.';

        return $body.' '.$suffix;
    }

    public function plainText(string $html): string
    {
        $text = preg_replace('~<(script|style)\b[^>]*>.*?</\1>~is', ' ', $html) ?? $html;
        $text = preg_replace('/\[\[.*?\]\]/s', ' ', $text) ?? $text;
        $text = preg_replace('~</(?:p|div|li|h[1-6]|tr)>|<br\s*/?>~i', ' ', $text) ?? $text;
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return Str::limit(Str::squish($text), 170);
    }
}
