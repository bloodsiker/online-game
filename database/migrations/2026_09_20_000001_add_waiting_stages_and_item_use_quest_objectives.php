<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quest_stages', function (Blueprint $table): void {
            $table->string('stage_type', 20)->default('action')->after('description');
            $table->unsignedInteger('wait_duration_seconds')->nullable()->after('stage_type');
            $table->text('waiting_text')->nullable()->after('wait_duration_seconds');
            $table->text('ready_text')->nullable()->after('waiting_text');
        });

        Schema::table('quest_players', function (Blueprint $table): void {
            $table->timestamp('current_stage_started_at')->nullable()->after('current_stage_id');
            $table->timestamp('current_stage_ready_at')->nullable()->after('current_stage_started_at');
        });

        Schema::table('quest_clan_progress', function (Blueprint $table): void {
            $table->timestamp('current_stage_started_at')->nullable()->after('current_stage_id');
            $table->timestamp('current_stage_ready_at')->nullable()->after('current_stage_started_at');
        });

        $this->changeObjectiveEnums(up: true);

        Schema::table('quest_objectives', function (Blueprint $table): void {
            $table->boolean('consume_item')->default(true)->after('share_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('quest_objectives', function (Blueprint $table): void {
            $table->dropColumn('consume_item');
        });

        $this->changeObjectiveEnums(up: false);

        Schema::table('quest_clan_progress', function (Blueprint $table): void {
            $table->dropColumn(['current_stage_started_at', 'current_stage_ready_at']);
        });

        Schema::table('quest_players', function (Blueprint $table): void {
            $table->dropColumn(['current_stage_started_at', 'current_stage_ready_at']);
        });

        Schema::table('quest_stages', function (Blueprint $table): void {
            $table->dropColumn(['stage_type', 'wait_duration_seconds', 'waiting_text', 'ready_text']);
        });
    }

    private function changeObjectiveEnums(bool $up): void
    {
        if (DB::getDriverName() === 'mysql') {
            $types = $up
                ? "ENUM('kill','collect','talk','deliver','use_item')"
                : "ENUM('kill','collect','talk','deliver')";
            $targets = $up
                ? "ENUM('monster','item','npc','location')"
                : "ENUM('monster','item','npc')";

            DB::statement("ALTER TABLE quest_objectives MODIFY type {$types} NOT NULL");
            DB::statement("ALTER TABLE quest_objectives MODIFY target_type {$targets} NOT NULL");

            return;
        }

        Schema::table('quest_objectives', function (Blueprint $table) use ($up): void {
            $table->enum('type', $up
                ? ['kill', 'collect', 'talk', 'deliver', 'use_item']
                : ['kill', 'collect', 'talk', 'deliver'])->change();
            $table->enum('target_type', $up
                ? ['monster', 'item', 'npc', 'location']
                : ['monster', 'item', 'npc'])->change();
        });
    }
};
