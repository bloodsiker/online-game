<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Payments;

use App\Modules\Notification\Domain\Events\PlayerNotificationAvailable;
use App\Modules\Payments\Application\Services\ManagePaymentSettings;
use App\Modules\Payments\Application\Services\PaymentOrderService;
use App\Modules\Payments\Domain\Enums\PaymentOrderStatus;
use App\Modules\Payments\Infrastructure\Nbu\NbuExchangeRate;
use App\Modules\Payments\Infrastructure\Persistence\Models\PaymentOrder;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class PaymentOrderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
            $this->markTestSkipped('Только SQLite :memory:, игровая база не используется.');
        }
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('lvl');
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('player_id')->nullable();
            $table->integer('diamond')->default(10);
            $table->integer('money')->default(100);
            $table->boolean('is_admin')->default(false);
            $table->string('active_login_token')->default('test-login');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });
        foreach ([1, 2, 3] as $id) {
            DB::table('users')->insert(['id' => $id, 'name' => 'PaymentUser'.$id, 'is_admin' => $id === 3, 'email_verified_at' => now()]);
        }
        Schema::create('user_game_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->json('settings');
            $table->timestamps();
        });
        (require database_path('migrations/2026_10_21_000003_create_payment_orders.php'))->up();
        (require database_path('migrations/2026_10_21_000004_use_usd_for_new_payment_orders.php'))->up();
        (require database_path('migrations/2026_11_21_000001_add_payment_url_to_payment_orders.php'))->up();
        (require database_path('migrations/2026_11_22_000001_add_charge_amount_to_payment_orders.php'))->up();
        (require database_path('migrations/2026_11_22_000002_create_exchange_rates.php'))->up();
        config(['services.monobank.token' => null]);
        app(ManagePaymentSettings::class)->execute(['diamond_price' => '6.50', 'minimum_amount' => '500.00', 'is_enabled' => 1]);
    }

    private function createOrder(int $userId = 1, int $diamonds = 77, ?string $requestId = null): PaymentOrder
    {
        return app(PaymentOrderService::class)->create($userId, $diamonds, $requestId ?? (string) Str::uuid());
    }

    private function loginAs(int $id): static
    {
        return $this->actingAs(User::query()->without('player')->findOrFail($id))
            ->withSession(['active_login_token' => 'test-login', '_token' => 'payment-test-csrf'])
            ->withHeaders(['X-CSRF-TOKEN' => 'payment-test-csrf']);
    }

    public function test_order_freezes_price_currency_and_amount_without_adding_diamonds(): void
    {
        $order = $this->createOrder();
        $this->assertSame(PaymentOrderStatus::PENDING, $order->status);
        $this->assertSame(50050, $order->amount_minor);
        $this->assertSame(650, $order->diamond_price_minor);
        $this->assertSame('USD', $order->currency);
        $this->assertEquals(10, DB::table('users')->where('id', 1)->value('diamond'));
        app(ManagePaymentSettings::class)->execute(['diamond_price' => '10.00', 'minimum_amount' => '500.00', 'is_enabled' => 1]);
        $this->assertSame(50050, $order->fresh()->amount_minor);
        $this->assertSame(77000, $this->createOrder()->amount_minor);
    }

    public function test_duplicate_creation_returns_same_order(): void
    {
        $uuid = (string) Str::uuid();
        $first = $this->createOrder(requestId: $uuid);
        $second = $this->createOrder(requestId: $uuid);
        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, PaymentOrder::count());
        $this->assertSame(1, DB::table('payment_order_events')->count());
        $this->assertNotSame($first->id, $this->createOrder(userId: 2, requestId: $uuid)->id);
    }

    public function test_confirmation_adds_exact_quantity_once_and_preserves_audit(): void
    {
        $order = $this->createOrder();
        $service = app(PaymentOrderService::class);
        $service->confirm($order->public_id, 3, 'Проверен платёж');
        $service->confirm($order->public_id, 3, 'Повторный клик');
        $this->assertEquals(87, DB::table('users')->where('id', 1)->value('diamond'));
        $order->refresh();
        $this->assertSame(PaymentOrderStatus::PAID, $order->status);
        $this->assertSame(3, $order->reviewed_by);
        $this->assertSame('Проверен платёж', $order->review_note);
        $this->assertNotNull($order->credited_at);
        $this->assertEquals(87, $order->diamond_balance_after);
        $this->assertSame(2, DB::table('payment_order_events')->count());
    }

    public function test_notification_is_persisted_once_for_offline_player(): void
    {
        Event::fake([PlayerNotificationAvailable::class]);
        Schema::create('player_notifications', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type');
            $table->string('deduplication_key');
            $table->string('translation_key');
            $table->json('translation_params');
            $table->timestamps();
            $table->unique(['user_id', 'deduplication_key']);
        });
        $order = $this->createOrder();
        app(PaymentOrderService::class)->confirm($order->public_id, 3);
        app(PaymentOrderService::class)->confirm($order->public_id, 3);
        $this->assertSame(1, DB::table('player_notifications')->count());
        $this->assertSame('payments.credited_notification', DB::table('player_notifications')->value('translation_key'));
    }

    public function test_rejected_and_cancelled_orders_cannot_be_credited(): void
    {
        $service = app(PaymentOrderService::class);
        foreach (['reject', 'cancel'] as $action) {
            $order = $this->createOrder();
            if ($action === 'reject') {
                $service->reject($order->public_id, 3, 'Оплата не найдена');
            } else {
                $service->cancel($order->public_id, 1);
            }
            try {
                $service->confirm($order->public_id, 3);
                $this->fail('Closed order cannot be paid.');
            } catch (ValidationException) {
                $this->assertEquals(10, DB::table('users')->where('id', 1)->value('diamond'));
            }
        }
    }

    public function test_paid_order_cannot_be_cancelled_or_rejected(): void
    {
        $order = $this->createOrder();
        $service = app(PaymentOrderService::class);
        $service->confirm($order->public_id, 3);
        foreach (['reject', 'cancel'] as $action) {
            try {
                if ($action === 'reject') {
                    $service->reject($order->public_id, 3, 'Нельзя');
                } else {
                    $service->cancel($order->public_id, 1);
                }
                $this->fail('Paid order cannot be changed.');
            } catch (ValidationException) {
                $this->assertSame(PaymentOrderStatus::PAID, $order->fresh()->status);
            }
        }
    }

    public function test_confirmation_rolls_back_when_audit_write_fails(): void
    {
        $order = $this->createOrder();
        DB::statement("CREATE TRIGGER reject_payment_audit BEFORE INSERT ON payment_order_events WHEN NEW.to_status = 'paid' BEGIN SELECT RAISE(ABORT, 'test failure'); END");
        try {
            app(PaymentOrderService::class)->confirm($order->public_id, 3);
            $this->fail('Audit failure must rollback credit.');
        } catch (QueryException) {
            $this->assertEquals(10, DB::table('users')->where('id', 1)->value('diamond'));
            $this->assertSame(PaymentOrderStatus::PENDING, $order->fresh()->status);
            $this->assertNull($order->fresh()->credited_at);
        }
    }

    public function test_minimum_disabled_orders_limit_and_overflow_are_enforced(): void
    {
        try {
            $this->createOrder(diamonds: 1);
            $this->fail('Below minimum');
        } catch (ValidationException) {
            $this->assertSame(0, PaymentOrder::count());
        }
        DB::table('payment_settings')->update(['is_enabled' => false]);
        try {
            $this->createOrder();
            $this->fail('Disabled');
        } catch (ValidationException) {
            $this->assertSame(0, PaymentOrder::count());
        }
        DB::table('payment_settings')->update(['is_enabled' => true]);
        foreach (range(1, 5) as $_) {
            $order = $this->createOrder();
        }
        try {
            $this->createOrder();
            $this->fail('Pending limit');
        } catch (ValidationException) {
            $this->assertSame(5, PaymentOrder::count());
        }
        DB::table('users')->where('id', 1)->update(['diamond' => 2147483640]);
        try {
            app(PaymentOrderService::class)->confirm($order->public_id, 3);
            $this->fail('Balance overflow');
        } catch (ValidationException) {
            $this->assertSame(PaymentOrderStatus::PENDING, $order->fresh()->status);
        }
    }

    public function test_player_cannot_view_cancel_or_poll_another_players_order(): void
    {
        $order = $this->createOrder();
        $this->loginAs(2)->get(route('payments.orders.show', $order->public_id))->assertNotFound();
        $this->getJson(route('payments.orders.status', $order->public_id))->assertNotFound();
        $this->post(route('payments.orders.cancel', $order->public_id))->assertNotFound();
        $this->assertSame(PaymentOrderStatus::PENDING, $order->fresh()->status);
    }

    public function test_player_cannot_confirm_payment_via_admin_endpoint_or_service(): void
    {
        $order = $this->createOrder();
        $this->loginAs(1)->post(route('admin.payment_orders.confirm', $order->public_id), ['confirmed_payment' => 1])->assertRedirect('/');
        try {
            app(PaymentOrderService::class)->confirm($order->public_id, 1);
            $this->fail('Admin required');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $this->assertEquals(10, DB::table('users')->where('id', 1)->value('diamond'));
    }

    public function test_creation_ignores_client_price_and_requires_agreement(): void
    {
        $this->loginAs(1)->postJson(route('payments.orders.store'), ['diamonds' => 77, 'request_id' => (string) Str::uuid()])
            ->assertUnprocessable()->assertJsonValidationErrors('agree');
        $this->post(route('payments.orders.store'), [
            'diamonds' => 77, 'agree' => 1, 'request_id' => (string) Str::uuid(), 'amount_minor' => 1, 'status' => 'paid', 'user_id' => 2,
        ])->assertRedirect(route('payments.orders.show', PaymentOrder::firstOrFail()->public_id));
        $this->assertSame(50050, PaymentOrder::first()->amount_minor);
        $this->assertSame(1, PaymentOrder::first()->user_id);
        $this->assertSame(PaymentOrderStatus::PENDING, PaymentOrder::first()->status);
    }

    public function test_owner_and_admin_pages_render_and_status_reflects_confirmation(): void
    {
        $order = $this->createOrder();
        $this->loginAs(1)->get(route('payments.orders.show', $order->public_id))->assertOk()->assertSee($order->public_id);
        $this->get(route('premium.topup'))->assertOk()->assertSee('top-up-form')->assertSee('Мои ордера')
            ->assertSee('PaymentUser1')->assertDontSee('Aizen')
            ->assertSee(route('info.user', ['id' => 1]))
            ->assertSee('img/icon/users-arrow.gif')->assertSee('main/images/player_info.gif');
        $this->loginAs(3)->get(route('admin.payment_orders.index'))->assertOk()->assertSee('ручное подтверждение', false);
        $this->get(route('admin.payment_orders.show', $order->public_id))->assertOk()->assertSee('Подтвердить оплату');
        $this->post(route('admin.payment_orders.confirm', $order->public_id), ['confirmed_payment' => 1])->assertRedirect();
        $this->loginAs(1)->getJson(route('payments.orders.status', $order->public_id))->assertOk()->assertJson(['status' => 'paid', 'diamond' => 87]);
    }

    public function test_admin_must_acknowledge_payment_and_rejection_requires_reason(): void
    {
        $order = $this->createOrder();
        $this->loginAs(3)->postJson(route('admin.payment_orders.confirm', $order->public_id), [])
            ->assertUnprocessable()->assertJsonValidationErrors('confirmed_payment');
        $this->postJson(route('admin.payment_orders.reject', $order->public_id), [])
            ->assertUnprocessable()->assertJsonValidationErrors('note');
        $this->assertSame(PaymentOrderStatus::PENDING, $order->fresh()->status);
        $this->assertEquals(10, DB::table('users')->where('id', 1)->value('diamond'));
    }

    public function test_confirmation_and_creation_require_valid_csrf(): void
    {
        $this->app['env'] = 'local';
        $order = $this->createOrder();
        $this->loginAs(3)->withHeaders(['X-CSRF-TOKEN' => 'wrong'])
            ->postJson(route('admin.payment_orders.confirm', $order->public_id), ['confirmed_payment' => 1])->assertStatus(419);
        $this->loginAs(1)->withHeaders(['X-CSRF-TOKEN' => 'wrong'])
            ->postJson(route('payments.orders.store'), ['diamonds' => 77, 'agree' => 1, 'request_id' => (string) Str::uuid()])->assertStatus(419);
        $this->assertEquals(10, DB::table('users')->where('id', 1)->value('diamond'));
    }

    private function enableHeleket(): void
    {
        config(['services.heleket' => [
            'merchant' => 'merchant-uuid', 'api_key' => 'secret-key', 'base_url' => 'https://api.heleket.test',
            'lifetime' => 3600, 'webhook_ips' => [],
        ]]);
    }

    /** @param  array<string, mixed>  $data */
    private function sendWebhook(array $data): TestResponse
    {
        $data['sign'] = md5(base64_encode((string) json_encode($data, JSON_UNESCAPED_UNICODE)).'secret-key');

        return $this->call('POST', route('payments.heleket.webhook'), [], [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode($data));
    }

    public function test_heleket_invoice_is_signed_and_reused(): void
    {
        $this->enableHeleket();
        Http::fake(['api.heleket.test/*' => Http::response(['state' => 0, 'result' => ['uuid' => 'inv-1', 'url' => 'https://pay.heleket.test/inv-1']])]);
        $order = $this->createOrder();
        $this->assertSame('heleket', $order->provider);

        $this->loginAs(1)->post(route('payments.orders.pay', $order->public_id))->assertRedirect('https://pay.heleket.test/inv-1');
        $this->loginAs(1)->post(route('payments.orders.pay', $order->public_id))->assertRedirect('https://pay.heleket.test/inv-1');

        Http::assertSentCount(1);
        Http::assertSent(function ($request) use ($order): bool {
            $body = $request->body();

            return $request->url() === 'https://api.heleket.test/v1/payment'
                && $request->header('merchant')[0] === 'merchant-uuid'
                && $request->header('sign')[0] === md5(base64_encode($body).'secret-key')
                && $request['amount'] === '500.50' && $request['currency'] === 'USD' && $request['order_id'] === $order->public_id;
        });
        $this->assertSame('inv-1', $order->fresh()->provider_payment_id);
    }

    public function test_heleket_paid_webhook_credits_once(): void
    {
        $this->enableHeleket();
        $order = $this->createOrder();
        $payload = ['type' => 'payment', 'uuid' => 'inv-1', 'order_id' => $order->public_id, 'amount' => '500.50',
            'currency' => 'USD', 'payment_amount' => '500.50', 'payer_currency' => 'USDT', 'status' => 'paid', 'txid' => 'abc/def'];

        $this->sendWebhook($payload)->assertOk();
        $this->sendWebhook($payload)->assertOk();

        $this->assertSame(PaymentOrderStatus::PAID, $order->fresh()->status);
        $this->assertSame('paid', $order->fresh()->provider_status);
        $this->assertEquals(87, DB::table('users')->where('id', 1)->value('diamond'));
    }

    public function test_heleket_webhook_rejects_bad_sign_wrong_amount_and_closes_cancelled(): void
    {
        $this->enableHeleket();
        $order = $this->createOrder();
        $payload = ['type' => 'payment', 'uuid' => 'inv-1', 'order_id' => $order->public_id, 'amount' => '500.50', 'currency' => 'USD', 'status' => 'paid'];

        $this->call('POST', route('payments.heleket.webhook'), [], [], [], ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode($payload + ['sign' => 'forged']))->assertForbidden();
        $this->sendWebhook(['amount' => '1.00'] + $payload)->assertOk();
        $this->assertSame(PaymentOrderStatus::PENDING, $order->fresh()->status);

        $this->sendWebhook(['status' => 'cancel'] + $payload)->assertOk();
        $this->assertSame(PaymentOrderStatus::CANCELLED, $order->fresh()->status);
        $this->assertEquals(10, DB::table('users')->where('id', 1)->value('diamond'));

        // Игрок мог отменить ордер, но всё же оплатить — деньги получены, начисляем.
        $this->sendWebhook($payload)->assertOk();
        $this->assertSame(PaymentOrderStatus::PAID, $order->fresh()->status);
    }

    public function test_heleket_webhook_rejects_foreign_ip(): void
    {
        $this->enableHeleket();
        config(['services.heleket.webhook_ips' => ['31.133.220.8']]);
        $this->sendWebhook(['type' => 'payment', 'order_id' => 'x'])->assertForbidden();
    }

    private ?\OpenSSLAsymmetricKey $monoKey = null;

    private function enableMonobank(): void
    {
        config(['services.monobank' => ['token' => 'mono-token', 'base_url' => 'https://api.mono.test', 'uah_per_usd' => 40.0, 'validity' => 3600,
            'nbu_url' => 'https://nbu.test/exchange']]);
        $this->monoKey = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
        $pem = openssl_pkey_get_details($this->monoKey)['key'];
        Http::fake([
            'api.mono.test/api/merchant/pubkey' => Http::response(['key' => base64_encode($pem)]),
            'api.mono.test/api/merchant/invoice/create' => Http::response(['invoiceId' => 'mono-inv-1', 'pageUrl' => 'https://pay.mbnk.test/mono-inv-1']),
        ]);
        DB::table('exchange_rates')->insert(['rate_date' => now('Europe/Kyiv')->toDateString(), 'currency' => 'USD', 'rate' => 42.0, 'source' => 'nbu']);
    }

    /** @param  array<string, mixed>  $data */
    private function sendMonoWebhook(array $data, ?string $sign = null): TestResponse
    {
        $body = (string) json_encode($data);
        openssl_sign($body, $signature, $this->monoKey, OPENSSL_ALGO_SHA256);

        return $this->call('POST', route('payments.monobank.webhook'), [], [], [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X_SIGN' => $sign ?? base64_encode($signature)], $body);
    }

    public function test_monobank_invoice_is_created_in_uah_and_reused(): void
    {
        $this->enableMonobank();
        $order = $this->createOrder();
        $this->assertSame('monobank', $order->provider);

        $this->loginAs(1)->post(route('payments.orders.pay', $order->public_id), ['method' => 'monobank'])->assertRedirect('https://pay.mbnk.test/mono-inv-1');
        $this->loginAs(1)->post(route('payments.orders.pay', $order->public_id))->assertRedirect('https://pay.mbnk.test/mono-inv-1');

        Http::assertSentCount(1);
        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.mono.test/api/merchant/invoice/create'
            && $request->header('X-Token')[0] === 'mono-token'
            && $request['amount'] === 2102100 && $request['ccy'] === 980
            && $request['merchantPaymInfo']['reference'] === $order->public_id
            && $request['webHookUrl'] === route('payments.monobank.webhook'));
        $fresh = $order->fresh();
        $this->assertSame('mono-inv-1', $fresh->provider_payment_id);
        $this->assertSame(2102100, $fresh->charge_amount_minor);
        $this->assertSame('UAH', $fresh->charge_currency);
        $this->assertSame(42.0, $fresh->charge_rate);
        $this->loginAs(3)->get(route('admin.payment_orders.show', $order->public_id))->assertOk()
            ->assertSee('21 021.00 UAH')->assertSee('курс НБУ 42.0000');
    }

    public function test_provider_can_be_switched_only_before_invoice(): void
    {
        $this->enableMonobank();
        $this->enableHeleket();
        $order = $this->createOrder();
        $this->assertSame('heleket', $order->provider);
        $this->loginAs(1)->get(route('payments.orders.show', $order->public_id))->assertOk()
            ->assertSee('21 021.00 UAH')->assertSee(__('payments.pay_card'))->assertSee(__('payments.pay_crypto'));

        $this->loginAs(1)->post(route('payments.orders.pay', $order->public_id), ['method' => 'monobank'])->assertRedirect('https://pay.mbnk.test/mono-inv-1');
        $this->assertSame('monobank', $order->fresh()->provider);

        $this->loginAs(1)->post(route('payments.orders.pay', $order->public_id), ['method' => 'heleket'])->assertSessionHasErrors('order');
        $this->assertSame('monobank', $order->fresh()->provider);
        $this->loginAs(1)->get(route('payments.orders.show', $order->public_id))->assertOk()
            ->assertSee(__('payments.pay_card'))->assertDontSee(__('payments.pay_crypto'));
    }

    public function test_monobank_success_webhook_credits_once_and_rejects_forgery(): void
    {
        $this->enableMonobank();
        $order = $this->createOrder();
        $this->loginAs(1)->post(route('payments.orders.pay', $order->public_id), ['method' => 'monobank']);
        $payload = ['invoiceId' => 'mono-inv-1', 'status' => 'success', 'amount' => 2102100, 'ccy' => 980, 'finalAmount' => 2102100, 'reference' => $order->public_id];

        $this->sendMonoWebhook($payload, base64_encode('forged'))->assertForbidden();
        $this->sendMonoWebhook(['amount' => 100] + $payload)->assertOk();
        $this->assertSame(PaymentOrderStatus::PENDING, $order->fresh()->status);

        $this->sendMonoWebhook($payload)->assertOk();
        $this->sendMonoWebhook($payload)->assertOk();
        // Запоздалый промежуточный статус не трогает оплаченный ордер.
        $this->sendMonoWebhook(['status' => 'processing'] + $payload)->assertOk();

        $fresh = $order->fresh();
        $this->assertSame(PaymentOrderStatus::PAID, $fresh->status);
        $this->assertSame('success', $fresh->provider_status);
        $this->assertEquals(87, DB::table('users')->where('id', 1)->value('diamond'));
    }

    public function test_monobank_failure_closes_order(): void
    {
        $this->enableMonobank();
        $order = $this->createOrder();
        $this->loginAs(1)->post(route('payments.orders.pay', $order->public_id), ['method' => 'monobank']);

        $this->sendMonoWebhook(['invoiceId' => 'mono-inv-1', 'status' => 'failure', 'amount' => 2102100, 'ccy' => 980, 'reference' => $order->public_id])->assertOk();

        $this->assertSame(PaymentOrderStatus::CANCELLED, $order->fresh()->status);
        $this->assertEquals(10, DB::table('users')->where('id', 1)->value('diamond'));
    }

    public function test_nbu_rate_is_synced_to_history_and_used_without_requests(): void
    {
        config(['services.monobank.nbu_url' => 'https://nbu.test/exchange', 'services.monobank.uah_per_usd' => 40.0]);
        $rates = app(NbuExchangeRate::class);
        $this->assertSame(40.0, $rates->usdToUah()); // истории нет — запасной курс

        $ok = [['r030' => 840, 'rate' => 44.9293, 'cc' => 'USD']];
        Http::fake(['nbu.test/*' => Http::sequence()->push($ok)->push($ok)->whenEmpty(Http::response('down', 500))]);
        $this->artisan('payments:sync-nbu-rate')->assertSuccessful();
        $this->artisan('payments:sync-nbu-rate')->assertSuccessful(); // повтор за тот же день не дублирует

        $this->assertSame(1, DB::table('exchange_rates')->count());
        $this->assertSame(44.9293, $rates->usdToUah());
        Http::assertSent(fn ($request): bool => str_contains($request->url(), 'valcode=USD') && str_contains($request->url(), 'date='.now('Europe/Kyiv')->format('Ymd')));

        $this->expectException(\RuntimeException::class);
        $rates->sync();
    }

    public function test_admin_sees_rate_history(): void
    {
        DB::table('exchange_rates')->insert([
            ['rate_date' => now('Europe/Kyiv')->subDay()->toDateString(), 'currency' => 'USD', 'rate' => 44.5, 'source' => 'nbu'],
            ['rate_date' => now('Europe/Kyiv')->toDateString(), 'currency' => 'USD', 'rate' => 44.9293, 'source' => 'nbu'],
        ]);
        $this->loginAs(3)->get(route('admin.payment_orders.index'))->assertOk()
            ->assertSee('44.9293 грн')->assertSee('+0.4293')->assertSee('История курсов');
    }

    public function test_admin_can_sync_rate_manually_and_player_cannot(): void
    {
        config(['services.monobank.nbu_url' => 'https://nbu.test/exchange']);
        Http::fake(['nbu.test/*' => Http::response([['r030' => 840, 'rate' => 45.0532, 'cc' => 'USD']])]);

        $this->loginAs(1)->post(route('admin.payment_orders.sync_rate'))->assertRedirect(); // не админ — уводит из админки
        $this->assertSame(0, DB::table('exchange_rates')->count());

        $this->loginAs(3)->post(route('admin.payment_orders.sync_rate'))
            ->assertRedirect(route('admin.payment_orders.index'))->assertSessionHas('message');
        $this->assertEquals(45.0532, DB::table('exchange_rates')->value('rate'));
        $this->get(route('admin.payment_orders.index'))->assertSee('Обновить курс НБУ сейчас');
    }
}
