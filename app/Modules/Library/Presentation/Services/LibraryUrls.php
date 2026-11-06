<?php

declare(strict_types=1);

namespace App\Modules\Library\Presentation\Services;

use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;

final class LibraryUrls
{
    /** @var array<string, ?string> */
    private array $articleCategories = [];

    /** @param array<string, mixed> $parameters */
    public function index(array $parameters = [], ?string $locale = null): string
    {
        return route('library.localized.index', ['locale' => $locale ?? app()->getLocale()] + $parameters);
    }

    /** @param array<string, mixed> $parameters */
    public function article(string $slug, array $parameters = [], ?string $locale = null, ?string $categorySlug = null): string
    {
        if ($categorySlug !== null) {
            $this->articleCategories[$slug] = $categorySlug;
        } elseif (! array_key_exists($slug, $this->articleCategories)) {
            $this->articleCategories[$slug] = LibraryArticle::query()->with('category:id,slug')
                ->where('slug', $slug)->first(['id', 'category_id'])?->category?->slug;
        }
        $categorySlug = $this->articleCategories[$slug];
        unset($parameters['category']);

        // Unknown editorial links keep a resolvable legacy URL and correctly return 404 there.
        return $categorySlug === null
            ? route('library.localized.legacy.show', ['locale' => $locale ?? app()->getLocale(), 'slug' => $slug] + $parameters)
            : route('library.localized.show', ['locale' => $locale ?? app()->getLocale(), 'category' => $categorySlug, 'slug' => $slug] + $parameters);
    }

    public function localizeContentLinks(string $html): string
    {
        // Keep editorial HTML intact while normalizing old and current same-site library links.
        return preg_replace_callback('~(<a\b[^>]*\bhref\s*=\s*)([\'"])(.*?)\2~is', function (array $match): string {
            $parts = parse_url(html_entity_decode($match[3], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($parts === false || isset($parts['scheme']) && ! in_array($parts['scheme'], ['http', 'https'], true)
                || isset($parts['host']) && $parts['host'] !== parse_url(url('/'), PHP_URL_HOST)
                || ! preg_match('~^/(?:ru/|uk/)?library(?:/([^/]+))?(?:/([^/]+))?/?$~', $parts['path'] ?? '', $path)) {
                return $match[0];
            }

            parse_str($parts['query'] ?? '', $query);
            $url = isset($path[1])
                ? $this->article(slug: rawurldecode($path[2] ?? $path[1]), parameters: $query)
                : $this->index(parameters: $query);
            if (isset($parts['fragment'])) {
                $url .= '#'.$parts['fragment'];
            }

            return $match[1].$match[2].e($url).$match[2];
        }, $html) ?? $html;
    }
}
