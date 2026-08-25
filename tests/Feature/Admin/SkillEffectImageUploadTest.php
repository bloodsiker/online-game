<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Middleware\AdminMiddleware;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SkillEffectImageUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('level')->default(1);
            $table->string('type');
            $table->string('target_type');
            $table->integer('mana_cost')->default(0);
            $table->integer('min_damage')->default(0);
            $table->integer('max_damage')->default(0);
            $table->float('power_coefficient')->default(0);
            $table->integer('base_healing')->default(0);
            $table->integer('cooldown')->default(0);
            $table->boolean('is_passive')->default(false);
            $table->json('effects')->nullable();
            $table->timestamps();
        });
        Schema::create('effects', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type');
            $table->string('active_type')->nullable();
            $table->string('damage_scaling_type')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('chance')->default(0);
            $table->boolean('is_stackable')->default(false);
            $table->integer('max_stacks')->default(1);
            $table->integer('tick_interval')->default(1);
            $table->integer('value_per_tick')->nullable();
            $table->json('stat_modifiers')->nullable();
            $table->boolean('is_dispellable')->default(true);
            $table->timestamps();
        });
        Schema::create('magic_skill_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('magic_skill_id');
            $table->unsignedBigInteger('effect_id');
            $table->integer('chance')->default(100);
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->timestamps();
        });

        Storage::fake('public');
        $this->withoutMiddleware(AdminMiddleware::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_admin_uploads_an_image_for_a_magic_skill(): void
    {
        $this->post(route('admin.magic_skill.create'), [
            'name' => 'Огненная искра',
            'slug' => 'fire-spark-image-test',
            'type' => 'attack',
            'target_type' => 'enemy',
            'image' => UploadedFile::fake()->image('fire-spark.png', 64, 64),
        ])->assertRedirect();

        $path = DB::table('magic_skills')->value('image');

        $this->assertIsString($path);
        $this->assertStringStartsWith('magic-skills/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_admin_uploads_an_image_for_an_effect(): void
    {
        $this->post(route('admin.effect.create'), [
            'name' => 'Кровотечение',
            'slug' => 'bleed-image-test',
            'type' => 'debuff',
            'active_type' => 'bleed',
            'damage_scaling_type' => 'target_max_hp',
            'image' => UploadedFile::fake()->image('bleed.png', 64, 64),
        ])->assertRedirect();

        $path = DB::table('effects')->value('image');

        $this->assertIsString($path);
        $this->assertStringStartsWith('effects/', $path);
        $this->assertSame('target_max_hp', DB::table('effects')->value('damage_scaling_type'));
        Storage::disk('public')->assertExists($path);
    }

    public function test_admin_allows_an_effect_without_damage_scaling_and_with_zero_tick_interval(): void
    {
        $this->post(route('admin.effect.create'), [
            'name' => 'Оглушение',
            'slug' => 'stun-no-ticks-test',
            'type' => 'debuff',
            'tick_interval' => 0,
        ])->assertRedirect();

        $effect = DB::table('effects')->where('slug', 'stun-no-ticks-test')->first();

        $this->assertNull($effect->damage_scaling_type);
        $this->assertSame(0, $effect->tick_interval);
    }

    public function test_admin_stores_magic_effect_duration_on_the_skill_assignment(): void
    {
        $skillId = DB::table('magic_skills')->insertGetId([
            'name' => 'Огненная искра',
            'slug' => 'fire-spark-duration-test',
            'type' => 'attack',
            'target_type' => 'enemy',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $effectId = DB::table('effects')->insertGetId([
            'name' => 'Ожог',
            'slug' => 'burn-duration-test',
            'type' => 'debuff',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->post(route('admin.magic_skill.effect.add', $skillId), [
            'effect_id' => $effectId,
            'chance' => 80,
            'duration_seconds' => 6,
        ])->assertRedirect();

        $this->assertDatabaseHas('magic_skill_effects', [
            'magic_skill_id' => $skillId,
            'effect_id' => $effectId,
            'chance' => 80,
            'duration_seconds' => 6,
        ]);

        $this->post(route('admin.magic_skill.effect.update', [$skillId, $effectId]), [
            'chance' => 65,
            'duration_seconds' => 9,
        ])->assertRedirect();

        $this->assertDatabaseHas('magic_skill_effects', [
            'magic_skill_id' => $skillId,
            'effect_id' => $effectId,
            'chance' => 65,
            'duration_seconds' => 9,
        ]);
    }
}
