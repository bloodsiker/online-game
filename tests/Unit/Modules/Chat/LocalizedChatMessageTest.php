<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Chat;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatMessageTranslator;
use App\Modules\Chat\Application\UseCases\GetMessages;
use App\Modules\Chat\Application\UseCases\SendSystemMessage;
use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Chat\Domain\Repositories\ChatMessageRepositoryInterface;
use App\Modules\Chat\Domain\Services\MessageRenderer;
use App\Modules\Quest\Domain\Enums\QuestRewardType;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestReward;
use App\Modules\Quest\Presentation\Http\QuestController;
use App\Modules\User\Application\Services\GameSettingsService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

final class LocalizedChatMessageTest extends TestCase
{
    public function test_clan_online_notice_shows_level_and_info_icon_without_smiley(): void
    {
        $message = new LocalizedChatMessage('chat.clan_member_online', [
            'member' => new LocalizedChatMessage('chat.party_member', [
                'name' => 'BlooDSikeR',
                'level' => 6,
                'user_id' => 2,
                'icon' => '/main/images/player_info.gif',
            ]),
        ]);

        foreach (['ru' => 'вошёл в игру.', 'uk' => 'увійшов у гру.'] as $locale => $ending) {
            $html = app(ChatMessageTranslator::class)->render($message, $locale);

            $this->assertStringNotContainsString(':speak:', $html);
            $this->assertStringContainsString('<b>BlooDSikeR</b></a> [6]', $html);
            $this->assertStringContainsString('class="player-link n" data-uid="2" data-name="BlooDSikeR"', $html);
            $this->assertStringContainsString('chatPlayerClick(Number(this.dataset.uid), this.dataset.name); return false;', $html);
            $this->assertStringContainsString('chatOpenUserInfo(2)', $html);
            $this->assertStringContainsString('src="/main/images/player_info.gif"', $html);
            $this->assertStringEndsWith($ending, $html);
        }
    }

    public function test_templates_translate_nested_rewards_and_escape_parameters(): void
    {
        $translator = app(ChatMessageTranslator::class);
        $message = new LocalizedChatMessage('chat.reputation_offering', [
            'count' => 50, 'item' => '<script>alert(1)</script>', 'points' => 10, 'reputation' => 'Бог Войны',
            'reward' => new LocalizedChatMessage('chat.received_item', ['item_id' => 2712, 'count' => 1]),
        ]);
        $ru = $translator->render($message, 'ru');
        $uk = $translator->render($message, 'uk');
        $this->assertStringContainsString('Вы поднесли 50', $ru);
        $this->assertStringContainsString('Ви піднесли 50', $uk);
        $this->assertStringContainsString('Отримано [[share_item_2712]] ×1.', $uk);
        $this->assertStringContainsString('<b>«Бог Войны»</b>', $uk);
        $this->assertStringNotContainsString('<script>', $uk);
        $this->assertStringContainsString('&lt;script&gt;', $uk);
        $this->assertSame($ru, $translator->render($message, 'unsupported'));
        $this->assertSame('', $translator->render(new LocalizedChatMessage('auth.password'), 'ru'));
    }

    public function test_event_links_and_gathering_bonus_keep_markup_in_both_languages(): void
    {
        $translator = app(ChatMessageTranslator::class);
        $event = new LocalizedChatMessage('chat.world_event_started', [
            'event' => new LocalizedChatMessage('chat.event_link', ['url' => '/events?mode=events#event-run-25', 'title' => 'Воры']),
            'map' => new LocalizedChatMessage('chat.map_link', ['url' => '/map/city', 'title' => 'Нейрин']),
        ]);
        $uk = $translator->render($event, 'uk');
        $this->assertStringContainsString('Почалася подія', $uk);
        $this->assertStringContainsString('href="/events?mode=events#event-run-25"', $uk);
        $this->assertStringContainsString('window.open(this.href', $uk);
        $this->assertStringContainsString('<em>«Нейрин»</em>', $uk);
        $loot = new LocalizedChatMessage('chat.gathering', [
            'resource' => new LocalizedChatMessage('chat.gathering_resource', ['name' => 'Агат', 'count' => 1, 'backpack_count' => 5]),
            'bonus' => new LocalizedChatMessage('chat.gathering_bonus', ['resources' => [
                new LocalizedChatMessage('chat.gathering_resource', ['name' => 'Пыльца', 'count' => 2, 'backpack_count' => 7]),
            ]]),
        ]);
        $this->assertSame('Ви отримали річ <b>Агат</b> 1 шт. (у рюкзаку 5 шт.) Додатково: <b>Пыльца</b> 2 шт. (у рюкзаку 7 шт.)', $translator->render($loot, 'uk'));
        $this->assertSame($loot->payload(), json_decode(json_encode($loot->payload()), true));
    }

