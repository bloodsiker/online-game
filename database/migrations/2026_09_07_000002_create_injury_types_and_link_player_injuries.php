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
        Schema::create('injury_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->text('description')->nullable();
            $table->string('body_part', 32);
            $table->unsignedTinyInteger('severity');
            $table->string('image')->nullable();
            $table->unsignedInteger('duration_seconds');
            $table->unsignedInteger('drop_weight')->default(1);
            $table->json('stat_modifiers')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'drop_weight']);
            $table->index(['body_part', 'severity']);
        });

        Schema::table('player_injuries', function (Blueprint $table): void {
            $table->unsignedBigInteger('injury_type_id')->nullable()->after('player_id');
            $table->index('injury_type_id');
        });

        $this->insertDefaultInjuryTypes();
        $this->linkExistingInjuries();

        Schema::table('player_injuries', function (Blueprint $table): void {
            $table->foreign('injury_type_id')
                ->references('id')
                ->on('injury_types')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('player_injuries', function (Blueprint $table): void {
            $table->dropForeign(['injury_type_id']);
            $table->dropIndex(['injury_type_id']);
            $table->dropColumn('injury_type_id');
        });

        Schema::dropIfExists('injury_types');
    }

    private function insertDefaultInjuryTypes(): void
    {
        $parts = [
            'head' => ['name' => 'головы', 'stats' => ['intuition', 'wisdom', 'intelligence']],
            'shoulders' => ['name' => 'плеч', 'stats' => ['strength', 'endurance']],
            'forearms' => ['name' => 'предплечий', 'stats' => ['strength']],
            'left_hand' => ['name' => 'левой руки', 'stats' => ['strength']],
            'right_hand' => ['name' => 'правой руки', 'stats' => ['strength']],
            'torso' => ['name' => 'туловища', 'stats' => ['endurance']],
            'chest' => ['name' => 'груди', 'stats' => ['endurance']],
            'legs' => ['name' => 'ног', 'stats' => ['agility', 'endurance']],
            'feet' => ['name' => 'ступней', 'stats' => ['agility']],
        ];
        $levels = [
            1 => ['slug' => 'light', 'name' => 'Лёгкая', 'duration' => 900, 'weight' => 65, 'penalty' => 5],
            2 => ['slug' => 'medium', 'name' => 'Средняя', 'duration' => 1800, 'weight' => 25, 'penalty' => 10],
            3 => ['slug' => 'severe', 'name' => 'Тяжёлая', 'duration' => 3600, 'weight' => 10, 'penalty' => 15],
        ];
        $now = now();
        $rows = [];

        foreach ($parts as $bodyPart => $part) {
            foreach ($levels as $severity => $level) {
                $rows[] = [
                    'name' => $level['name'].' травма '.$part['name'],
                    'slug' => $level['slug'].'-'.$bodyPart,
                    'description' => 'После смерти занимает соответствующий слот экипировки до окончания лечения.',
                    'body_part' => $bodyPart,
                    'severity' => $severity,
                    'image' => null,
                    'duration_seconds' => $level['duration'],
                    'drop_weight' => $level['weight'],
                    'stat_modifiers' => json_encode(array_map(
                        static fn (string $stat): array => [
                            'stat' => $stat,
                            'value' => -$level['penalty'],
                            'is_percent' => true,
                        ],
                        $part['stats'],
                    ), JSON_THROW_ON_ERROR),
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('injury_types')->insert($rows);
    }

    private function linkExistingInjuries(): void
    {
        DB::table('player_injuries')
            ->select(['id', 'body_part', 'severity'])
            ->orderBy('id')
            ->chunkById(200, function ($injuries): void {
                foreach ($injuries as $injury) {
                    $injuryTypeId = DB::table('injury_types')
                        ->where('body_part', $injury->body_part)
                        ->where('severity', $injury->severity)
                        ->value('id');

                    DB::table('player_injuries')
                        ->where('id', $injury->id)
                        ->update(['injury_type_id' => $injuryTypeId]);
                }
            });
    }
};
