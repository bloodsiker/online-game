<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('races', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->float('str', 2);
            $table->float('agil', 2);
            $table->float('int', 2);
            $table->float('mud', 2);
            $table->float('intel', 2);
            $table->integer('free_stats');
            $table->timestamps();
        });

        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('race_id');
            $table->integer('lvl')->default(1);
            $table->integer('exp')->default(0);
            $table->integer('exp_up');
            $table->integer('exp_diff');
            $table->float('str', 2);
            $table->float('agil', 2);
            $table->float('int', 2);
            $table->float('mud', 2);
            $table->float('intel', 2);
            $table->integer('hp_now');
            $table->integer('hp_max');
            $table->integer('mp_now')->default(0);
            $table->integer('mp_max')->default(0);
            $table->double('min_dmg');
            $table->double('max_dmg');
            $table->integer('dodge');
            $table->integer('critical');
            $table->integer('free_stats');
            $table->integer('victory')->default(0);
            $table->integer('death')->default(0);
            $table->tinyInteger('is_main')->default(0);
            $table->tinyInteger('is_active')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('race_id')->references('id')->on('races');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('player_id')->nullable()->after('id');
            $table->integer('money')->default(0);
            $table->integer('diamond')->default(0);
            $table->integer('warehouse_count')->default(50);

            $table->foreign('player_id')->references('id')->on('players');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['player_id']);
            $table->dropColumn('player_id');
            $table->dropColumn('money');
            $table->dropColumn('diamond');
        });

        Schema::dropIfExists('players');
        Schema::dropIfExists('races');
    }
};
