<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Share;

use App\Http\Controllers\Admin\SkillController;
use App\Modules\Share\Infrastructure\Localization\LocalizedContentSeederQuery;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

final class SkillTranslationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        app()->setLocale('ru');
        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type');
            $table->timestamps();
        });
    }

    public function test_migration_preserves_ids_russian_text_and_existing_translations(): void
    {
        DB::table('skills')->insert([
            ['id' => 42, 'name' => 'Травник', 'description' => '<p>Сбор трав</p>', 'type' => 'peaceful'],
            ['id' => 55, 'name' => json_encode(['ru' => 'Лесоруб', 'uk' => 'Лісоруб']), 'description' => null, 'type' => 'peaceful'],
        ]);
        $migration = require database_path('migrations/2026_10_12_160000_localize_skill_content.php');
        $migration->up();
        $migration->up();
        $this->assertSame(2, Skill::count());
        $this->assertSame('Травник', Skill::findOrFail(42)->name);
        $this->assertSame('<p>Сбор трав</p>', Skill::findOrFail(42)->description);
        $this->assertSame('Травник', DB::table('skills')->where('id', 42)->value('name_legacy'));
        app()->setLocale('uk');
        $this->assertSame('Лісоруб', Skill::findOrFail(55)->name);
        $this->assertSame(55, Skill::where('name->ru', 'Лесоруб')->value('id'));
        $this->assertSame('', Skill::findOrFail(55)->description);
    }

    public function test_admin_saves_both_languages_independently_of_current_locale(): void
    {
        app()->setLocale('uk');
        app('redirect')->setSession(app('session.store'));
        $skill = Skill::create(['name' => 'Травник', 'type' => 'peaceful']);
        $request = Request::create('/', 'POST', ['name' => 'Лесоруб', 'description' => 'Заготовка древесины', 'type' => 'peaceful', 'translations' => ['uk' => ['name' => 'Лісоруб', 'description' => 'Заготівля деревини']]]);
        (new SkillController)->info(request: $request, skill: $skill);
        $skill->refresh();
        $this->assertSame('Лісоруб', $skill->name);
        $this->assertSame('Лесоруб', $skill->getTranslation('name', 'ru'));
        $this->assertSame('Заготівля деревини', $skill->description);
        $request->merge(['translations' => ['uk' => ['name' => '', 'description' => null]]]);
        (new SkillController)->info(request: $request, skill: $skill);
        $this->assertSame('Лесоруб', $skill->refresh()->name);
    }

    public function test_imports_find_canonical_names_and_preserve_ukrainian_text(): void
    {
        $id = LocalizedContentSeederQuery::forTable('skills')->insertGetId(['name' => 'Лесоруб', 'type' => 'peaceful']);
        Skill::findOrFail($id)->setTranslation('name', 'uk', 'Лісоруб')->save();
        LocalizedContentSeederQuery::forTable('skills')->where('name', 'Лесоруб')->update(['description' => 'Новое описание']);
        app()->setLocale('uk');
        $this->assertSame('Лісоруб', Skill::findOrFail($id)->name);
        $this->assertSame('Новое описание', Skill::findOrFail($id)->description);
        $this->assertSame($id, LocalizedContentSeederQuery::forTable('skills')->where('name', 'Лесоруб')->value('id'));
    }

    public function test_admin_forms_contain_exactly_one_control_for_each_language_field(): void
    {
        $skill = new Skill(['name' => ['ru' => 'Мечник', 'uk' => 'Мечник'], 'type' => 'combat']);
        $skill->id = 42;
        foreach (['create', 'info'] as $page) {
            $html = view('admin.skill.'.$page, ['skill' => $skill, 'errors' => new ViewErrorBag])->render();
            $document = new \DOMDocument;
            @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
            $xpath = new \DOMXPath($document);
            $form = $xpath->query('//form')->item(0);
            foreach (['name', 'description', 'translations[uk][name]', 'translations[uk][description]'] as $name) {
                $this->assertSame(1, $xpath->query('.//*[@name="'.$name.'"]', $form)->length);
            }
            $this->assertSame(2, $xpath->query('.//button[@data-bs-toggle="tab"]', $form)->length);
            $panel = $xpath->query('//div[@id="tab-translations"]')->item(0);
            $this->assertSame('tab-content', $panel->parentNode->getAttribute('class'));
        }
    }
}
