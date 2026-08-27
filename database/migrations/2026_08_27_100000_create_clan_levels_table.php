<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clan_levels', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('level')->unique();
            $table->decimal('experience_required', 18, 2)->unsigned()->unique();
            $table->timestamps();
        });

        $now = now();
        DB::table('clan_levels')->insert([
            ['level' => 1, 'experience_required' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['level' => 2, 'experience_required' => 20_000_000, 'created_at' => $now, 'updated_at' => $now],
            ['level' => 3, 'experience_required' => 75_000_000, 'created_at' => $now, 'updated_at' => $now],
            ['level' => 4, 'experience_required' => 250_000_000, 'created_at' => $now, 'updated_at' => $now],
            ['level' => 5, 'experience_required' => 700_000_000, 'created_at' => $now, 'updated_at' => $now],
            ['level' => 6, 'experience_required' => 1_600_000_000, 'created_at' => $now, 'updated_at' => $now],
            ['level' => 7, 'experience_required' => 3_000_000_000, 'created_at' => $now, 'updated_at' => $now],
            ['level' => 8, 'experience_required' => 5_500_000_000, 'created_at' => $now, 'updated_at' => $now],
            ['level' => 9, 'experience_required' => 9_500_000_000, 'created_at' => $now, 'updated_at' => $now],
            ['level' => 10, 'experience_required' => 15_000_000_000, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('clan_levels');
    }
};
