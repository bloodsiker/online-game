<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call(DungeonSeeder::class);
        $this->call(SurvivalArenaSeeder::class);
        $this->call(BuffSkillSeeder::class);
        $this->call(EventActivitySeeder::class);
        $this->call(HighTierEquipmentSeeder::class);
        $this->call(OvergrownRoadMonsterSeeder::class);
        $this->call(OvergrownRoadMonsterPlacementSeeder::class);
        $this->call(OvergrownRoadLocationSeeder::class);
        $this->call(WatchHillsSeeder::class);
        $this->call(GranitePassSeeder::class);
    }
}
