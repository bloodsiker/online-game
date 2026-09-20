<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Forum;

use App\Modules\Forum\Application\Services\ForumWriter;
use App\Modules\Forum\Domain\Services\ForumHtmlSanitizer;
use App\Modules\Forum\Infrastructure\Persistence\Models\ForumTopic;
use App\Modules\Moderation\Application\Services\CommunicationMuteService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ForumWriterVoteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('forum_topics', function (Blueprint $table): void {
            $table->id();
            $table->integer('rating')->default(0);
            $table->timestamps();
        });

        Schema::create('forum_topic_votes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('topic_id');
            $table->unsignedBigInteger('user_id');
            $table->smallInteger('value');
            $table->timestamps();
            $table->unique(['topic_id', 'user_id']);
        });
    }

    public function test_repeated_same_vote_does_not_cancel_it(): void
    {
        $topicId = DB::table('forum_topics')->insertGetId([
            'rating' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $topic = ForumTopic::query()->findOrFail($topicId);
        $user = new User;
        $user->id = 7;
        $writer = new ForumWriter(
            new ForumHtmlSanitizer,
            $this->createMock(CommunicationMuteService::class),
        );

        self::assertSame(1, $writer->vote($user, $topic, 1));
        self::assertSame(1, $writer->vote($user, $topic, 1));
        self::assertSame(1, DB::table('forum_topic_votes')->count());
        self::assertSame(1, (int) DB::table('forum_topics')->where('id', $topicId)->value('rating'));

        self::assertSame(-1, $writer->vote($user, $topic, -1));
        self::assertSame(1, DB::table('forum_topic_votes')->count());
        self::assertSame(-1, (int) DB::table('forum_topics')->where('id', $topicId)->value('rating'));
    }
}
