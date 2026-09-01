<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Player;

use App\Modules\Player\Application\UseCases\GetProfessionsPage;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProfessionsPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('skill_level_requirements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('skill_id');
            $table->unsignedInteger('lvl');
            $table->unsignedInteger('exp_required');
            $table->unsignedInteger('exp_diff');
        });
        Schema::create('player_skills', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('skill_id');
            $table->unsignedInteger('lvl');
            $table->unsignedInteger('exp');
            $table->unsignedInteger('exp_up');
            $table->unsignedInteger('exp_diff');
            $table->timestamps();
        });
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image');
            $table->unsignedBigInteger('skill_id')->nullable();
            $table->unsignedInteger('skill_lvl')->nullable();
            $table->unsignedInteger('skill_exp')->nullable();
            $table->timestamps();
        });
        Schema::create('share_recipes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedBigInteger('kraft_item_id')->nullable();
            $table->unsignedInteger('percent')->default(100);
            $table->string('unlock_type')->default('single_use');
            $table->timestamps();
        });
        Schema::create('player_recipes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('share_recipe_id');
            $table->timestamps();
        });
    }

    public function test_page_groups_learned_recipes_by_peaceful_profession(): void
    {
        DB::table('skills')->insert([
            ['id' => 10, 'name' => 'Алхимик', 'type' => 'peaceful', 'description' => 'Создаёт зелья.'],
            ['id' => 11, 'name' => 'Рыбак', 'type' => 'peaceful', 'description' => 'Ловит рыбу.'],
            ['id' => 12, 'name' => 'Мечник', 'type' => 'combat', 'description' => 'Боевой навык.'],
            ['id' => 13, 'name' => 'Повар', 'type' => 'peaceful', 'description' => 'Готовит еду.'],
            ['id' => 14, 'name' => 'Ремесленник', 'type' => 'peaceful', 'description' => 'Создаёт инструменты.'],
            ['id' => 15, 'name' => 'Кузнец', 'type' => 'peaceful', 'description' => 'Куёт снаряжение.'],
        ]);
        DB::table('skill_level_requirements')->insert([
            ['skill_id' => 10, 'lvl' => 1, 'exp_required' => 100, 'exp_diff' => 100],
            ['skill_id' => 11, 'lvl' => 1, 'exp_required' => 100, 'exp_diff' => 100],
            ['skill_id' => 13, 'lvl' => 1, 'exp_required' => 100, 'exp_diff' => 100],
            ['skill_id' => 14, 'lvl' => 1, 'exp_required' => 100, 'exp_diff' => 100],
            ['skill_id' => 15, 'lvl' => 1, 'exp_required' => 100, 'exp_diff' => 100],
        ]);
        DB::table('player_skills')->insert([
            'player_id' => 1,
            'skill_id' => 10,
            'lvl' => 5,
            'exp' => 450,
            'exp_up' => 500,
            'exp_diff' => 100,
        ]);
        DB::table('share_items')->insert([
            ['id' => 30, 'type' => 'recipe', 'name' => 'Рецепт настоя', 'description' => 'Учит варить настой.', 'image' => '/recipe.png', 'skill_id' => 10, 'skill_lvl' => 4, 'skill_exp' => 7],
            ['id' => 31, 'type' => 'potion', 'name' => 'Травяной настой', 'description' => null, 'image' => '/potion.png', 'skill_id' => null, 'skill_lvl' => null, 'skill_exp' => null],
        ]);
        DB::table('share_recipes')->insert([
            'id' => 20,
            'share_item_id' => 30,
            'kraft_item_id' => 31,
            'percent' => 100,
            'unlock_type' => 'learnable',
        ]);
        DB::table('player_recipes')->insert([
            'player_id' => 1,
            'share_recipe_id' => 20,
            'created_at' => '2026-09-01 12:00:00',
            'updated_at' => '2026-09-01 12:00:00',
        ]);

        $player = new Player;
        $player->id = 1;
        $page = (new GetProfessionsPage)->execute($player);

        $this->assertCount(4, $page['professions']);
        $this->assertSame(10, $page['activeProfessionId']);
        $this->assertSame('Алхимик', $page['professions'][0]['name']);
        $this->assertSame('Повар', $page['professions'][1]['name']);
        $this->assertSame('Ремесник', $page['professions'][2]['name']);
        $this->assertSame('Кузнец', $page['professions'][3]['name']);
        $this->assertSame(5, $page['professions'][0]['level']);
        $this->assertSame(50.0, $page['professions'][0]['experiencePercent']);
        $this->assertSame('Рецепт настоя', $page['professions'][0]['recipes'][0]['name']);
        $this->assertSame('Травяной настой', $page['professions'][0]['recipes'][0]['resultName']);
        $this->assertSame([], $page['professions'][3]['recipes']);
    }

    public function test_professions_view_and_menu_entry_are_available(): void
    {
        $html = view('player::professions', [
            'page' => ['professions' => [], 'activeProfessionId' => null],
        ])->render();
        $menu = view('interface::menu')->render();

        $this->assertStringContainsString('Профессии', $html);
        $this->assertStringContainsString('m_professions', $menu);
        $this->assertSame('/character/professions', parse_url(route('character.professions'), PHP_URL_PATH));
    }
}
