<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Share;

use App\Modules\Battle\Application\Services\BattleLogTranslator;
use App\Modules\Effect\Domain\Enums\ActiveEffectType;
use App\Modules\Effect\Domain\Enums\EffectDamageScalingType;
use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Interface\Application\Mappers\HeroPageViewMapper;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;
use App\Modules\Share\Application\Services\ContentTranslationEditor;
use App\Modules\Share\Infrastructure\Localization\LocalizedContentSeederQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

final class MagicSkillEffectTranslationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        app()->setLocale('ru');
        foreach (['magic_skills', 'effects'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('slug')->nullable();
                $table->string('type')->nullable();
                $table->integer('level')->default(1);
                $table->integer('base_healing')->default(0);
                $table->boolean('is_passive')->default(false);
                $table->integer('chance')->default(0);
                $table->boolean('is_stackable')->default(false);
                $table->integer('max_stacks')->default(1);
                $table->integer('tick_interval')->default(1);
                $table->json('stat_modifiers')->nullable();
                $table->timestamps();
            });
        }
    }

    public function test_migration_preserves_ids_and_both_existing_locales(): void
    {
        foreach (['magic_skills', 'effects'] as $table) {
            DB::table($table)->insert([
                ['id' => 42, 'name' => 'Ожог', 'description' => '<p>Урон: 10%</p>'],
                ['id' => 55, 'name' => json_encode(['ru' => 'Оглушение', 'uk' => 'Оглушення']), 'description' => null],
            ]);
        }
        $migration = require database_path('migrations/2026_10_12_170000_localize_magic_skill_and_effect_content.php');
        $migration->up();
        $migration->up();
        foreach ([MagicSkill::class, Effect::class] as $class) {
            $this->assertSame(2, $class::count());
            $this->assertSame('Ожог', $class::findOrFail(42)->name);
            $this->assertSame('<p>Урон: 10%</p>', $class::findOrFail(42)->description);
            app()->setLocale('uk');
            $this->assertSame('Оглушення', $class::findOrFail(55)->name);
            $this->assertSame(55, $class::where('name->ru', 'Оглушение')->value('id'));
            app()->setLocale('ru');
        }
    }

    public function test_editor_and_importer_preserve_other_languages(): void
    {
        foreach ([MagicSkill::class, Effect::class] as $class) {
            app()->setLocale('uk');
            $model = new $class;
            app(ContentTranslationEditor::class)->fill(model: $model, request: Request::create('/', 'POST', [
                'name' => 'Ожог', 'description' => 'Урон',
                'translations' => ['uk' => ['name' => 'Опік', 'description' => 'Шкода']],
            ]));
            $model->save();
            LocalizedContentSeederQuery::forTable($model->getTable())->where('name', 'Ожог')->update(['description' => 'Новый урон']);
            $this->assertSame('Опік', $model->refresh()->name);
            $this->assertSame('Шкода', $model->description);
            $this->assertSame('Новый урон', $model->getTranslation('description', 'ru'));
        }
    }

    public function test_search_is_grouped_and_matches_both_languages(): void
    {
        foreach ([MagicSkill::class, Effect::class] as $class) {
            $first = $class::create(['name' => ['ru' => 'Burn', 'uk' => 'Опік'], 'type' => 'buff']);
            $class::create(['name' => ['ru' => 'Ожог', 'uk' => 'BURN'], 'type' => 'debuff']);
            $this->assertSame(2, $class::query()->searchTranslations('%burn%')->count());
            $this->assertSame([$first->id], $class::query()->where('type', 'buff')->searchTranslations('%burn%')->pluck('id')->all());
        }
    }

    public function test_logs_and_hero_effects_render_for_their_viewer_without_changing_timers(): void
    {
        $spell = new MagicSkill(['name' => ['ru' => 'Огненный шар', 'uk' => 'Вогняна куля']]);
        $effect = new Effect(['name' => ['ru' => 'Ожог', 'uk' => 'Опік'], 'description' => ['ru' => 'Урон', 'uk' => 'Шкода'], 'type' => 'debuff', 'slug' => 'burn']);
        $stored = BattleLogTranslator::format('%s: %s', BattleLogTranslator::contentName($spell), BattleLogTranslator::contentName($effect));
        $this->assertSame('Огненный шар: Ожог', BattleLogTranslator::render($stored));
        app()->setLocale('uk');
        $this->assertSame('Вогняна куля: Опік', BattleLogTranslator::render($stored));
        $active = (new PlayerActiveEffect)->forceFill(['id' => 1, 'applied_at' => now(), 'expires_at' => now()->addMinute()]);
        $active->setRelation('effect', $effect);
        $mapper = new HeroPageViewMapper;
        $ru = $mapper->mapEffects(collect([$active]), now(), locale: 'ru')[0];
        $uk = $mapper->mapEffects(collect([$active]), now(), locale: 'uk')[0];
        $this->assertSame('Ожог', $ru->name);
        $this->assertSame('Опік', $uk->name);
        $this->assertSame('Шкода', $uk->description);
        $this->assertSame($ru->duration, $uk->duration);
        $this->assertSame($ru->totalDuration, $uk->totalDuration);
    }

    public function test_all_four_admin_forms_submit_both_languages(): void
    {
        $spell = new MagicSkill(['name' => 'Огненный шар', 'slug' => 'fireball']);
        $spell->id = 42;
        $effect = new Effect(['name' => 'Ожог', 'slug' => 'burn']);
        $effect->id = 55;
        foreach (['skillEffects', 'requirements'] as $relation) {
            $spell->setRelation($relation, new Collection);
        }
        foreach (['magicSkills', 'monsters'] as $relation) {
            $effect->setRelation($relation, new Collection);
        }
        $data = ['magicSkill' => $spell, 'effect' => $effect, 'effects' => collect(), 'skills' => collect(), 'activeTypes' => ActiveEffectType::cases(), 'damageScalingTypes' => EffectDamageScalingType::cases(), 'errors' => new ViewErrorBag];
        foreach (['admin.magic_skill.', 'effect::admin.'] as $prefix) {
            foreach (['create', 'info'] as $page) {
                $document = new \DOMDocument;
                @$document->loadHTML('<?xml encoding="UTF-8">'.view($prefix.$page, $data)->render());
                $xpath = new \DOMXPath($document);
                $form = $xpath->query('//form[@enctype="multipart/form-data"]')->item(0);
                $this->assertNotNull($form);
                foreach (['name', 'description', 'translations[uk][name]', 'translations[uk][description]'] as $name) {
                    $this->assertSame(1, $xpath->query('.//*[@name="'.$name.'"]', $form)->length);
                }
                $this->assertSame(2, $xpath->query('.//button[@data-bs-toggle="tab"]', $form)->length);
            }
        }
    }
}
