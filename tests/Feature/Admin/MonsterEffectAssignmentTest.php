<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Middleware\AdminMiddleware;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MonsterEffectAssignmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('monsters', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('effects', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type');
            $table->string('active_type')->nullable();
            $table->json('stat_modifiers')->nullable();
            $table->timestamps();
        });
        Schema::create('monster_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('monster_id');
            $table->unsignedBigInteger('effect_id');
            $table->decimal('chance', 5, 2)->default(0);
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->decimal('power_percent', 5, 2)->nullable();
            $table->boolean('trigger_on_hit')->default(false);
            $table->timestamps();
        });

        DB::table('monsters')->insert(['id' => 10, 'name' => 'Волк']);
        DB::table('effects')->insert([
            'id' => 20,
            'name' => 'Кровотечение',
            'slug' => 'monster_bleed',
            'type' => 'debuff',
            'active_type' => 'bleed',
        ]);

        $this->withoutMiddleware(AdminMiddleware::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_admin_can_assign_and_update_a_signature_effect(): void
    {
        $this->post(route('admin.monster.effect.add', 10), [
            'effect_id' => 20,
            'chance' => 12,
            'duration_seconds' => 4,
            'power_percent' => 7,
            'trigger_on_hit' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('monster_effects', [
            'monster_id' => 10,
            'effect_id' => 20,
            'chance' => 12,
            'duration_seconds' => 4,
            'power_percent' => 7,
        ]);

        $this->post(route('admin.monster.effect.update', [10, 20]), [
            'chance' => 18,
            'duration_seconds' => 7,
            'power_percent' => 8,
        ])->assertRedirect();

        $this->assertDatabaseHas('monster_effects', [
            'monster_id' => 10,
            'effect_id' => 20,
            'chance' => 18,
            'duration_seconds' => 7,
            'power_percent' => 8,
        ]);
        $this->assertSame(1, DB::table('monster_effects')->count());
    }
}