    public function test_single_persisted_message_is_rendered_for_each_recipient_and_legacy_is_preserved(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
            $this->markTestSkipped('Only SQLite :memory: is allowed.');
        }
        config(['cache.default' => 'array']);
        Schema::create('user_game_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->json('settings');
            $table->timestamps();
        });
        Schema::create('chat_messages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('map_id')->nullable();
            $table->unsignedBigInteger('clan_id')->nullable();
            $table->string('channel');
            $table->string('type');
            $table->text('message');
            $table->timestamps();
        });
        (require database_path('migrations/2026_10_11_120000_add_translations_to_chat_messages.php'))->up();
        DB::table('user_game_settings')->insert(['user_id' => 2, 'settings' => json_encode(['language' => 'uk'])]);
        $repo = Mockery::mock(ChatMessageRepositoryInterface::class);
        $repo->shouldReceive('create')->twice()->andReturnUsing(fn (array $data) => ChatMessage::create($data));
        $sender = new SendSystemMessage($repo);
        $message = $sender->toChannel(new LocalizedChatMessage('chat.world_event_finished', ['title' => 'Воры']));
        $legacy = $sender->toChannel('Старое сообщение');
        $this->assertSame('', $message->message);
        $this->assertSame('chat.world_event_finished', $message->fresh()->translation_key);
        $this->assertSame(['title' => 'Воры'], $message->fresh()->translation_params);
        foreach ([$message, $legacy] as $row) {
            $row->setRelation('sender', null);
            $row->setRelation('target', null);
        }
        $repo->shouldReceive('getForChannel')->times(3)->andReturn([$message, $legacy]);
        $getter = new GetMessages($repo, new MessageRenderer, app(ChatMessageTranslator::class), new GameSettingsService);
        $ruUser = (new User)->forceFill(['id' => 1]);
        $ukUser = (new User)->forceFill(['id' => 2]);
        $ru = $getter->execute($ruUser, ChatChannel::Main);
        $uk = $getter->execute($ukUser, ChatChannel::Main);
        $this->assertStringContainsString('закончилось!', $ru[0]->content);
        $this->assertStringContainsString('завершилася!', $uk[0]->content);
        $this->assertSame('Старое сообщение', $uk[1]->content);
        $this->assertSame($ru[0]->content, $getter->execute($ruUser, ChatChannel::Main)[0]->content);
        $this->assertSame(2, ChatMessage::query()->count());
    }

    public function test_quest_rewards_are_localized_without_database_lookups(): void
    {
        $quest = new Quest;
        $quest->title = 'Испытание';
        $rewardClass = QuestReward::class;
        $exp = (new $rewardClass)->forceFill(['type' => QuestRewardType::EXP, 'amount' => 100]);
        $item = (new $rewardClass)->forceFill(['type' => QuestRewardType::ITEM, 'amount' => 2, 'share_item_id' => null]);
        $quest->setRelation('rewards', new Collection([$exp, $item]));
        $reflection = new \ReflectionClass(QuestController::class);
        $message = $reflection->getMethod('buildQuestCompleteMessage')->invoke($reflection->newInstanceWithoutConstructor(), $quest);
        $uk = app(ChatMessageTranslator::class)->render($message, 'uk');
        $this->assertStringContainsString('Квест <b>«Испытание»</b> завершено!', $uk);
        $this->assertStringContainsString('+100 досвіду | 2x предмет', $uk);
    }
}
