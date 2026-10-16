<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dungeons', function (Blueprint $table): void {
            $table->boolean('is_clan_boss')->default(false)->after('is_active');
            $table->unsignedTinyInteger('min_clan_days')->default(3)->after('is_clan_boss')
                ->comment('Анти-твин: минимум дней в клане для наград за клан-босса');
            $table->unsignedBigInteger('min_damage')->default(0)->after('min_clan_days')
                ->comment('Абсолютный минимум урона по боссу для награды (дополнительно к порогу 1%)');
        });

        Schema::table('dungeon_stages', function (Blueprint $table): void {
            $table->unsignedInteger('max_hits_per_user')->nullable()->after('total_monsters')
                ->comment('Сколько ударов игрок может сделать по этому боссу (null = без лимита)');
            $table->unsignedInteger('personal_cooldown_seconds')->nullable()->after('max_hits_per_user')
                ->comment('Личный КД между ударами по этому боссу');
            $table->unsignedInteger('clan_cooldown_seconds')->nullable()->after('personal_cooldown_seconds')
                ->comment('КД клана перед повторным открытием этого босса');
        });

        Schema::table('structures', function (Blueprint $table): void {
            $table->foreignId('dungeon_id')->nullable()->after('location_id')
                ->constrained('dungeons')->nullOnDelete()
                ->comment('Привязка структуры клан-босса к данжу');
        });

        $this->changeStructuresEnum($this->allTypesWithClanBoss());

        Schema::create('clan_boss_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('dungeon_id')->constrained('dungeons')->cascadeOnDelete();
            $table->foreignId('clan_id')->constrained('clans')->cascadeOnDelete();
            $table->foreignId('opened_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('current_stage_id')->nullable()->constrained('dungeon_stages')->nullOnDelete();
            $table->string('status', 20)->default('active');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['dungeon_id', 'clan_id', 'status']);
        });

        Schema::create('clan_boss_run_stages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('clan_boss_run_id')->constrained('clan_boss_runs')->cascadeOnDelete();
            $table->foreignId('dungeon_stage_id')->constrained('dungeon_stages')->cascadeOnDelete();
            $table->foreignId('monster_on_location_id')->nullable()->constrained('monster_on_locations')->nullOnDelete();
            $table->unsignedBigInteger('hp_max');
            $table->string('status', 20)->default('active');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('killed_at')->nullable();
            $table->timestamps();

            $table->unique(['clan_boss_run_id', 'dungeon_stage_id']);
        });

        Schema::create('clan_boss_contributions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('clan_boss_run_id')->constrained('clan_boss_runs')->cascadeOnDelete();
            $table->foreignId('dungeon_stage_id')->constrained('dungeon_stages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('damage')->default(0);
            $table->unsignedInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->boolean('rewarded')->default(false);
            $table->timestamps();

            $table->unique(['clan_boss_run_id', 'dungeon_stage_id', 'user_id'], 'cb_contrib_run_stage_user');
            $table->index(['clan_boss_run_id', 'dungeon_stage_id', 'damage'], 'cb_contrib_rating');
        });

        Schema::create('clan_boss_cooldowns', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('dungeon_stage_id')->constrained('dungeon_stages')->cascadeOnDelete();
            $table->foreignId('clan_id')->constrained('clans')->cascadeOnDelete();
            $table->timestamp('available_at');
            $table->timestamps();

            $table->unique(['dungeon_stage_id', 'clan_id']);
        });

        Schema::table('monster_on_locations', function (Blueprint $table): void {
            $table->foreignId('clan_boss_stage_state_id')->nullable()->after('dungeon_session_id')
                ->constrained('clan_boss_run_stages')->nullOnDelete()
                ->comment('Общий инстанс клан-босса; null = обычный моб');
            $table->index('clan_boss_stage_state_id');
        });
    }

    public function down(): void
    {
        Schema::table('monster_on_locations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('clan_boss_stage_state_id');
        });
        Schema::dropIfExists('clan_boss_cooldowns');
        Schema::dropIfExists('clan_boss_contributions');
        Schema::dropIfExists('clan_boss_run_stages');
        Schema::dropIfExists('clan_boss_runs');

        $this->changeStructuresEnum($this->allTypesWithoutClanBoss());

        Schema::table('structures', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('dungeon_id');
        });
        Schema::table('dungeon_stages', function (Blueprint $table): void {
            $table->dropColumn(['max_hits_per_user', 'personal_cooldown_seconds', 'clan_cooldown_seconds']);
        });
        Schema::table('dungeons', function (Blueprint $table): void {
            $table->dropColumn(['is_clan_boss', 'min_clan_days', 'min_damage']);
        });
    }

    /** @return list<string> */
    private function allTypesWithClanBoss(): array
    {
        return [...$this->allTypesWithoutClanBoss(), 'clan_boss'];
    }

    /** @return list<string> */
    private function allTypesWithoutClanBoss(): array
    {
        return [
            'shop', 'auction', 'heal', 'warehouse', 'clan_warehouse', 'bank', 'clan_bank',
            'blacksmith', 'exchange', 'auction_exchange', 'clan_skill_hall', 'reputation_exchange',
            'barter_shop', 'workshop', 'tool_workshop',
        ];
    }

    /** @param list<string> $types */
    private function changeStructuresEnum(array $types): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $list = "'".implode("','", $types)."'";
        DB::statement("ALTER TABLE structures MODIFY type ENUM({$list}) NULL DEFAULT 'shop'");
    }
};
