<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Modules\Auth\Application\Listeners\ActivateLoginSession;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class ChatPlayerMessagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        Schema::create('clans', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        Schema::create('user_game_settings', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id');
        });
        Schema::create('chat_messages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('target_user_id')->nullable();
            $table->unsignedBigInteger('clan_id')->nullable();
            $table->string('channel');
            $table->string('type');
            $table->text('message');
            $table->timestamps();
        });
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Анри'],
            ['id' => 2, 'name' => 'Веста'],
        ]);
        DB::table('clans')->insert(['id' => 3, 'name' => 'Лесная стража']);
    }

    public function test_admin_sees_player_messages_with_labels_and_pagination(): void
    {
        $this->signIn(isAdmin: true);

        DB::table('chat_messages')->insert(array_map(fn (int $number) => [
            'user_id' => 1, 'channel' => 'main', 'type' => 'message', 'message' => 'Общее сообщение №'.$number,
        ], range(1, 50)));
        DB::table('chat_messages')->insert([
            'user_id' => 1, 'channel' => 'main', 'type' => 'mention', 'message' => 'to[Веста] Привет!',
        ]);
        DB::table('chat_messages')->insert([
            'user_id' => 1, 'channel' => 'clan', 'type' => 'message', 'clan_id' => 3, 'message' => 'Клановый разговор',
        ]);
        DB::table('chat_messages')->insert([
            'user_id' => 1, 'channel' => 'private', 'type' => 'private', 'target_user_id' => 2, 'message' => '<b>Приватный разговор</b>',
        ]);
        DB::table('chat_messages')->insert([
            'channel' => 'main', 'type' => 'information', 'target_user_id' => 2, 'message' => 'Служебное уведомление',
        ]);

        $this->get(route('admin.chat.messages'))->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertSeeInOrder(['Приватный разговор', 'Клановый разговор', 'to[Веста] Привет!', 'Общее сообщение №50'])
            ->assertSee('>Общий</span>', false)
            ->assertSee('>Клан</span>', false)
            ->assertSee('>Личное</span>', false)
            ->assertSee('>Приват</span>', false)
            ->assertSee('Лесная стража')
            ->assertSee('<td>Веста</td>', false)
            ->assertSee('&lt;b&gt;Приватный разговор&lt;/b&gt;', false)
            ->assertDontSee('Служебное уведомление')
            ->assertDontSee('Общее сообщение №1<', false)
            ->assertSee(route('admin.chat.messages', ['page' => 2]), false);

        $this->get(route('admin.chat.messages', ['page' => 2]))->assertOk()
            ->assertSee('Общее сообщение №1<', false)
            ->assertDontSee('Приватный разговор');
    }

    public function test_player_without_admin_rights_cannot_open_chat_history(): void
    {
        $this->signIn(isAdmin: false);

        $this->get(route('admin.chat.messages'))->assertRedirect(url('/'));
    }

    private function signIn(bool $isAdmin): void
    {
        $user = (new User)->forceFill([
            'id' => 1,
            'is_admin' => $isAdmin,
            'email_verified_at' => now(),
            'active_login_token' => 'test-token',
        ]);

        $this->actingAs($user)->withSession([ActivateLoginSession::SESSION_KEY => 'test-token']);
    }
}
