<?php

declare(strict_types=1);

namespace App\Modules\Forum\Presentation\Services;

use App\Modules\Forum\Infrastructure\Persistence\Models\ForumSection;
use App\Modules\Forum\Infrastructure\Persistence\Models\ForumTopic;
use App\Modules\Library\Presentation\DTOs\LibrarySeoData;
use App\Modules\Library\Presentation\Services\LibrarySeo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * SEO форума: в title и description всегда есть название игры.
 * Форум одноязычный, поэтому без hreflang-альтернатив.
 */
final readonly class ForumSeo
{
    private const IMAGE = 'img/og-image.jpg';

    public function __construct(private LibrarySeo $library) {}

    public function index(): LibrarySeoData
    {
        return $this->make(
            heading: __('library.forum_name'),
            title: __('library.forum_name').' — '.__('library.seo_brand'),
            description: __('library.forum_description'),
            canonical: route('forum.index'),
        );
    }

    public function section(ForumSection $section, LengthAwarePaginator $topics): LibrarySeoData
    {
        $description = $this->library->plainText((string) $section->description);

        return $this->make(
            heading: $section->name,
            title: $section->name.$this->page($topics).' — '.__('library.forum_name').' — '.__('library.seo_brand'),
            description: $description !== ''
                ? $this->library->withGame($description)
                : __('library.forum_section_description', ['section' => $section->name]),
            canonical: route('forum.section', ['slug' => $section->slug] + $this->pageQuery($topics)),
        );
    }

    public function topic(ForumTopic $topic, LengthAwarePaginator $posts): LibrarySeoData
    {
        $firstPost = $topic->posts->first();
        $text = $this->library->plainText((string) $firstPost?->body);

        return $this->make(
            heading: $topic->title,
            title: $topic->title.$this->page($posts).' — '.__('library.forum_name').' — '.__('library.seo_brand'),
            description: $this->library->withGame($text !== '' ? $text : $topic->title.' — '.$topic->section->name),
            canonical: route('forum.topic', ['id' => $topic->id] + $this->pageQuery($posts)),
            type: 'article',
            publishedAt: $topic->created_at?->toAtomString(),
            modifiedAt: ($topic->last_post_at ?? $topic->updated_at)?->toAtomString(),
        );
    }

    public function search(string $query): LibrarySeoData
    {
        return $this->make(
            heading: __('library.forum_search'),
            title: __('library.forum_search').': '.$query.' — '.__('library.seo_brand'),
            description: __('library.forum_description'),
            canonical: route('forum.index'),
            robots: 'noindex, follow',
        );
    }

    private function make(
        string $heading,
        string $title,
        string $description,
        string $canonical,
        string $type = 'website',
        string $robots = 'index, follow',
        ?string $publishedAt = null,
        ?string $modifiedAt = null,
    ): LibrarySeoData {
        return new LibrarySeoData(
            heading: $heading,
            title: $title,
            description: $description,
            canonical: $canonical,
            alternates: [],
            image: asset(self::IMAGE),
            type: $type,
            robots: $robots,
            publishedAt: $publishedAt,
            modifiedAt: $modifiedAt,
        );
    }

    private function page(LengthAwarePaginator $paginator): string
    {
        return $paginator->currentPage() > 1 ? ' — '.__('library.page', ['number' => $paginator->currentPage()]) : '';
    }

    /** @return array<string, int> */
    private function pageQuery(LengthAwarePaginator $paginator): array
    {
        return $paginator->currentPage() > 1 ? ['page' => $paginator->currentPage()] : [];
    }
}
