<?php

declare(strict_types=1);

namespace App\Modules\Forum\Application\Services;

use App\Modules\Forum\Domain\Services\ForumHtmlSanitizer;
use App\Modules\Forum\Infrastructure\Persistence\Models\ForumPost;
use App\Modules\Forum\Infrastructure\Persistence\Models\ForumSection;
use App\Modules\Forum\Infrastructure\Persistence\Models\ForumTopic;
use App\Modules\Forum\Infrastructure\Persistence\Models\ForumTopicVote;
use App\Modules\Moderation\Application\Services\CommunicationMuteService;
use App\Modules\Moderation\Domain\Enums\CommunicationScope;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class ForumWriter
{
    public function __construct(
        private readonly ForumHtmlSanitizer $sanitizer,
        private readonly CommunicationMuteService $communicationMuteService,
    ) {}

    public function createTopic(User $user, ForumSection $section, string $title, string $body): ForumTopic
    {
        abort_unless($this->canCreateTopic($user, $section), 403, 'Создание тем в этом разделе отключено.');
        $this->communicationMuteService->throwIfMuted($user, CommunicationScope::Forum);
        $body = $this->requireBody($body);

        return DB::transaction(function () use ($user, $section, $title, $body): ForumTopic {
            $topic = ForumTopic::query()->create([
                'section_id' => $section->id,
                'user_id' => $user->id,
                'player_id' => $user->player_id,
                'author_name' => $user->name,
                'title' => mb_substr(trim($title), 0, 150),
                'posts_count' => 1,
                'last_post_at' => now(),
                'last_post_user_id' => $user->id,
            ]);

            $topic->posts()->create([
                'user_id' => $user->id,
                'player_id' => $user->player_id,
                'author_name' => $user->name,
                'body' => $body,
            ]);

            return $topic;
        });
    }

    public function createPost(User $user, ForumTopic $topic, string $body): ForumPost
    {
        abort_unless($this->canComment($user, $topic->section), 403, 'Комментарии в этом разделе отключены.');
        $this->communicationMuteService->throwIfMuted($user, CommunicationScope::Forum);
        abort_if($topic->is_locked, 403, 'Тема закрыта.');
        $body = $this->requireBody($body);

        return DB::transaction(function () use ($user, $topic, $body): ForumPost {
            $locked = ForumTopic::query()->whereKey($topic->id)->lockForUpdate()->firstOrFail();
            abort_if($locked->is_locked, 403, 'Тема закрыта.');

            $post = $locked->posts()->create([
                'user_id' => $user->id,
                'player_id' => $user->player_id,
                'author_name' => $user->name,
                'body' => $body,
            ]);

            $locked->update([
                'posts_count' => $locked->posts()->count(),
                'last_post_at' => now(),
                'last_post_user_id' => $user->id,
            ]);

            return $post;
        });
    }

    public function updatePost(User $user, ForumPost $post, string $body): ForumPost
    {
        $this->communicationMuteService->throwIfMuted($user, CommunicationScope::Forum);
        abort_unless($this->canEdit($user, $post), 403);
        $post->update(['body' => $this->requireBody($body)]);

        return $post->fresh();
    }

    public function deletePost(User $user, ForumPost $post): ?ForumTopic
    {
        abort_unless($this->canModerate($user) || (int) $post->user_id === (int) $user->id, 403);

        return DB::transaction(function () use ($post): ?ForumTopic {
            $topic = ForumTopic::query()->whereKey($post->topic_id)->lockForUpdate()->firstOrFail();
            $post->delete();

            $remaining = $topic->posts()->count();
            if ($remaining === 0) {
                $topic->delete();

                return null;
            }

            $last = $topic->posts()->orderByDesc('id')->first();
            $topic->update([
                'posts_count' => $remaining,
                'last_post_at' => $last->created_at,
                'last_post_user_id' => $last->user_id,
            ]);

            return $topic->fresh();
        });
    }

    public function vote(User $user, ForumTopic $topic, int $value): int
    {
        abort_unless(in_array($value, [1, -1], true), 422);

        return DB::transaction(function () use ($user, $topic, $value): int {
            $lockedTopic = ForumTopic::query()->whereKey($topic->id)->lockForUpdate()->firstOrFail();
            $vote = ForumTopicVote::query()
                ->where('topic_id', $topic->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($vote === null) {
                ForumTopicVote::query()->create([
                    'topic_id' => $topic->id,
                    'user_id' => $user->id,
                    'value' => $value,
                ]);
            } elseif ((int) $vote->value !== $value) {
                $vote->update(['value' => $value]);
            }

            $rating = (int) ForumTopicVote::query()->where('topic_id', $topic->id)->sum('value');
            $lockedTopic->update(['rating' => $rating]);

            return $rating;
        });
    }

    public function setPinned(User $user, ForumTopic $topic, bool $pinned): void
    {
        abort_unless($this->canModerate($user), 403);
        $topic->update(['is_pinned' => $pinned]);
    }

    public function setLocked(User $user, ForumTopic $topic, bool $locked): void
    {
        abort_unless($this->canModerate($user), 403);
        $topic->update(['is_locked' => $locked]);
    }

    public function deleteTopic(User $user, ForumTopic $topic): void
    {
        abort_unless($this->canModerate($user), 403);
        $topic->delete();
    }

    public function canEdit(User $user, ForumPost $post): bool
    {
        return $this->canModerate($user) || (int) $post->user_id === (int) $user->id;
    }

    public function canModerate(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function canCreateTopic(User $user, ForumSection $section): bool
    {
        return $this->canModerate($user) || $section->allow_topics;
    }

    public function canComment(User $user, ForumSection $section): bool
    {
        return $this->canModerate($user) || $section->allow_comments;
    }

    private function requireBody(string $body): string
    {
        $clean = $this->sanitizer->clean($body);
        abort_if($this->sanitizer->textLength($clean) < 2, 422, 'Сообщение слишком короткое.');
        abort_if(mb_strlen($clean) > 20000, 422, 'Сообщение слишком длинное.');

        return $clean;
    }
}
