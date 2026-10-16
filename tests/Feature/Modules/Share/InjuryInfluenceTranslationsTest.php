<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Share;

use App\Modules\Influence\Infrastructure\Persistence\Models\MapInfluenceLevel;
use App\Modules\Player\Infrastructure\Persistence\Models\InjuryType;
use App\Modules\Share\Application\Services\ContentTranslationEditor;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class InjuryInfluenceTranslationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        app()->setLocale('ru');

        Schema::create('injury_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('map_influence_levels', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function test_migration_and_models_keep_injury_and_influence_names_in_both_locales(): void
    {
        DB::table('injury_types')->insert(['id' => 3, 'name' => 'Тяжёлая травма', 'description' => 'Сильное повреждение']);
        DB::table('map_influence_levels')->insert(['id' => 8, 'name' => 'Доверие города']);

        $migration = require database_path('migrations/2026_10_12_340000_localize_injury_and_influence_levels.php');
        $migration->up();

        $injury = InjuryType::query()->findOrFail(3);
        $level = MapInfluenceLevel::query()->findOrFail(8);
        $injury->setTranslation('name', 'uk', 'Важка травма');
        $injury->setTranslation('description', 'uk', 'Сильне ушкодження');
        $injury->save();
        $level->setTranslation('name', 'uk', 'Довіра міста');
        $level->save();

        app()->setLocale('uk');
        $this->assertSame('Важка травма', $injury->fresh()->name);
        $this->assertSame('Сильне ушкодження', $injury->fresh()->description);
        $this->assertSame('Довіра міста', $level->fresh()->name);
        $this->assertSame('Тяжёлая травма', $injury->fresh()->getTranslation('name', 'ru'));
    }

    public function test_editor_accepts_translations_for_injury_and_level_definitions(): void
    {
        $injury = new InjuryType;
        app(ContentTranslationEditor::class)->fill($injury, Request::create('/', 'POST', [
            'name' => 'Тяжёлая травма',
            'description' => 'Сильное повреждение',
            'translations' => ['uk' => ['name' => 'Важка травма', 'description' => 'Сильне ушкодження']],
        ]), ['name', 'description']);

        $level = new MapInfluenceLevel;
        app(ContentTranslationEditor::class)->fill($level, Request::create('/', 'POST', [
            'name' => 'Доверие города',
            'translations' => ['uk' => ['name' => 'Довіра міста']],
        ]), ['name']);

        $this->assertSame('Важка травма', $injury->getTranslation('name', 'uk'));
        $this->assertSame('Довіра міста', $level->getTranslation('name', 'uk'));
    }
}
