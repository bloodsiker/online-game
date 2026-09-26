<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Player;

use App\Modules\MagicSkill\Application\Services\PlayerMagicSkillService;
use App\Modules\Player\Application\Services\HotbarService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class HotbarMagicSkillTest extends TestCase
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
            $table->string('image')->nullable();
            $table->boolean('is_passive')->default(false);
            $table->unsignedInteger('cooldown')->default(0);
            $table->timestamps();
        });
        Schema::create('player_magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('magic_skill_id');
            $table->boolean('is_equipped')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('cooldown_end_at')->nullable();
            $table->timestamps();
        });
        Schema::create('effects', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('magic_skill_effects', function (Blueprint $table): void {
            $table->unsignedBigInteger('magic_skill_id');
            $table->unsignedBigInteger('effect_id');
            $table->unsignedInteger('chance')->default(100);
            $table->unsignedInteger('duration_seconds')->default(0);
        });
        Schema::create('player_slots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedTinyInteger('slot_number');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->timestamps();
        });
    }

    public function test_active_spell_can_be_assigned_and_formatted_for_hotbar(): void
    {
        $player = $this->player();
        DB::table('magic_skills')->insert([
            'id' => 10,
            'name' => 'Ледяная стрела',
            'image' => '/storage/skills/ice.webp',
            'is_passive' => false,
            'cooldown' => 12,
        ]);
        DB::table('player_magic_skills')->insert([
            'player_id' => 7,
            'magic_skill_id' => 10,
            'is_equipped' => false,
            'cooldown_end_at' => now()->addSeconds(8),
        ]);

        $service = new HotbarService;

        self::assertNull($service->setSlot($player, 1, 'skill', 10));

        $player->unsetRelation('hotbarSlots');
        $slot = $service->getSlotsData($player)['slots'][0];

        self::assertFalse($slot['empty']);
        self::assertSame('skill', $slot['entity_type']);
        self::assertSame('Ледяная стрела', $slot['name']);
        self::assertSame(12, $slot['cooldown']);
        self::assertNotNull($slot['cooldown_until']);
    }

    public function test_passive_spell_cannot_be_assigned(): void
    {
        $player = $this->player();
        DB::table('magic_skills')->insert([
            'id' => 11,
            'name' => 'Пассивная мудрость',
            'is_passive' => true,
            'cooldown' => 0,
        ]);
        DB::table('player_magic_skills')->insert([
            'player_id' => 7,
            'magic_skill_id' => 11,
        ]);

        self::assertSame(
            'Заклинание не найдено или является пассивным.',
            (new HotbarService)->setSlot($player, 1, 'skill', 11),
        );
    }

    public function test_spell_on_hotbar_is_available_in_battle_without_equipped_flag(): void
    {
        $player = $this->player();
        DB::table('magic_skills')->insert([
            'id' => 12,
            'name' => 'Огненный шар',
            'is_passive' => false,
            'cooldown' => 5,
        ]);
        DB::table('player_magic_skills')->insert([
            'player_id' => 7,
            'magic_skill_id' => 12,
            'is_equipped' => false,
        ]);
        DB::table('player_slots')->insert([
            'player_id' => 7,
            'slot_number' => 1,
            'entity_type' => 'skill',
            'entity_id' => 12,
        ]);

        $skill = (new PlayerMagicSkillService)->getActiveSkillForBattle($player, 12);

        self::assertSame(12, $skill?->id);
    }

    public function test_spell_outside_hotbar_still_requires_equipped_flag_in_battle(): void
    {
        $player = $this->player();
        DB::table('magic_skills')->insert([
            ['id' => 13, 'name' => 'Экипированное', 'is_passive' => false, 'cooldown' => 0],
            ['id' => 14, 'name' => 'Не экипированное', 'is_passive' => false, 'cooldown' => 0],
        ]);
        DB::table('player_magic_skills')->insert([
            ['player_id' => 7, 'magic_skill_id' => 13, 'is_equipped' => true],
            ['player_id' => 7, 'magic_skill_id' => 14, 'is_equipped' => false],
        ]);

        $service = new PlayerMagicSkillService;

        self::assertSame(13, $service->getActiveSkillForBattle($player, 13)?->id);
        self::assertNull($service->getActiveSkillForBattle($player, 14));
    }

    private function player(): Player
    {
        $player = (new Player)->forceFill(['id' => 7, 'user_id' => 3]);
        $player->setRelation('playerEquip', null);

        return $player;
    }
}
