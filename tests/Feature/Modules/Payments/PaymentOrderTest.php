<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Payments;

use App\Modules\Notification\Domain\Events\PlayerNotificationAvailable;
use App\Modules\Payments\Application\Services\ManagePaymentSettings;
use App\Modules\Payments\Application\Services\PaymentOrderService;
use App\Modules\Payments\Domain\Enums\PaymentOrderStatus;
use App\Modules\Payments\Infrastructure\Persistence\Models\PaymentOrder;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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
}
