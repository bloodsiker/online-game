<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Modules\Clan\Domain\Enums\ClanJoinRequestStatus;
use App\Modules\Clan\Domain\Enums\ClanPermission;
use App\Modules\Clan\Domain\Models\ClanJoinRequest;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait CreatesClanFixture
{
    use CreatesInventoryFixture;

    private function createClanTables(): void
    {
        $this->createInventoryTables();
        Schema::table('players', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('lvl')->default(1);
        });
        Schema::table('clan_join_requests', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->unique();
            $table->unsignedBigInteger('chat_message_id')->nullable();
        });
        Schema::table('clan_members', fn (Blueprint $table) => $table->unique('user_id'));
        Schema::create('magic_skills', fn (Blueprint $table) => $table->id());
        Schema::create('player_magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('magic_skill_id');
            $table->boolean('is_equipped')->default(true);
            $table->timestamps();
            $table->unique(['player_id', 'magic_skill_id']);
        });
        foreach (['2026_03_17_000002_create_clan_skill_definitions_table.php',
            '2026_08_27_090000_create_clan_skill_level_item_requirements_table.php',
            '2026_08_28_110000_add_required_money_to_clan_skill_levels_table.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    private function seedClanFixture(): void
    {
        $this->seedEconomyFixture();
        DB::table('locations')->insert(['id' => 29]);
        DB::table('users')->insert(['id' => 4, 'name' => 'Other Leader', 'money' => 100, 'diamond' => 100]);
        foreach ([1, 2, 3, 4] as $id) {
            DB::table('players')->insert(['id' => $id, 'user_id' => $id]);
            DB::table('users')->where('id', $id)->update(['player_id' => $id, 'location_id' => 29]);
        }
        DB::table('clans')->where('id', 1)->update(['points' => 100, 'lvl' => 5]);
        DB::table('clan_roles')->where('id', 2)->update(['is_default' => true]);
        DB::table('clans')->insert(['id' => 2, 'name' => 'Second Clan', 'owner_id' => 4, 'tax_paid_until' => now()->addDay()]);
        DB::table('clan_roles')->insert([
            ['id' => 3, 'clan_id' => 2, 'name' => 'Leader', 'is_leader' => true, 'is_default' => true, 'permissions' => ClanPermission::allBits()],
            ['id' => 4, 'clan_id' => 2, 'name' => 'Member', 'is_leader' => false, 'is_default' => true, 'permissions' => 0],
        ]);
        DB::table('clan_members')->insert(['clan_id' => 2, 'user_id' => 4, 'role_id' => 3]);
        DB::table('magic_skills')->insert([['id' => 1], ['id' => 2]]);
        foreach ([1, 2] as $id) {
            DB::table('clan_skill_definitions')->insert(['id' => $id, 'name' => json_encode(['ru' => 'Навык '.$id]), 'max_level' => 2]);
            foreach ([1, 2] as $level) {
                DB::table('clan_skill_levels')->insert([
                    'clan_skill_definition_id' => $id, 'level' => $level, 'required_clan_level' => 1,
                    'required_bonus_points' => 60, 'required_money' => 40, 'share_item_id' => 1,
                    'share_item_count' => 3, 'magic_skill_id' => $level,
                ]);
            }
        }
    }

    private function joinRequest(int $clanId = 1, int $userId = 3, bool $application = false): ClanJoinRequest
    {
        return ClanJoinRequest::create([
            'clan_id' => $clanId, 'user_id' => $userId, 'uuid' => (string) Str::uuid(),
            'status' => $application ? ClanJoinRequestStatus::REQUEST : ClanJoinRequestStatus::INVITE,
        ]);
    }
}
