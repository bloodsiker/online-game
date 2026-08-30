<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Post;

use App\Modules\Post\Application\Services\BroadcastMailboxUnreadState;
use App\Modules\Post\Application\UseCases\GetMailbox;
use App\Modules\Post\Application\UseCases\ReadLetter;
use App\Modules\Post\Application\UseCases\SendLetter;
use App\Modules\Post\Domain\Events\MailboxUnreadStateUpdated;
use App\Modules\Post\Infrastructure\Persistence\Models\PostLetter;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MailboxUnreadBroadcastTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_admin')->default(false);
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->unsignedInteger('money')->default(0);
            $table->unsignedInteger('warehouse_count')->default(50);
            $table->unsignedInteger('bag_count')->default(25);
            $table->unsignedInteger('slot_count')->default(3);
            $table->timestamps();
        });
        Schema::create('post_letters', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('sender_user_id')->nullable();
            $table->unsignedBigInteger('recipient_user_id');
            $table->string('subject');
            $table->text('text');
            $table->unsignedInteger('money')->default(0);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('recipient_deleted_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_unread_state_is_broadcast_when_letter_arrives_and_is_read(): void
    {
        $sender = $this->createUser('Sender', 10);
        $recipient = $this->createUser('Recipient');
        $mailbox = new GetMailbox;
        $unreadState = new BroadcastMailboxUnreadState($mailbox);

        Event::fake([MailboxUnreadStateUpdated::class]);

        $result = (new SendLetter($mailbox, $unreadState))->execute(
            $sender,
            $recipient->name,
            'Тема',
            'Текст письма',
            0,
        );

        $this->assertTrue($result->ok);
        Event::assertDispatched(
            MailboxUnreadStateUpdated::class,
            fn (MailboxUnreadStateUpdated $event): bool => $event->userId === $recipient->id && $event->hasUnread,
        );

        Event::fake([MailboxUnreadStateUpdated::class]);
        $letter = PostLetter::query()->firstOrFail();
        (new ReadLetter($unreadState))->execute($recipient, $letter->id);

        Event::assertDispatched(
            MailboxUnreadStateUpdated::class,
            fn (MailboxUnreadStateUpdated $event): bool => $event->userId === $recipient->id && ! $event->hasUnread,
        );
    }

    private function createUser(string $name, int $money = 0): User
    {
        $user = (new User)->forceFill([
            'name' => $name,
            'email' => strtolower($name).'@example.test',
            'password' => Hash::make('secret'),
            'money' => $money,
        ]);
        $user->save();

        return $user;
    }
}
