<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Forum;

use App\Modules\Forum\Application\Services\ForumWriter;
use App\Modules\Forum\Domain\Services\ForumHtmlSanitizer;
use App\Modules\Forum\Infrastructure\Persistence\Models\ForumSection;
use App\Modules\Forum\Infrastructure\Persistence\Models\ForumTopic;
use App\Modules\Moderation\Application\Services\CommunicationMuteService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class ForumSectionPostingPermissionsTest extends TestCase
{
    private ForumWriter $writer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->writer = new ForumWriter(
            new ForumHtmlSanitizer,
            $this->createMock(CommunicationMuteService::class),
        );
    }

    public function test_regular_player_cannot_create_topic_in_restricted_section(): void
    {
        $section = new ForumSection(['allow_topics' => false, 'allow_comments' => true]);
        $user = (new User)->forceFill(['id' => 5, 'is_admin' => false]);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Создание тем в этом разделе отключено.');

        $this->writer->createTopic($user, $section, 'Новая тема', 'Текст сообщения');
    }

    public function test_regular_player_cannot_comment_in_restricted_section(): void
    {
        $section = new ForumSection(['allow_topics' => true, 'allow_comments' => false]);
        $topic = new ForumTopic;
        $topic->setRelation('section', $section);
        $user = (new User)->forceFill(['id' => 5, 'is_admin' => false]);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Комментарии в этом разделе отключены.');

        $this->writer->createPost($user, $topic, 'Новый комментарий');
    }

    public function test_administrator_bypasses_section_posting_restrictions(): void
    {
        $section = new ForumSection(['allow_topics' => false, 'allow_comments' => false]);
        $admin = (new User)->forceFill(['id' => 1, 'is_admin' => true]);

        self::assertTrue($this->writer->canCreateTopic($admin, $section));
        self::assertTrue($this->writer->canComment($admin, $section));
    }
}
