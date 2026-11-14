<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Gift;

use App\Modules\Chat\Application\UseCases\SendSystemMessage;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Gift\Application\DTOs\SendGiftResult;
use App\Modules\Gift\Application\ItemTooltip\GiftTooltipStrategy;
use App\Modules\Gift\Application\UseCases\GetPlayerGiftsBlock;
use App\Modules\Gift\Application\UseCases\SendGift;
use App\Modules\Gift\Infrastructure\Persistence\Models\PlayerGift;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Domain\Services\ItemActionLogger;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

final class SendGiftTest extends TestCase
{
    /** @var list<array{user: int, key: string, params: array}> */
    private array $sentMessages = [];

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        $this->createTables();

        $this->instance(ItemActionLogger::class, Mockery::mock(ItemActionLogger::class)->shouldIgnoreMissing());
        $messages = Mockery::mock(SendSystemMessage::class);
        $messages->shouldReceive('toUser')->andReturnUsing(function (User $user, $message): ChatMessage {
            $this->sentMessages[] = ['user' => (int) $user->id, 'key' => $message->key, 'params' => $message->params];

            return new ChatMessage;
        });
        $this->instance(SendSystemMessage::class, $messages);

        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Даритель'],
            ['id' => 2, 'name' => 'Получатель'],
        ]);
        DB::table('share_items')->insert([
            ['id' => 10, 'type' => 'gift', 'name' => '{"ru":"Хрустальная роза","uk":"Кришталева троянда"}', 'image' => 'items/gifts/crystal-rose.gif', 'is_active' => 1],
            ['id' => 11, 'type' => 'resource', 'name' => '{"ru":"Руда","uk":"Руда"}', 'image' => null, 'is_active' => 1],
        ]);
        DB::table('items')->insert([['id' => 100, 'share_item_id' => 10], ['id' => 101, 'share_item_id' => 11]]);
        DB::table('backpacks')->insert([
            ['id' => 1000, 'user_id' => 1, 'item_id' => 100, 'count' => 2, 'equipped' => 0],
            ['id' => 1001, 'user_id' => 1, 'item_id' => 101, 'count' => 1, 'equipped' => 0],
        ]);
    }

    public function test_gift_is_taken_from_backpack_saved_for_recipient_and_announced(): void
    {
        $result = $this->send(backpackId: 1000, recipient: ' Получатель ', message: "  С днём <b>рождения</b>!\n ");

        $this->assertTrue($result->ok);
        $this->assertSame(1, $result->remainingCount);
        $this->assertSame(1, (int) DB::table('backpacks')->where('id', 1000)->value('count'));
        $gift = PlayerGift::query()->sole();
        $this->assertSame([2, 1, 10, 'С днём рождения!', false], [$gift->recipient_id, $gift->sender_id, $gift->share_item_id, $gift->message, $gift->is_anonymous]);
        $this->assertSame([['user' => 2, 'key' => 'chat.gift_received', 'params' => [
            'gift' => ['translations' => ['ru' => 'Хрустальная роза', 'uk' => 'Кришталева троянда']], 'user_id' => 1,
        ]]], $this->sentMessages);

        // Последний экземпляр — строка рюкзака и сам предмет удаляются.
        $this->assertTrue($this->send(backpackId: 1000, recipient: 'Получатель', message: null, anonymous: true)->ok);
        $this->assertDatabaseMissing('backpacks', ['id' => 1000]);
        $this->assertDatabaseMissing('items', ['id' => 100]);
        $this->assertSame('chat.gift_received_anonymous', $this->sentMessages[1]['key']);
        $this->assertArrayNotHasKey('user_id', $this->sentMessages[1]['params']);
        $anonymous = PlayerGift::query()->orderByDesc('id')->first();
        $this->assertTrue($anonymous->is_anonymous);
        $this->assertNull($anonymous->visibleSender());
        $this->assertNull($anonymous->message);
    }

    public function test_invalid_gifts_change_nothing(): void
    {
        $this->assertFalse($this->send(1000, 'Никто')->ok);
        $this->assertFalse($this->send(1000, 'Даритель')->ok);              // самому себе
        $this->assertFalse($this->send(1001, 'Получатель')->ok);            // не подарок
        $this->assertFalse($this->send(9999, 'Получатель')->ok);            // чужой/несуществующий
        DB::table('backpacks')->where('id', 1000)->update(['user_id' => 2]);
        $this->assertFalse($this->send(1000, 'Получатель')->ok);            // уже не в рюкзаке дарителя

        $this->assertSame(0, PlayerGift::query()->count());
        $this->assertSame([], $this->sentMessages);
        $this->assertSame(2, (int) DB::table('backpacks')->where('id', 1000)->value('count'));
    }

    public function test_long_signature_is_trimmed_to_150_chars(): void
    {
        $this->assertTrue($this->send(1000, 'Получатель', str_repeat('я', 400))->ok);
        $this->assertSame(150, mb_strlen((string) PlayerGift::query()->sole()->message));
    }

    public function test_profile_block_shows_latest_gifts_and_all_gifts_page_is_public(): void
    {
        foreach (range(1, 14) as $i) {
            PlayerGift::query()->create(['recipient_id' => 2, 'sender_id' => 1, 'share_item_id' => 10, 'message' => 'Подпись '.$i, 'is_anonymous' => $i === 14]);
        }

        $block = app(GetPlayerGiftsBlock::class)->execute(2);
        $this->assertSame(14, $block->total);
        $this->assertCount(GetPlayerGiftsBlock::LIMIT, $block->gifts);
        $this->assertSame('Подпись 14', $block->gifts->first()->message);
        $this->assertSame(0, app(GetPlayerGiftsBlock::class)->execute(1)->total);

        $html = view('gift::profile-block', ['giftsBlock' => $block])->render();
        $this->assertStringContainsString('data-id="gift_'.$block->gifts->first()->id.'"', $html);
        $this->assertStringContainsString('showItemInfo(this,event,2)', $html);
        $this->assertStringContainsString(e(route('gifts.index', ['id' => 2])), $html);

        // Подсказка — как у предметов: название, «Подарил», дата, подпись; анонимный — без имени.
        $tooltips = $this->tooltips($block->gifts);
        $first = $tooltips['gift_'.$block->gifts->first()->id];
        $this->assertSame('Хрустальная роза', $first['title']);
        $this->assertSame(__('gift.tooltip_anonymous'), $first['specialInfo'][0]['value']);
        $this->assertStringContainsString('«Подпись 14»', $first['desc']);
        $this->assertSame('Даритель', $tooltips['gift_'.$block->gifts->get(1)->id]['specialInfo'][0]['value']);

        $this->get(route('gifts.index', ['id' => 2]))->assertOk()
            ->assertSee('window.itemTooltip', false)->assertSee('Подпись 1')->assertSee('gift_', false);
        $this->get(route('gifts.index', ['id' => 404]))->assertNotFound();
    }

    public function test_tooltip_escapes_player_text(): void
    {
        DB::table('users')->where('id', 1)->update(['name' => '<b>Злодей</b>']);
        $gift = PlayerGift::query()->create(['recipient_id' => 2, 'sender_id' => 1, 'share_item_id' => 10,
            'message' => '<img src=x onerror=alert(1)> & "привет"', 'is_anonymous' => false]);

        $tooltip = $this->tooltips(collect([$gift->load(['shareItem', 'sender'])]))['gift_'.$gift->id];

        $this->assertStringNotContainsString('<img', $tooltip['desc']);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt; &amp; &quot;привет&quot;', $tooltip['desc']);
        $this->assertSame('&lt;b&gt;Злодей&lt;/b&gt;', $tooltip['specialInfo'][0]['value']);
    }

    /** @return array<string, array<string, mixed>> */
    private function tooltips(iterable $gifts): array
    {
        return app(ItemTooltipCollector::class)->collectFrom(new GiftTooltipStrategy($gifts))->all();
    }

    private function send(int $backpackId, string $recipient, ?string $message = 'Привет', bool $anonymous = false): SendGiftResult
    {
        return app(SendGift::class)->execute(User::query()->without('player')->findOrFail(1), $backpackId, $recipient, $message, $anonymous);
    }

    private function createTables(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->text('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('rarity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->integer('upgrade_lvl')->default(0);
            $table->integer('count_use')->default(0);
            $table->timestamps();
        });
        Schema::create('backpacks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('item_id');
            $table->integer('count')->default(1);
            $table->boolean('equipped')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('share_recipes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedBigInteger('kraft_item_id')->nullable();
            $table->timestamps();
        });
        Schema::create('share_recipe_has_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('share_recipe_id');
            $table->unsignedBigInteger('share_item_id');
            $table->integer('count')->default(1);
        });
        Schema::create('player_gifts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('recipient_id');
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->unsignedBigInteger('share_item_id');
            $table->string('message', 150)->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->timestamps();
        });
    }
}
