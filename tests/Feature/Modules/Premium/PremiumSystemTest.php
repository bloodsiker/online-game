<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Premium;

use App\Modules\Player\Domain\Services\ExperienceService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Premium\Application\Requests\SavePremiumRequest;
use App\Modules\Premium\Application\Services\ExchangeDiamonds;
use App\Modules\Premium\Application\Services\ManagePremium;
use App\Modules\Premium\Application\Services\PremiumBonusService;
use App\Modules\Premium\Domain\Enums\PremiumBonus;
use App\Modules\Premium\Infrastructure\Persistence\Models\PremiumLevel;
use App\Modules\Premium\Infrastructure\Persistence\Models\PremiumPeriod;
use App\Modules\Premium\Presentation\Http\PremiumController;
use App\Modules\Structure\Auction\Application\UseCases\CreateLot;
use App\Modules\Structure\Auction\Application\UseCases\CreateOrder;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class PremiumSystemTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
            $this->markTestSkipped('Только SQLite :memory:, без миграций основной базы.');
        }
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->integer('diamond');
            $table->unsignedBigInteger('money');
            $table->unsignedBigInteger('location_id')->default(10);
        });
        DB::table('users')->insert(['id' => 1, 'name' => 'PremiumTest', 'diamond' => 1000, 'money' => 10]);
        (require database_path('migrations/2026_10_21_000002_create_premium_system.php'))->up();
        app(ManagePremium::class)->execute($this->configuration());
    }

    private function configuration(): array
    {
        $levels = [];
        foreach (range(1, 5) as $level) {
            $bonuses = [];
            foreach (PremiumBonus::cases() as $bonus) {
                $bonuses[$bonus->value] = $bonus->isBoolean() ? (int) ($level >= 2) : $level * 10;
            }
            $levels[$level] = ['threshold' => (string) ($level * 10), 'bonuses' => $bonuses];
        }

        return ['gold_per_diamond' => 100, 'levels' => $levels];
    }

    private function exchange(string $amount, ?string $requestId = null): object
    {
        return app(ExchangeDiamonds::class)->execute(1, $amount, $requestId ?? (string) Str::uuid());
    }

    public function test_first_exchange_starts_thirty_days_and_higher_levels_do_not_extend_period(): void
    {
        $this->travelTo(now()->startOfSecond());
        $this->exchange('10');
        $period = PremiumPeriod::firstOrFail();
        $this->assertEquals(now()->addDays(30), $period->expires_at);
        $bonuses = app(PremiumBonusService::class);
        $this->assertSame(1, $bonuses->level(1)->level);
        $this->assertSame(10.0, $bonuses->value(1, PremiumBonus::EXPERIENCE));
        $this->travel(5)->days();
        $this->exchange('10');
        $this->assertSame(2, $bonuses->level(1)->level);
        $this->assertSame(20.0, $bonuses->value(1, PremiumBonus::EXPERIENCE));
        $this->assertTrue($period->expires_at->equalTo($period->fresh()->expires_at));
        $this->assertSame(1, PremiumPeriod::count());
    }

    public function test_expiry_disables_every_bonus_and_next_exchange_creates_new_period(): void
    {
        $this->travelTo(now()->startOfSecond());
        $this->exchange('50');
        $bonuses = app(PremiumBonusService::class);
        $this->assertSame(5, $bonuses->level(1)->level);
        $this->travel(30)->days();
        foreach (PremiumBonus::cases() as $bonus) {
            $this->assertSame(0.0, $bonuses->value(1, $bonus));
        }
        $this->assertNull($bonuses->period(1));
        $this->exchange('10');
        $this->assertSame(2, PremiumPeriod::count());
        $this->assertSame(1, $bonuses->level(1)->level);
        $this->assertSame(1000, $bonuses->period(1)->diamond_cents);
    }

    public function test_exchange_is_idempotent_and_balances_are_exact(): void
    {
        $id = (string) Str::uuid();
        $first = $this->exchange('10', $id);
        $second = $this->exchange('10', $id);
        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, DB::table('premium_exchanges')->count());
        $this->assertEquals(990, DB::table('users')->value('diamond'));
        $this->assertEquals(1010, DB::table('users')->value('money'));
        $this->assertSame(1000, PremiumPeriod::first()->diamond_cents);
    }

    public function test_failed_exchange_does_not_change_balances_or_create_period(): void
    {
        try {
            $this->exchange('2000');
            $this->fail('Insufficient balance must fail.');
        } catch (ValidationException) {
            $this->assertEquals(1000, DB::table('users')->value('diamond'));
            $this->assertEquals(10, DB::table('users')->value('money'));
            $this->assertSame(0, PremiumPeriod::count());
            $this->assertSame(0, DB::table('premium_exchanges')->count());
        }
    }

    public function test_unconfigured_exchange_is_disabled(): void
    {
        PremiumLevel::whereKey(5)->update(['required_diamond_cents' => null]);
        $this->expectException(ValidationException::class);
        $this->exchange('10');
    }

    public function test_fractional_diamonds_and_money_overflow_are_rejected_without_charges(): void
    {
        foreach (['0', '0.5', '10.25', '-1', '1e3'] as $amount) {
            try {
                $this->exchange($amount);
                $this->fail('Invalid amount accepted: '.$amount);
            } catch (ValidationException) {
                $this->assertEquals(1000, DB::table('users')->value('diamond'));
                $this->assertSame(0, PremiumPeriod::count());
            }
        }
        DB::table('users')->where('id', 1)->update(['money' => 2147483640]);
        try {
            $this->exchange('1');
            $this->fail('Overflow must be rejected.');
        } catch (ValidationException) {
            $this->assertEquals(2147483640, DB::table('users')->value('money'));
            $this->assertEquals(1000, DB::table('users')->value('diamond'));
        }
    }

    public function test_reward_failure_rolls_back_money_and_progress(): void
    {
        DB::statement("CREATE TRIGGER reject_premium_exchange BEFORE INSERT ON premium_exchanges BEGIN SELECT RAISE(ABORT, 'test failure'); END");
        try {
            $this->exchange('10');
            $this->fail('Missing log must rollback.');
        } catch (QueryException) {
            $this->assertEquals(1000, DB::table('users')->value('diamond'));
            $this->assertSame(0, PremiumPeriod::count());
        }
    }

    public function test_admin_rejects_unordered_thresholds_and_invalid_bonus_values(): void
    {
        $data = $this->configuration();
        $request = SavePremiumRequest::create('/admin/premium', 'POST', $data);
        $validator = Validator::make($data, $request->rules());
        foreach ($request->after() as $after) {
            $validator->after($after);
        }
        $this->assertTrue($validator->passes(), json_encode($validator->errors()->toArray()));
        $data['levels'][2]['threshold'] = '5';
        $request = SavePremiumRequest::create('/admin/premium', 'POST', $data);
        $validator = Validator::make($data, $request->rules());
        foreach ($request->after() as $after) {
            $validator->after($after);
        }
        $this->assertTrue($validator->fails());
        $data = $this->configuration();
        $data['levels'][1]['bonuses']['bonus_resource_chance_percent'] = 101;
        $data['levels'][1]['bonuses']['bag_slots'] = 1.5;
        $data['levels'][1]['bonuses']['anonymous_auction'] = 2;
        $validator = Validator::make($data, (new SavePremiumRequest)->rules());
        $this->assertTrue($validator->fails());
        $this->assertCount(3, $validator->errors());
    }

    public function test_admin_changes_apply_to_active_premium_and_anonymity_rights_expire(): void
    {
        $this->exchange('20');
        $bonuses = app(PremiumBonusService::class);
        $this->assertSame(1.0, $bonuses->value(1, PremiumBonus::ANONYMOUS_AUCTION));
        $data = $this->configuration();
        $data['levels'][2]['bonuses']['bag_slots'] = 77;
        app(ManagePremium::class)->execute($data);
        $bonuses->forget(1);
        $this->assertSame(77.0, $bonuses->value(1, PremiumBonus::BAG_SLOTS));
        $this->travel(31)->days();
        $this->assertSame(0.0, $bonuses->value(1, PremiumBonus::ANONYMOUS_AUCTION));
    }

    public function test_experience_bonus_multiplies_existing_gain(): void
    {
        Schema::create('player_active_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->string('type');
            $table->timestamp('expires_at')->nullable();
            $table->integer('current_value');
        });
        $this->exchange('10');
        $player = (new Player)->forceFill(['id' => 9, 'user_id' => 1, 'experience_multiplier' => 1.5]);
        $this->assertSame(165, app(ExperienceService::class)->calculateGain($player, 100));
        $this->travel(31)->days();
        $this->assertSame(150, app(ExperienceService::class)->calculateGain($player, 100));
    }

    public function test_bag_slots_expire_without_modifying_base_capacity(): void
    {
        $this->exchange('10');
        $player = (new Player)->setRelation('playerEquip', (object) ['bagFirstSlot' => null, 'bagSecondSlot' => null]);
        $user = (new User)->forceFill(['id' => 1, 'bag_count' => 25])->setRelation('player', $player);
        $this->assertSame(35, $user->getBagCount());
        $this->assertSame(25, $user->bag_count);
        $this->travel(31)->days();
        $this->assertSame(25, $user->getBagCount());
    }

    public function test_bonus_can_be_disabled_per_level_without_losing_its_value(): void
    {
        $data = $this->configuration();
        $data['levels'][1]['bonus_enabled']['experience_percent'] = 0;
        app(ManagePremium::class)->execute($data);
        $this->exchange('10');
        $bonuses = app(PremiumBonusService::class);
        $this->assertSame(0.0, $bonuses->value(1, PremiumBonus::EXPERIENCE));
        $this->assertSame(10.0, $bonuses->value(1, PremiumBonus::MONSTER_MONEY));
        $this->assertSame(10, PremiumLevel::find(1)->bonuses['experience_percent']);
        $this->assertTrue(PremiumLevel::find(2)->bonusEnabled(PremiumBonus::EXPERIENCE));
        $request = Request::create('/premium/status');
        $request->setUserResolver(fn () => (new User)->forceFill(['id' => 1, 'money' => 10, 'diamond' => 100]));
        view()->share('errors', new ViewErrorBag);
        $html = app(PremiumController::class)->index($request, $bonuses)->render();
        $this->assertSame(4, substr_count($html, __('premium.bonus.experience_percent')));
        $data['levels'][1]['bonus_enabled']['experience_percent'] = 1;
        app(ManagePremium::class)->execute($data);
        $bonuses->forget(1);
        $this->assertSame(10.0, $bonuses->value(1, PremiumBonus::EXPERIENCE));
    }

    public function test_enabled_bonuses_are_rendered_even_with_zero_value(): void
    {
        $data = $this->configuration();
        $data['levels'][1]['bonuses']['experience_percent'] = 0;
        app(ManagePremium::class)->execute($data);
        $request = Request::create('/premium/status');
        $request->setUserResolver(fn () => (new User)->forceFill(['id' => 1, 'money' => 10, 'diamond' => 100]));
        view()->share('errors', new ViewErrorBag);
        $html = app(PremiumController::class)->index($request, app(PremiumBonusService::class))->render();
        $panel = explode('data-premium-level-panel="2"', explode('data-premium-level-panel="1"', $html)[1])[0];
        $this->assertStringContainsString(__('premium.bonus.experience_percent'), $panel);
        $this->assertStringContainsString(__('premium.bonus.anonymous_auction'), $panel);
        $this->assertStringContainsString(__('premium.bonus.anonymous_exchange'), $panel);
        $this->assertStringContainsString(__('premium.bonus.monster_money_percent'), $panel);
    }

    public function test_anonymous_lot_and_order_cannot_be_created_without_bonus(): void
    {
        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
        });
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
            $table->boolean('is_auction_sellable');
        });
        DB::table('share_items')->insert(['id' => 1, 'name' => '{"ru":"Агат"}', 'is_auction_sellable' => true]);
        $user = (new User)->forceFill(['id' => 1, 'location_id' => 10, 'money' => 10]);
        $structure = (new Structure)->forceFill(['id' => 9, 'location_id' => 10]);
        $lot = app(CreateLot::class)
            ->execute($user, $structure, 100, 1, 1, true);
        $order = app(CreateOrder::class)
            ->execute($user, $structure, 1, 1, 1, true);
        $this->assertFalse($lot->ok);
        $this->assertFalse($order->ok);
        $this->assertSame($lot->message, $order->message);
        $this->assertEquals(10, DB::table('users')->value('money'));
    }

    public function test_frontend_and_admin_render_five_levels_and_current_state(): void
    {
        $this->exchange('20');
        $request = Request::create('/premium/status');
        $request->setUserResolver(fn () => (new User)->forceFill(['id' => 1, 'money' => 2010, 'diamond' => '980.50']));
        view()->share('errors', new ViewErrorBag);
        $view = app(PremiumController::class)->index($request, app(PremiumBonusService::class));
        $html = $view->render();
        $this->assertStringContainsString('premium-track__fill', $html);
        $this->assertStringContainsString('premium/exchange', $html);
        $this->assertSame(5, $view->getData()['levels']->count());
        $this->assertStringContainsString('class="premium-crown level-2"', $html);
        $admin = app(\App\Modules\Premium\Presentation\Http\Admin\PremiumController::class)->index()->render();
        $this->assertStringContainsString('История обменов', $admin);
        $this->assertStringContainsString('levels[5][bonuses][bag_slots]', $admin);
        $this->assertStringContainsString('PremiumTest', $admin);
    }
}
