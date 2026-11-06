<?php

declare(strict_types=1);

namespace App\Modules\Library\Presentation\Services;

final class LibraryUrls
{
    /** @param array<string, mixed> $parameters */
    public function index(array $parameters = [], ?string $locale = null): string
    {
        return route('library.localized.index', ['locale' => $locale ?? app()->getLocale()] + $parameters);
    }

    /** @param array<string, mixed> $parameters */
    public function article(string $slug, array $parameters = [], ?string $locale = null): string
    {
        return route('library.localized.show', ['locale' => $locale ?? app()->getLocale(), 'slug' => $slug] + $parameters);
    }

    public function localizeContentLinks(string $html): string
    {
        // Keep editorial HTML intact; only rewrite same-site, unprefixed library links.
        return preg_replace_callback('~(<a\b[^>]*\bhref\s*=\s*)([\'"])(.*?)\2~is', function (array $match): string {
            $parts = parse_url(html_entity_decode($match[3], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($parts === false || isset($parts['scheme']) && ! in_array($parts['scheme'], ['http', 'https'], true)
                || isset($parts['host']) && $parts['host'] !== parse_url(url('/'), PHP_URL_HOST)
                || ! preg_match('~^/library(?:/([^/]+))?/?$~', $parts['path'] ?? '', $path)) {
                return $match[0];
            }

            parse_str($parts['query'] ?? '', $query);
            $url = isset($path[1])
                ? $this->article(slug: rawurldecode($path[1]), parameters: $query)
                : $this->index(parameters: $query);
            if (isset($parts['fragment'])) {
                $url .= '#'.$parts['fragment'];
            }

            return $match[1].$match[2].e($url).$match[2];
        }, $html) ?? $html;
    }
}
