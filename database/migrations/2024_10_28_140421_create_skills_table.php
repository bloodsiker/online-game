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
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['combat', 'magic', 'peaceful']);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('share_items', function (Blueprint $table) {
            $table->foreignId('skill_id')->after('slot')->nullable()->constrained('skills')->nullOnDelete();
            $table->integer('skill_lvl')->nullable()->after('skill_id');
            $table->integer('skill_exp')->nullable()->after('skill_lvl');
            $table->boolean('is_two_hand')->default(0)->after('image');
        });

        Schema::create('player_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->nullable()->constrained('players')->cascadeOnDelete();
            $table->foreignId('skill_id')->nullable()->constrained('skills')->cascadeOnDelete();
            $table->integer('lvl')->default(1);
            $table->integer('exp')->default(0);
            $table->integer('exp_up')->default(1000);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('share_items', function (Blueprint $table) {
            $table->dropForeign(['skill_id']);
            $table->dropColumn('skill_lvl');
        });

        Schema::dropIfExists('skills');
    }
};
