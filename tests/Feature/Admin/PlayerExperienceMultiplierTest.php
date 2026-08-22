<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Middleware\AdminMiddleware;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PlayerExperienceMultiplierTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::connection('sqlite')->create('users', function (Blueprint $table): void {
            $table->id();
            $table->integer('money')->default(0);
            $table->integer('diamond')->default(0);
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('players', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('lvl')->default(1);
            $table->bigInteger('exp')->default(0);
            $table->decimal('experience_multiplier', 8, 4)->default(1);
            $table->integer('hp_now')->default(10);
            $table->integer('hp_max')->default(10);
            $table->integer('mp_now')->default(0);
            $table->integer('mp_max')->default(0);
            $table->float('strength')->default(1);
            $table->float('agility')->default(1);
            $table->float('intuition')->default(1);
            $table->float('wisdom')->default(1);
            $table->float('intelligence')->default(1);
            $table->float('min_dmg')->default(1);
            $table->float('max_dmg')->default(2);
            $table->integer('free_stats')->default(0);
            $table->timestamps();
        });

        DB::table('users')->insert(['id' => 1, 'money' => 0, 'diamond' => 0]);
        DB::table('players')->insert(['id' => 1, 'user_id' => 1]);

        $this->withoutMiddleware(AdminMiddleware::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_admin_can_update_player_experience_multiplier(): void
    {
        $response = $this->post(route('admin.player.info', 1), $this->playerPayload([
            'experience_multiplier' => '1.5000',
        ]));

        $response->assertRedirect();
        $this->assertSame(1.5, Player::findOrFail(1)->experience_multiplier);
    }

    public function test_admin_cannot_set_negative_experience_multiplier(): void
    {
        $response = $this->from(route('admin.player.info', 1))
            ->post(route('admin.player.info', 1), $this->playerPayload([
                'experience_multiplier' => '-0.5',
            ]));

        $response->assertRedirect(route('admin.player.info', 1));
        $response->assertSessionHasErrors('experience_multiplier');
        $this->assertSame(1.0, Player::findOrFail(1)->experience_multiplier);
    }

    /** @param  array<string, mixed>  $overrides */
    private function playerPayload(array $overrides = []): array
    {
        return array_replace([
            'lvl' => 1,
            'exp' => 0,
            'experience_multiplier' => '1.0000',
            'hp_now' => 10,
            'hp_max' => 10,
            'mp_now' => 0,
            'mp_max' => 0,
            'strength' => 1,
            'agility' => 1,
            'intuition' => 1,
            'wisdom' => 1,
            'intelligence' => 1,
            'min_dmg' => 1,
            'max_dmg' => 2,
            'free_stats' => 0,
            'money' => 0,
            'diamond' => 0,
        ], $overrides);
    }
}
