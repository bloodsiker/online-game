<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Clan;

use App\Modules\Clan\Domain\Models\ClanMember;
use App\Modules\Clan\Domain\Services\ClanExperienceService;
use App\Modules\Clan\Domain\Services\ClanLevelService;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ClanExperienceServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('clans', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('lvl')->default(1);
            $table->decimal('experience', 18, 2)->default(0);
        });
        Schema::create('clan_levels', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('level')->unique();
            $table->decimal('experience_required', 18, 2)->unique();
            $table->timestamps();
        });
        Schema::create('clan_members', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('clan_id');
            $table->unsignedInteger('user_id');
            $table->decimal('experience_contributed', 18, 2)->default(0);
        });

        DB::table('clans')->insert(['id' => 1, 'lvl' => 1, 'experience' => 0]);
        DB::table('clan_levels')->insert([
            ['level' => 1, 'experience_required' => 0],
            ['level' => 2, 'experience_required' => 0.50],
        ]);
        DB::table('clan_members')->insert([
            'id' => 1,
            'clan_id' => 1,
            'user_id' => 1,
            'experience_contributed' => 0,
        ]);

        app(ClanLevelService::class)->forgetThresholds();
    }

    public function test_uses_configured_level_difference_scale(): void
    {
        $service = app(ClanExperienceService::class);

        $this->assertSame(0, $service->percentForLevelDifference(-11));
        $this->assertSame(1, $service->percentForLevelDifference(-10));
        $this->assertSame(1, $service->percentForLevelDifference(0));
        $this->assertSame(3, $service->percentForLevelDifference(1));
        $this->assertSame(3, $service->percentForLevelDifference(9));
        $this->assertSame(5, $service->percentForLevelDifference(10));
    }

    public function test_awards_decimal_experience_to_clan_and_member_contribution(): void
    {
        $player = new Player;
        $player->lvl = 20;
        $member = new ClanMember(['clan_id' => 1, 'user_id' => 1]);
        $member->id = 1;
        $member->exists = true;
        $user = new User(['name' => 'Участник']);
        $user->id = 1;
        $user->exists = true;
        $user->setRelation('clanMembership', $member);
        $player->setRelation('user', $user);

        $monster = new Monster;
        $monster->lvl = 25;

        $awarded = app(ClanExperienceService::class)->awardForMonsterExperience($player, $monster, 17);

        $this->assertSame(0.51, $awarded);
        $this->assertEquals(0.51, (float) DB::table('clans')->value('experience'));
        $this->assertSame(2, (int) DB::table('clans')->value('lvl'));
        $this->assertEquals(0.51, (float) DB::table('clan_members')->value('experience_contributed'));
    }
}
