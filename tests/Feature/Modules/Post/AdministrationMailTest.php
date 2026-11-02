<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Post;

use App\Modules\Post\Application\Services\AdministrationMailService;
use App\Modules\Post\Domain\Events\MailboxUnreadStateUpdated;
use App\Modules\Post\Infrastructure\Persistence\Models\PostLetter;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class AdministrationMailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
            $this->markTestSkipped('Только SQLite :memory:, игровая база не используется.');
        }
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('money')->default(0);
            $table->boolean('is_admin')->default(false);
            $table->string('active_login_token')->default('test-login');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });
        Schema::create('share_items', fn (Blueprint $table) => $table->id());
        Schema::create('user_game_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->json('settings');
            $table->timestamps();
        });
        (require database_path('migrations/2026_07_10_000001_create_post_letters_table.php'))->up();
        (require database_path('migrations/2026_10_21_000005_add_administration_mail_to_post_letters.php'))->up();
        foreach ([1, 2, 3] as $id) {
            DB::table('users')->insert(['id' => $id, 'name' => 'MailUser'.$id, 'is_admin' => $id === 3, 'email_verified_at' => now()]);
        }
        Event::fake([MailboxUnreadStateUpdated::class]);
    }

    private function user(int $id): User
    {
        return User::query()->without('player')->findOrFail($id);
    }

    private function loginAs(int $id): static
    {
        return $this->actingAs($this->user($id))
            ->withSession(['active_login_token' => 'test-login', '_token' => 'post-test-csrf'])
            ->withHeaders(['X-CSRF-TOKEN' => 'post-test-csrf']);
    }

    public function test_request_is_shared_free_and_cannot_spoof_recipient_or_attachments(): void
    {
        $this->loginAs(1)->post(route('post.administration.send'), [
            'subject' => 'Помощь', 'text' => 'Проверка', 'recipient_user_id' => 2,
            'money' => 100, 'share_item_id' => 999, 'administration_sender_id' => 3,
        ])->assertRedirect();
        $letter = PostLetter::query()->sole();
        $this->assertTrue($letter->is_to_administration);
        $this->assertNull($letter->recipient_user_id);
        $this->assertNull($letter->administration_sender_id);
        $this->assertNull($letter->share_item_id);
        $this->assertSame(0, $letter->money);
        $this->assertSame('pending', $letter->administration_status);
        $this->assertEquals(0, DB::table('users')->where('id', 1)->value('money'));
    }

    public function test_reply_arrives_in_mailbox_and_followup_reopens_thread(): void
    {
        $service = app(AdministrationMailService::class);
        $thread = $service->send(player: $this->user(1), subject: 'Помощь', text: 'Вопрос');
        $reply = $service->reply(admin: $this->user(3), threadId: $thread->id, text: 'Ответ');
        $this->assertTrue($reply->isAdministrationReply());
        $this->assertNull($reply->sender_user_id);
        $this->assertEquals(1, $reply->recipient_user_id);
        $this->assertEquals(3, $reply->administration_sender_id);
        $this->assertSame('answered', $thread->fresh()->administration_status);
        Event::assertDispatched(MailboxUnreadStateUpdated::class, fn ($event) => $event->userId === 1 && $event->hasUnread);
        $service->send(player: $this->user(1), subject: 'Подмена', text: 'Уточнение', threadId: $thread->id);
        $this->assertSame('pending', $thread->fresh()->administration_status);
        $this->assertSame('Помощь', PostLetter::query()->latest('id')->first()->subject);
        $this->assertSame(3, $service->messages($thread)->count());
        $this->assertSame(1, $service->threads()->count());
    }

    public function test_other_player_cannot_read_or_continue_thread(): void
    {
        $thread = app(AdministrationMailService::class)->send($this->user(1), 'Секрет', 'Личная информация');
        $this->loginAs(2)->get(route('post', ['mode' => 'administration', 'reply_to' => $thread->id]))->assertNotFound();
        $this->post(route('post.administration.send'), ['thread_id' => $thread->id, 'subject' => 'Взлом', 'text' => 'Взлом'])->assertNotFound();
        $this->assertSame(1, PostLetter::count());
    }

    public function test_non_admin_cannot_reply_and_admin_route_is_protected(): void
    {
        $thread = app(AdministrationMailService::class)->send($this->user(1), 'Тема', 'Текст');
        $this->loginAs(2)->post(route('admin.post.administration.reply', $thread->id), ['text' => 'Подмена'])->assertRedirect();
        $this->assertSame(1, PostLetter::count());
        $this->expectException(HttpException::class);
        app(AdministrationMailService::class)->reply($this->user(2), $thread->id, 'Подмена');
    }

    public function test_player_views_render_and_escape_messages(): void
    {
        $thread = app(AdministrationMailService::class)->send($this->user(1), 'Тема', '<script>bad()</script>');
        $this->loginAs(1)->get(route('post', ['mode' => 'administration']))->assertOk()->assertSee('Написать Администрации')->assertSee('Тема');
        $this->get(route('post', ['mode' => 'administration', 'reply_to' => $thread->id]))
            ->assertOk()->assertSee('&lt;script&gt;bad()&lt;/script&gt;', false)->assertDontSee('<script>bad()</script>', false);
        $reply = app(AdministrationMailService::class)->reply($this->user(3), $thread->id, 'Ответ поддержки');
        $this->get(route('post.letter', $reply->id))->assertOk()->assertSee('Администрация')->assertSee('Ответ поддержки')->assertDontSee('MailUser3');
    }

    public function test_admin_can_answer_through_controller(): void
    {
        $thread = app(AdministrationMailService::class)->send($this->user(1), 'Тема', 'Текст');
        $this->loginAs(3)->post(route('admin.post.administration.reply', $thread->id), ['text' => 'Готово'])
            ->assertRedirect(route('admin.post.administration.show', $thread->id));
        $this->assertSame('answered', $thread->fresh()->administration_status);
        $this->assertSame(2, PostLetter::count());
        $this->get(route('admin.post.administration.index'))->assertOk()->assertSee('Тема');
        $this->get(route('admin.post.administration.show', $thread->id))->assertOk()->assertSee('Готово')->assertSee('MailUser3');
    }
}
