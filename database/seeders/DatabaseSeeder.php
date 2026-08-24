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
        // Порядок важен: MagicBookStarterSeeder::bookifyExistingAttackSpells()
        // оборачивает в книги три заклинания (fire_spark/flame_barrage/
        // incinerating_vortex), которые создаёт AttackSkillSeeder. Оба зависят
        // от навыка «Колдовство» из миграции 2026_08_22_120000.
        $this->call(AttackSkillSeeder::class);
        $this->call(MagicBookStarterSeeder::class);
        $this->call(MageTierOneEquipmentSeeder::class);
        $this->call(EventActivitySeeder::class);
        $this->call(HighTierEquipmentSeeder::class);
        $this->call(OvergrownRoadMonsterSeeder::class);
        $this->call(OvergrownRoadMonsterPlacementSeeder::class);
        $this->call(OvergrownRoadLocationSeeder::class);
        $this->call(WatchHillsSeeder::class);
        $this->call(GranitePassSeeder::class);
    }
}
