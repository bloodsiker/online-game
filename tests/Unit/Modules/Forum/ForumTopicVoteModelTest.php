<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Forum;

use App\Modules\Forum\Infrastructure\Persistence\Models\ForumTopicVote;
use PHPUnit\Framework\TestCase;

class ForumTopicVoteModelTest extends TestCase
{
    public function test_model_can_be_loaded(): void
    {
        $vote = new ForumTopicVote;

        self::assertSame('forum_topic_votes', $vote->getTable());
    }
}
