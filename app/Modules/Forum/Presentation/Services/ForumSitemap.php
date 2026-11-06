<?php

declare(strict_types=1);

namespace App\Modules\Forum\Presentation\Services;

use App\Modules\Forum\Application\Services\ForumService;
use App\Modules\Forum\Infrastructure\Persistence\Models\ForumSection;
use App\Modules\Forum\Infrastructure\Persistence\Models\ForumTopic;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;

final class ForumSitemap
{
    /** @return list<array{url: string, lastmod: ?string}> */
    public function entries(): array
    {
        $entries = [['url' => route('forum.index'), 'lastmod' => null]];
        $sections = ForumSection::query()->select(['id', 'parent_id', 'slug', 'is_active', 'updated_at'])
            ->withCount('topics')->withMax('topics', 'updated_at')->orderBy('id')->get()->keyBy('id');
        $visible = $sections->filter(fn (ForumSection $section): bool => $this->isVisible($section, $sections));
        if ($visible->isEmpty()) {
            return $entries;
        }
        $parents = $visible->pluck('parent_id')->filter()->map(fn ($id): int => (int) $id)->all();
        foreach ($visible as $section) {
            // Parent headings with active children have no public section page.
            if (in_array((int) $section->id, $parents, true)) {
                continue;
            }
            $lastmod = $this->latestTimestamp($section->updated_at, $section->topics_max_updated_at);
            $pages = max(1, (int) ceil($section->topics_count / ForumService::TOPICS_PER_PAGE));
            foreach (range(1, $pages) as $page) {
                $entries[] = [
                    'url' => route('forum.section', ['slug' => $section->slug] + ($page > 1 ? ['page' => $page] : [])),
                    'lastmod' => $lastmod,
                ];
            }
        }

        $topics = ForumTopic::query()->select(['id', 'section_id', 'created_at', 'updated_at', 'last_post_at'])
            ->whereIn('section_id', $visible->modelKeys())
            ->withCount('posts')->withMax('posts', 'updated_at')->orderBy('id')->get();
        foreach ($topics as $topic) {
            $lastmod = $this->latestTimestamp($topic->created_at, $topic->updated_at, $topic->last_post_at, $topic->posts_max_updated_at);
            $pages = max(1, (int) ceil($topic->posts_count / ForumService::POSTS_PER_PAGE));
            foreach (range(1, $pages) as $page) {
                $entries[] = [
                    'url' => route('forum.topic', ['id' => $topic->id] + ($page > 1 ? ['page' => $page] : [])),
                    'lastmod' => $lastmod,
                ];
            }
        }
        $entries[0]['lastmod'] = $this->latestTimestamp(...array_column($entries, 'lastmod'));

        return $entries;
    }

    /** @param Collection<int, ForumSection> $sections */
    private function isVisible(ForumSection $section, Collection $sections): bool
    {
        $visited = [];
        while (true) {
            if (! $section->is_active || isset($visited[$section->id])) {
                return false;
            }
            $visited[$section->id] = true;
            if ($section->parent_id === null) {
                return true;
            }
            $section = $sections->get($section->parent_id);
            if ($section === null) {
                return false;
            }
        }
    }

    private function latestTimestamp(DateTimeInterface|string|null ...$timestamps): ?string
    {
        $latest = null;
        foreach ($timestamps as $timestamp) {
            if ($timestamp === null) {
                continue;
            }
            $date = CarbonImmutable::parse($timestamp);
            if ($latest === null || $date->greaterThan($latest)) {
                $latest = $date;
            }
        }

        return $latest?->toAtomString();
    }
}
