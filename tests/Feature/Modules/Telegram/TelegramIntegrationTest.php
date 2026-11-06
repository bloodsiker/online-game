<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Telegram;

use App\Modules\Telegram\Application\Services\TelegramLinkService;
use App\Modules\Telegram\Domain\Contracts\BackpackPreviewRenderer;
use App\Modules\Telegram\Domain\Contracts\TelegramLinkRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class TelegramIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'session.driver' => 'array', 'cache.default' => 'array',
            'telegram.bot_username' => 'eternity_test_bot', 'telegram.bot_token' => 'test-bot-token',
            'telegram.api_secret' => str_repeat('a', 40), 'telegram.webhook_secret' => str_repeat('w', 40)]);
        DB::purge('sqlite');
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        app()->setLocale('ru');
        Http::preventStrayRequests();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('player_id');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('active_login_token')->nullable();
            $table->unsignedBigInteger('money')->default(0);
            $table->decimal('diamond', 14, 2)->default(0);
        });
        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('lvl');
        });
        Schema::create('user_game_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->json('settings');
        });
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->text('name');
            $table->string('type')->default('resource');
            $table->string('rarity')->default('common');
            $table->string('image')->nullable();
            $table->unsignedInteger('count_use')->default(0);
        });
        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedInteger('upgrade_lvl')->default(0);
            $table->unsignedInteger('count_use')->default(0);
            $table->timestamp('expires_at')->nullable();
        });
        Schema::create('backpacks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedInteger('count')->default(1);
            $table->boolean('equipped')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
        });
        foreach (['auctions' => 'item_id', 'auction_orders' => 'share_item_id'] as $name => $itemColumn) {
            Schema::create($name, function (Blueprint $table) use ($itemColumn): void {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('structure_id')->nullable();
                $table->unsignedBigInteger($itemColumn);
                $table->integer('count');
                $table->integer('price');
                $table->timestamps();
            });
        }
        (require database_path('migrations/2026_11_06_120000_create_telegram_links_tables.php'))->up();
        foreach ([1, 2] as $id) {
            DB::table('players')->insert(['id' => $id, 'lvl' => $id * 10]);
            DB::table('users')->insert(['id' => $id, 'name' => $id === 1 ? 'Mage <test>' : 'Other',
                'player_id' => $id, 'money' => $id * 1000, 'diamond' => '56.75',
                'email_verified_at' => now(), 'active_login_token' => 'session-'.$id]);
            DB::table('user_game_settings')->insert(['user_id' => $id, 'settings' => json_encode(['language' => 'uk'])]);
        }
    }

    public function test_api_rejects_missing_wrong_and_empty_secret_and_unlinked_identity(): void
    {
        $this->getJson(route('telegram.api.character'))->assertUnauthorized();
        $this->getJson(route('telegram.api.character'), ['X-Telegram-Api-Secret' => 'wrong'])->assertUnauthorized();
        $this->getJson(route('telegram.api.character'), $this->apiHeaders())->assertForbidden();
        config(['telegram.api_secret' => '']);
        $this->getJson(route('telegram.api.character'), $this->apiHeaders())->assertUnauthorized();
    }

    public function test_character_api_returns_only_linked_character_without_losing_fractional_diamonds(): void
    {
        $this->link();
        $response = $this->getJson(route('telegram.api.character', ['user_id' => 2]), $this->apiHeaders())
            ->assertOk()->assertExactJson(['ok' => true, 'data' => [
                'nickname' => 'Mage <test>', 'level' => 10, 'money' => 1000, 'diamonds' => '56.75',
            ]]);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringNotContainsString('Set-Cookie', implode(' ', array_keys($response->headers->all())));
        $this->assertSame(1000, (int) DB::table('users')->where('id', 1)->value('money'));
        DB::table('users')->where('id', 1)->update(['email_verified_at' => null]);
        $this->getJson(route('telegram.api.character'), $this->apiHeaders())->assertForbidden();
        $this->getJson(route('telegram.api.auction'), $this->apiHeaders())->assertForbidden();
    }

    public function test_market_api_filters_owner_paginates_localizes_and_distinguishes_total_and_unit_prices(): void
    {
        $this->link();
        $this->marketFixtures();
        $this->getJson(route('telegram.api.auction', ['per_page' => 1]), $this->apiHeaders())
            ->assertOk()->assertJsonPath('meta.total', 2)->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('data.0.name', 'Агат +2')->assertJsonPath('data.0.total_price', 100)
            ->assertJsonPath('data.0.unit_price', 25)->assertJsonPath('data.0.quantity', 4);
        $this->getJson(route('telegram.api.auction', ['per_page' => 1, 'page' => 2]), $this->apiHeaders())
            ->assertOk()->assertJsonPath('data.0.id', 1)->assertJsonCount(1, 'data');
        $this->getJson(route('telegram.api.exchange'), $this->apiHeaders())
            ->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.name', 'Агат')
            ->assertJsonPath('data.0.quantity', 3)->assertJsonPath('data.0.unit_price', 20)
            ->assertJsonPath('data.0.total_price', 60);
        foreach (['per_page=51', 'per_page=0', 'page=-1', 'page[]=1'] as $query) {
            $this->getJson(route('telegram.api.auction').'?'.$query, $this->apiHeaders())->assertUnprocessable();
        }
    }

    public function test_backpack_api_filters_categories_counts_stacks_and_excludes_other_players_equipment_and_expired_items(): void
    {
        $this->getJson(route('telegram.api.backpack'))->assertUnauthorized();
        $this->link();
        $this->backpackFixtures();
        $response = $this->getJson(route('telegram.api.backpack', ['user_id' => 2]), $this->apiHeaders())->assertOk()
            ->assertJsonPath('category', 'main')->assertJsonPath('meta.total', 10)->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.per_page', 8)->assertJsonCount(8, 'data')->assertJsonCount(6, 'categories')
            ->assertJsonPath('data.0.name', 'Агат <магічний>')->assertJsonPath('data.0.quantity', 500)
            ->assertJsonPath('data.0.image_url', asset('/storage/telegram-test/icon.png'));
        $categories = collect($response->json('categories'))->keyBy('key');
        $this->assertSame(10, $categories['main']['positions']);
        $this->assertSame(1, $categories['artifact']['positions']);
        $this->getJson(route('telegram.api.backpack', ['category' => 'artifact']), $this->apiHeaders())->assertOk()
            ->assertJsonPath('meta.total', 1)->assertJsonCount(1, 'data')->assertJsonPath('data.0.upgrade_level', 2)
            ->assertJsonPath('data.0.name', 'Предмет 3 +2');
        foreach (['misc', 'key', 'quest', 'gift'] as $category) {
            $this->getJson(route('telegram.api.backpack', ['category' => $category]), $this->apiHeaders())
                ->assertOk()->assertJsonPath('meta.total', 1)->assertJsonCount(1, 'data');
        }
        $this->getJson(route('telegram.api.backpack', ['page' => 999]), $this->apiHeaders())->assertOk()
            ->assertJsonPath('meta.current_page', 2)->assertJsonCount(2, 'data');
        foreach (['category=unknown', 'category[]=main', 'per_page=0', 'per_page=51', 'page=-1'] as $query) {
            $this->getJson(route('telegram.api.backpack').'?'.$query, $this->apiHeaders())->assertUnprocessable();
        }
        $this->assertSame(1000, (int) DB::table('users')->where('id', 1)->value('money'));
        $this->assertSame(19, DB::table('backpacks')->count());
    }

    public function test_bot_backpack_returns_numbered_picture_and_inline_category_pagination_buttons(): void
    {
        $this->link();
        $this->backpackFixtures();
        $response = $this->postJson(route('telegram.webhook'), $this->update('/backpack'), $this->webhookHeaders())
            ->assertOk()->assertJsonPath('method', 'sendPhoto')->assertJsonPath('chat_id', 10001);
        $this->assertStringContainsString('Агат &lt;магічний&gt; ×500', $response->json('caption'));
        $this->assertLessThanOrEqual(1024, mb_strlen(html_entity_decode(strip_tags($response->json('caption')))));
        $buttons = collect($response->json('reply_markup.inline_keyboard'))->flatten(1)->pluck('callback_data');
        $this->assertContains('bag:main:2', $buttons);
        $this->assertContains('bag:artifact:1', $buttons);
        $this->assertNotContains('bag:main:0', $buttons);
        $image = $this->get($response->json('photo'))->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->assertSame("\x89PNG\r\n\x1a\n", substr($image->getContent(), 0, 8));
        $decoded = imagecreatefromstring($image->getContent());
        $this->assertSame(600, imagesx($decoded));
        $this->assertSame(324, imagesy($decoded));
        $this->assertSame(0xFF0000, imagecolorat($decoded, 70, 60) & 0xFFFFFF);
        imagedestroy($decoded);
        Http::assertNothingSent();
    }

    public function test_backpack_callbacks_edit_same_message_acknowledge_after_transaction_and_reset_page_when_switching_category(): void
    {
        $this->link();
        $this->backpackFixtures();
        $transactionLevels = [];
        Http::fake(function ($request) use (&$transactionLevels) {
            $transactionLevels[] = DB::transactionLevel();

            return Http::response(['ok' => true]);
        });
        $response = $this->postJson(route('telegram.webhook'), $this->callbackUpdate('bag:main:2'), $this->webhookHeaders())
            ->assertOk()->assertJsonPath('method', 'editMessageMedia')->assertJsonPath('message_id', 77)
            ->assertJsonPath('chat_id', 10001)->assertJsonPath('media.type', 'photo');
        $this->assertStringContainsString('2/2', $response->json('media.caption'));
        Http::assertSent(fn ($request): bool => str_ends_with($request->url(), '/answerCallbackQuery')
            && $request['callback_query_id'] === 'callback-1');
        $this->assertSame([0], $transactionLevels);
        $buttons = collect($response->json('reply_markup.inline_keyboard'))->flatten(1)->pluck('callback_data');
        $this->assertContains('bag:main:1', $buttons);
        $this->assertNotContains('bag:main:3', $buttons);
        $response = $this->postJson(route('telegram.webhook'), $this->callbackUpdate('bag:artifact:1', 2), $this->webhookHeaders())
            ->assertOk()->assertJsonPath('method', 'editMessageMedia');
        $this->assertStringContainsString('Предмет 3 +2', $response->json('media.caption'));
        $this->assertStringContainsString('1/1', $response->json('media.caption'));
        $this->postJson(route('telegram.webhook'), $this->callbackUpdate('bag:artifact:1', 2), $this->webhookHeaders())
            ->assertExactJson($response->json());
        $this->assertSame(2, DB::table('telegram_webhook_updates')->count());
    }

    public function test_backpack_callbacks_do_not_accept_other_chats_invalid_categories_or_revoked_links(): void
    {
        $this->link();
        $update = $this->callbackUpdate('bag:main:1');
        $update['callback_query']['message']['chat']['id'] = 20002;
        $this->postJson(route('telegram.webhook'), $update, $this->webhookHeaders())->assertExactJson(['ok' => true]);
        $update = $this->callbackUpdate('bag:main:1');
        $update['callback_query']['message']['chat']['type'] = 'group';
        $this->postJson(route('telegram.webhook'), $update, $this->webhookHeaders())->assertExactJson(['ok' => true]);
        $this->postJson(route('telegram.webhook'), $this->callbackUpdate('bag:other:1'), $this->webhookHeaders())->assertOk()
            ->assertJsonPath('method', 'answerCallbackQuery')->assertJsonPath('show_alert', true);
        DB::table('users')->where('id', 1)->update(['email_verified_at' => null]);
        $this->postJson(route('telegram.webhook'), $this->callbackUpdate('bag:main:1', 2), $this->webhookHeaders())
            ->assertJsonPath('text', __('telegram.account_unavailable', locale: 'uk'));
        app(TelegramLinkRepository::class)->unlink(userId: 1);
        $this->postJson(route('telegram.webhook'), $this->callbackUpdate('bag:main:1', 3), $this->webhookHeaders())
            ->assertJsonPath('method', 'answerCallbackQuery')->assertJsonPath('show_alert', true);
        Http::assertNothingSent();
    }

    public function test_backpack_picture_requires_a_valid_signature_and_stops_working_after_unlink(): void
    {
        $this->link();
        $this->backpackFixtures();
        $photo = $this->postJson(route('telegram.webhook'), $this->update('/backpack'), $this->webhookHeaders())->assertOk()->json('photo');
        $this->get($photo)->assertOk();
        $this->get(strtok($photo, '?'))->assertForbidden();
        $this->get($photo.'&changed=1')->assertForbidden();
        app(TelegramLinkRepository::class)->unlink(userId: 1);
        $this->get($photo)->assertNotFound();
    }

    public function test_backpack_previews_reuse_unchanged_contents_and_expire(): void
    {
        $this->link();
        $this->backpackFixtures();
        $first = $this->postJson(route('telegram.webhook'), $this->update('/backpack'), $this->webhookHeaders())->assertOk()->json('photo');
        $again = $this->postJson(route('telegram.webhook'), $this->update('/backpack', 2), $this->webhookHeaders())->assertOk()->json('photo');
        $this->assertSame(parse_url($first, PHP_URL_PATH), parse_url($again, PHP_URL_PATH));
        $this->get($first)->assertOk();
        $this->travel(25)->hours();
        try {
            $this->get($first)->assertForbidden();
        } finally {
            $this->travelBack();
        }
    }

    public function test_empty_backpack_and_stale_page_are_displayed_without_errors(): void
    {
        $this->link();
        $this->getJson(route('telegram.api.backpack', ['category' => 'artifact', 'page' => 3]), $this->apiHeaders())
            ->assertOk()->assertJsonPath('meta.current_page', 1)->assertJsonPath('meta.last_page', 1)->assertJsonCount(0, 'data');
        $response = $this->postJson(route('telegram.webhook'), $this->update('/backpack artifact 2'), $this->webhookHeaders())
            ->assertOk()->assertJsonPath('method', 'sendPhoto');
        $this->assertStringContainsString(__('telegram.backpack_empty', locale: 'uk'), $response->json('caption'));
        $this->get($response->json('photo'))->assertOk();
    }

    public function test_backpack_renderer_displays_real_webp_icons(): void
    {
        Storage::fake('public');
        $this->assertTrue(function_exists('imagewebp'), 'PHP image must be built with GD WebP support.');
        $icon = imagecreatetruecolor(12, 12);
        imagefill($icon, 0, 0, imagecolorallocate($icon, 0, 255, 0));
        ob_start();
        imagewebp($icon, quality: 100);
        Storage::disk('public')->put('telegram-test/icon.webp', ob_get_clean());
        imagedestroy($icon);
        $png = app(BackpackPreviewRenderer::class)->render(items: [['image_url' => asset('/storage/telegram-test/icon.webp'),
            'quantity' => 10, 'rarity_color' => '#666666', 'upgrade_level' => 0]]);
        $decoded = imagecreatefromstring($png);
        $pixel = imagecolorsforindex($decoded, imagecolorat($decoded, 70, 60));
        $this->assertGreaterThan(240, $pixel['green']);
        $this->assertLessThan(10, $pixel['red']);
        imagedestroy($decoded);
    }

    public function test_link_tokens_are_hashed_single_use_and_new_link_invalidates_previous_one(): void
    {
        $links = app(TelegramLinkRepository::class);
        $old = $links->createToken(userId: 1);
        $token = $links->createToken(userId: 1);
        $this->assertNotSame($old, $token);
        $this->assertSame(hash('sha256', $token), DB::table('telegram_link_tokens')->value('token_hash'));
        $this->assertInvalidToken($old);
        $account = $links->consumeToken(token: $token, telegramUserId: 10001, username: 'player');
        $this->assertSame(1, $account->userId);
        $this->assertSame(10001, $account->telegramUserId);
        $this->assertSame(0, DB::table('telegram_link_tokens')->count());
        $this->assertInvalidToken($token);
    }

    public function test_expired_token_and_unverified_owner_cannot_link(): void
    {
        $token = app(TelegramLinkRepository::class)->createToken(userId: 1);
        DB::table('telegram_link_tokens')->update(['expires_at' => now()->subSecond()]);
        $this->assertInvalidToken($token);
        $token = app(TelegramLinkRepository::class)->createToken(userId: 1);
        DB::table('users')->where('id', 1)->update(['email_verified_at' => null]);
        $this->assertInvalidToken($token);
        $this->assertSame(0, DB::table('telegram_links')->count());
    }

    public function test_a_telegram_account_cannot_take_over_another_character(): void
    {
        $this->link();
        $token = app(TelegramLinkRepository::class)->createToken(userId: 2);
        $this->assertInvalidToken($token);
        $this->assertSame(1, app(TelegramLinkRepository::class)->forTelegram(telegramUserId: 10001)->userId);
        $this->assertNull(app(TelegramLinkRepository::class)->forUser(userId: 2));
    }

    public function test_unlink_revokes_api_access_and_pending_tokens_immediately(): void
    {
        $this->link();
        app(TelegramLinkRepository::class)->unlink(userId: 1);
        $this->getJson(route('telegram.api.character'), $this->apiHeaders())->assertForbidden();
        $token = app(TelegramLinkRepository::class)->createToken(userId: 1);
        app(TelegramLinkRepository::class)->unlink(userId: 1);
        $this->assertInvalidToken($token);
    }

    public function test_webhook_requires_its_own_secret_and_ignores_groups_and_forged_sender_identity(): void
    {
        $this->postJson(route('telegram.webhook'), $this->update('/character'))->assertForbidden();
        $this->postJson(route('telegram.webhook'), $this->update('/character'), $this->apiHeaders())->assertForbidden();
        foreach (['group', 'supergroup', 'channel'] as $type) {
            $update = $this->update('/character');
            $update['message']['chat']['type'] = $type;
            $this->postJson(route('telegram.webhook'), $update, $this->webhookHeaders())->assertExactJson(['ok' => true]);
        }
        $update = $this->update('/character');
        $update['message']['chat']['id'] = 20002;
        $this->postJson(route('telegram.webhook'), $update, $this->webhookHeaders())->assertExactJson(['ok' => true]);
        $this->assertSame(0, DB::table('telegram_webhook_updates')->count());
    }

    public function test_bot_links_once_handles_duplicate_update_and_escapes_player_names(): void
    {
        $token = app(TelegramLinkRepository::class)->createToken(userId: 1);
        $update = $this->update('/start '.$token);
        $response = $this->postJson(route('telegram.webhook'), $update, $this->webhookHeaders())->assertOk()
            ->assertJsonPath('method', 'sendMessage')->assertJsonPath('chat_id', 10001)
            ->assertJsonPath('text', 'Telegram прив’язано до персонажа <b>Mage &lt;test&gt;</b>. Виберіть дію в меню.');
        $this->postJson(route('telegram.webhook'), $update, $this->webhookHeaders())->assertExactJson($response->json());
        $this->assertSame(1, DB::table('telegram_links')->count());
        $this->assertSame(1, DB::table('telegram_webhook_updates')->count());
        $this->postJson(route('telegram.webhook'), $this->update('/character', 2), $this->webhookHeaders())->assertOk()
            ->assertJsonPath('text', "<b>Mage &lt;test&gt;</b> [10]\nМонети: 1 000\nДіаманти: 56.75");
        Http::assertNothingSent();
    }

    public function test_bot_commands_and_menu_show_only_own_market_data(): void
    {
        $this->link();
        $this->marketFixtures();
        $reply = $this->postJson(route('telegram.webhook'), $this->update('Мої лоти'), $this->webhookHeaders())->assertOk();
        $this->assertStringContainsString('Агат +2 ×4 — 100', $reply->json('text'));
        $this->assertStringContainsString('Усього: 2.', $reply->json('text'));
        $reply = $this->postJson(route('telegram.webhook'), $this->update('/exchange', 2), $this->webhookHeaders())->assertOk();
        $this->assertStringContainsString('Агат ×3 — 60', $reply->json('text'));
        $this->postJson(route('telegram.webhook'), $this->update('/auction -1', 3), $this->webhookHeaders())
            ->assertJsonPath('text', __('telegram.invalid_page', locale: 'uk'));
        app(TelegramLinkRepository::class)->unlink(userId: 1);
        $this->postJson(route('telegram.webhook'), $this->update('/character', 4), $this->webhookHeaders())
            ->assertJsonPath('text', __('telegram.not_linked', locale: 'uk'));
    }

    public function test_settings_actions_require_login_and_act_only_on_current_user(): void
    {
        $this->post(route('telegram.link'))->assertUnauthorized();
        $user = User::query()->without('player')->findOrFail(1)->setRelation('player', null);
        $this->actingAs($user)->withSession(['active_login_token' => 'session-1']);
        $response = $this->post(route('telegram.link'), ['user_id' => 2])->assertRedirect(route('settings'));
        $this->assertStringStartsWith('https://t.me/eternity_test_bot?start=', $response->getSession()->get('telegram_link'));
        $this->assertSame(1, (int) DB::table('telegram_link_tokens')->value('user_id'));
        $this->link(userId: 2, telegramId: 20002);
        $this->delete(route('telegram.unlink'), ['user_id' => 2])->assertRedirect(route('settings'));
        $this->assertNotNull(app(TelegramLinkRepository::class)->forUser(userId: 2));
        $this->assertSame(0, DB::table('telegram_link_tokens')->count());
    }

    public function test_missing_configuration_disables_link_creation_and_revoked_account_gets_no_bot_data(): void
    {
        $this->link();
        DB::table('users')->where('id', 1)->update(['email_verified_at' => null]);
        $this->postJson(route('telegram.webhook'), $this->update('/character'), $this->webhookHeaders())->assertOk()
            ->assertJsonPath('text', __('telegram.account_unavailable', locale: 'uk'));
        config(['telegram.bot_token' => '']);
        $this->assertFalse(app(TelegramLinkService::class)->available());
        $html = view('telegram::settings')->render();
        $this->assertStringContainsString(__('telegram.unavailable'), $html);
        $this->assertStringNotContainsString('action="'.route('telegram.link').'"', $html);
    }

    public function test_settings_link_can_be_created_as_json_for_the_modal_without_redirecting(): void
    {
        $user = User::query()->without('player')->findOrFail(1)->setRelation('player', null);
        $this->actingAs($user)->withSession(['active_login_token' => 'session-1']);
        $response = $this->postJson(route('telegram.link'), ['user_id' => 2])->assertOk()
            ->assertJsonPath('ok', true)->assertSessionMissing('telegram_link');
        $this->assertStringStartsWith('https://t.me/eternity_test_bot?start=', $response->json('url'));
        parse_str(parse_url($response->json('url'), PHP_URL_QUERY), $query);
        $this->assertSame(hash('sha256', $query['start']), DB::table('telegram_link_tokens')->where('user_id', 1)->value('token_hash'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->link();
        $this->postJson(route('telegram.link'))->assertUnprocessable()->assertJsonValidationErrors('telegram');
    }

    public function test_settings_block_has_game_buttons_status_and_localization(): void
    {
        $html = view('telegram::settings', ['telegramAvailable' => true])->render();
        $this->assertStringContainsString('Привязать Telegram', $html);
        $this->assertStringContainsString('class="butt1 pointer"', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->link();
        app()->setLocale('uk');
        $html = view('telegram::settings', ['telegramAccount' => app(TelegramLinkRepository::class)->forUser(userId: 1)])->render();
        $this->assertStringContainsString('Telegram прив’язано:', $html);
        $this->assertStringContainsString('@player', $html);
        $this->assertStringContainsString('name="_method" value="DELETE"', $html);
        $this->assertStringNotContainsString('test-bot-token', $html);
        $this->assertStringNotContainsString(config('telegram.api_secret'), $html);
    }

    public function test_api_is_rate_limited_and_settings_have_csrf_web_middleware(): void
    {
        $this->link();
        foreach (range(1, 60) as $attempt) {
            $this->getJson(route('telegram.api.character'), $this->apiHeaders())->assertOk();
        }
        $this->getJson(route('telegram.api.character'), $this->apiHeaders())->assertStatus(429);
        foreach (['telegram.link', 'telegram.unlink'] as $name) {
            $route = app('router')->getRoutes()->getByName($name);
            $this->assertContains('web', $route->gatherMiddleware());
            $this->assertContains('auth', $route->gatherMiddleware());
        }
        foreach (['telegram.webhook', 'telegram.api.character'] as $name) {
            $this->assertNotContains('web', app('router')->getRoutes()->getByName($name)->gatherMiddleware());
        }
    }

    public function test_webhook_setup_and_cleanup_commands(): void
    {
        config(['app.url' => 'http://localhost']);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
        $this->artisan('telegram:set-webhook')->assertFailed();
        Http::assertNothingSent();
        $this->artisan('telegram:set-webhook', ['--url' => 'https://game.example.test'])->assertSuccessful();
        Http::assertSent(fn ($request): bool => $request['url'] === 'https://game.example.test/telegram/webhook'
            && $request['secret_token'] === config('telegram.webhook_secret') && $request['allowed_updates'] === ['message', 'callback_query']);
        config(['app.url' => 'https://default.example.test']);
        $this->artisan('telegram:set-webhook')->assertSuccessful();
        Http::assertSent(fn ($request): bool => $request['url'] === 'https://default.example.test/telegram/webhook');
        app(TelegramLinkRepository::class)->createToken(userId: 1);
        DB::table('telegram_link_tokens')->update(['expires_at' => now()->subMinute()]);
        DB::table('telegram_webhook_updates')->insert(['update_id' => 1, 'response' => '{}', 'created_at' => now()->subDays(8)]);
        $this->artisan('telegram:prune')->assertSuccessful();
        $this->assertSame(0, DB::table('telegram_link_tokens')->count());
        $this->assertSame(0, DB::table('telegram_webhook_updates')->count());
    }

    private function link(int $userId = 1, int $telegramId = 10001): void
    {
        $links = app(TelegramLinkRepository::class);
        $links->consumeToken(token: $links->createToken(userId: $userId), telegramUserId: $telegramId, username: 'player');
    }

    private function assertInvalidToken(string $token): void
    {
        try {
            app(TelegramLinkRepository::class)->consumeToken(token: $token, telegramUserId: 10001, username: null);
            $this->fail('Invalid or used token must not link an account.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('telegram', $exception->errors());
        }
    }

    /** @return array<string, string> */
    private function apiHeaders(): array
    {
        return ['X-Telegram-Api-Secret' => str_repeat('a', 40), 'X-Telegram-User-Id' => '10001'];
    }

    /** @return array<string, string> */
    private function webhookHeaders(): array
    {
        return ['X-Telegram-Bot-Api-Secret-Token' => str_repeat('w', 40)];
    }

    /** @return array<string, mixed> */
    private function update(string $text, int $id = 1): array
    {
        return ['update_id' => $id, 'message' => ['from' => ['id' => 10001, 'username' => 'player', 'is_bot' => false, 'language_code' => 'uk'],
            'chat' => ['id' => 10001, 'type' => 'private'], 'text' => $text]];
    }

    private function marketFixtures(): void
    {
        DB::table('share_items')->insert(['id' => 1, 'name' => json_encode(['ru' => 'Агат RU', 'uk' => 'Агат'])]);
        DB::table('items')->insert(['id' => 1, 'share_item_id' => 1, 'upgrade_lvl' => 2]);
        foreach ([[1, 1, 4], [2, 1, 4], [3, 2, 99], [4, 1, 0]] as [$id, $owner, $quantity]) {
            DB::table('auctions')->insert(['id' => $id, 'user_id' => $owner, 'structure_id' => 9,
                'item_id' => 1, 'count' => $quantity, 'price' => 100, 'created_at' => now(), 'updated_at' => now()]);
        }
        foreach ([1, 2] as $owner) {
            DB::table('auction_orders')->insert(['user_id' => $owner, 'structure_id' => 13,
                'share_item_id' => 1, 'count' => 3, 'price' => 20, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    /** @return array<string, mixed> */
    private function callbackUpdate(string $data, int $id = 1): array
    {
        return ['update_id' => $id, 'callback_query' => ['id' => 'callback-'.$id, 'data' => $data,
            'from' => ['id' => 10001, 'is_bot' => false, 'language_code' => 'uk'],
            'message' => ['message_id' => 77, 'chat' => ['id' => 10001, 'type' => 'private']]]];
    }

    private function backpackFixtures(): void
    {
        Storage::fake('public');
        $icon = imagecreatetruecolor(12, 12);
        imagefill($icon, 0, 0, imagecolorallocate($icon, 255, 0, 0));
        ob_start();
        imagepng($icon);
        Storage::disk('public')->put('telegram-test/icon.png', ob_get_clean());
        imagedestroy($icon);
        foreach ([1 => 'resource', 2 => 'fish', 3 => 'artifact', 4 => 'key', 5 => 'quest', 6 => 'gift', 7 => 'misc', 8 => 'trade_goods', 9 => 'treasure_map'] as $id => $type) {
            DB::table('share_items')->insert(['id' => $id, 'name' => json_encode(['ru' => 'Предмет RU '.$id,
                'uk' => $id === 1 ? 'Агат <магічний>' : 'Предмет '.$id]), 'type' => $type,
                'image' => 'telegram-test/icon.png']);
            DB::table('items')->insert(['id' => $id, 'share_item_id' => $id, 'upgrade_lvl' => $id === 3 ? 2 : 0]);
            DB::table('backpacks')->insert(['id' => $id, 'user_id' => 1, 'item_id' => $id, 'count' => $id === 1 ? 500 : 1]);
        }
        foreach (range(10, 19) as $id) {
            DB::table('items')->insert(['id' => $id, 'share_item_id' => 1, 'expires_at' => $id === 17 ? now()->subMinute() : null]);
            DB::table('backpacks')->insert(['id' => $id, 'user_id' => $id === 19 ? 2 : 1, 'item_id' => $id,
                'count' => $id === 18 ? 0 : 1, 'equipped' => $id === 16]);
        }
    }
}
