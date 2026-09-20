<?php

declare(strict_types=1);

namespace App\Modules\Forum\Application\Services;

use App\Modules\Forum\Infrastructure\Persistence\Models\ForumSection;
use App\Modules\Forum\Infrastructure\Persistence\Models\ForumTopic;
use App\Modules\Forum\Infrastructure\Persistence\Models\ForumTopicVote;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ForumService
{
    /**
     * @return Collection<int, ForumSection>
     */
    public function sectionsTree(): Collection
    {
        return ForumSection::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->withCount('topics')
            ->withSum('topics as posts_total', 'posts_count')
            ->with([
                'children' => fn ($query) => $query
                    ->withCount('topics')
                    ->withSum('topics as posts_total', 'posts_count')
                    ->with([
                        'latestTopic.author.player',
                        'latestTopic.author.clanMembership.clan',
                        'latestTopic.lastPostAuthor.player',
                        'latestTopic.lastPostAuthor.clanMembership.clan',
                    ]),
                'latestTopic.author.player',
                'latestTopic.author.clanMembership.clan',
                'latestTopic.lastPostAuthor.player',
                'latestTopic.lastPostAuthor.clanMembership.clan',
            ])
            ->get();
    }

    public function findSection(string $slug): ForumSection
    {
        return ForumSection::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->whereDoesntHave('children')
            ->with('parent')
            ->firstOrFail();
    }

    public function sectionTopics(ForumSection $section, int $perPage = 20): LengthAwarePaginator
    {
        return ForumTopic::query()
            ->where('section_id', $section->id)
            ->with([
                'section',
                'author.player',
                'author.clanMembership.clan',
                'lastPostAuthor.player',
            ])
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_post_at')
            ->paginate($perPage);
    }

    public function findTopic(int $id): ForumTopic
    {
        return ForumTopic::query()
            ->with(['section', 'posts' => fn ($query) => $query->orderBy('id')->limit(1)])
            ->findOrFail($id);
    }

    public function topicPosts(ForumTopic $topic, int $perPage = 15): LengthAwarePaginator
    {
        return $topic->posts()
            ->with(['author.player', 'author.clanMembership.clan'])
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function searchTopics(string $query, int $perPage = 20): LengthAwarePaginator
    {
        return ForumTopic::query()
            ->whereHas('section', fn ($sectionQuery) => $sectionQuery->where('is_active', true))
            ->where(function ($topicQuery) use ($query): void {
                $topicQuery
                    ->where('title', 'like', '%'.$query.'%')
                    ->orWhereHas('posts', fn ($postQuery) => $postQuery->where('body', 'like', '%'.$query.'%'));
            })
            ->with([
                'section',
                'author.player',
                'author.clanMembership.clan',
                'lastPostAuthor.player',
            ])
            ->orderByDesc('last_post_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function userVote(int $topicId, ?int $userId): ?int
    {
        if ($userId === null) {
            return null;
        }

        $value = ForumTopicVote::query()
            ->where('topic_id', $topicId)
            ->where('user_id', $userId)
            ->value('value');

        return $value === null ? null : (int) $value;
    }
}
