<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Structure\Prison;

use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Prison\Application\Services\DetentionService;
use App\Modules\Structure\Prison\Infrastructure\Persistence\Models\PlayerDetention;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DetentionServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->unsignedBigInteger('player_id')->nullable();
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('prev_location_id')->default(1);
            $table->unsignedBigInteger('money')->default(0);
            $table->decimal('diamond', 12, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('race_id')->nullable();
            $table->unsignedInteger('lvl')->default(1);
        });
        Schema::create('races', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
        });
        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
        });
        Schema::create('structures', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('type')->nullable();
            $table->string('name')->nullable();
        });
        Schema::create('player_detentions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('prison_structure_id')->nullable();
            $table->unsignedBigInteger('prison_location_id');
            $table->string('prison_name');
            $table->unsignedBigInteger('imposed_by_user_id')->nullable();
            $table->string('user_name');
            $table->string('imposed_by_name')->nullable();
            $table->unsignedBigInteger('previous_location_id')->nullable();
            $table->text('reason');
            $table->unsignedBigInteger('fine_money')->default(0);
            $table->decimal('fine_diamond', 12, 2)->default(0);
            $table->timestamp('starts_at');
            $table->timestamp('release_at');
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_detention_uses_virtual_prison_context_and_preserves_player_location(): void
    {
        DB::table('users')->insert([
            ['id' => 10, 'name' => 'Player', 'player_id' => 10, 'location_id' => 5, 'money' => 0, 'diamond' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 20, 'name' => 'Admin', 'player_id' => 20, 'location_id' => 1, 'money' => 0, 'diamond' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('players')->insert([['id' => 10, 'race_id' => 1], ['id' => 20, 'race_id' => 1]]);
        DB::table('races')->insert(['id' => 1, 'name' => 'Race']);
        DB::table('locations')->insert(['id' => 2, 'name' => 'Prison']);
        DB::table('structures')->insert(['id' => 7, 'location_id' => 2, 'type' => Structure::TYPE_PRISON, 'name' => 'Темница']);

        $user = User::query()->findOrFail(10);
        $admin = User::query()->findOrFail(20);
        $prison = Structure::query()->findOrFail(7);
        $service = new DetentionService;

        $detention = $service->impose($user, $prison, $admin, 60, 'Тестовое наказание');

        $this->assertSame(5, (int) $user->fresh()->location_id);
        $this->assertSame('Player', $detention->user_name);

        $this->assertNotNull($service->blockLeaving($user));
        $this->assertSame(5, (int) $user->fresh()->location_id);
        $this->assertSame(1, PlayerDetention::query()->count());

        $this->travel(61)->minutes();
        $this->assertTrue($service->releaseIfExpired($user));
        $this->assertSame(5, (int) $user->fresh()->location_id);
        $this->assertNull($service->activeFor($user));
    }

    public function test_buyout_cost_decreases_with_remaining_term_and_releases_player(): void
    {
        DB::table('users')->insert([
            ['id' => 10, 'name' => 'Player', 'player_id' => 10, 'location_id' => 5, 'money' => 1000, 'diamond' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 20, 'name' => 'Admin', 'player_id' => 20, 'location_id' => 1, 'money' => 0, 'diamond' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('players')->insert([['id' => 10, 'race_id' => 1], ['id' => 20, 'race_id' => 1]]);
        DB::table('races')->insert(['id' => 1, 'name' => 'Race']);
        DB::table('locations')->insert(['id' => 2, 'name' => 'Prison']);
        DB::table('structures')->insert(['id' => 7, 'location_id' => 2, 'type' => Structure::TYPE_PRISON, 'name' => 'Темница']);

        $user = User::query()->findOrFail(10);
        $admin = User::query()->findOrFail(20);
        $service = new DetentionService;
        $detention = $service->impose(User::query()->findOrFail(10), Structure::query()->findOrFail(7), $admin, 60, 'Тест', 600, 6);

        $this->travel(30)->minutes();
        $this->assertSame(['money' => 300, 'diamond' => 3.0], $detention->fresh()->currentBuyoutAmounts());

        $paid = $service->buyOut($user, 7);

        $this->assertSame(['money' => 300, 'diamond' => 3.0], $paid);
        $this->assertSame(700, (int) $user->fresh()->money);
        $this->assertSame(7.0, (float) $user->fresh()->diamond);
        $this->assertNotNull($detention->fresh()->released_at);
        $this->assertNull($service->activeFor($user));
    }
}
