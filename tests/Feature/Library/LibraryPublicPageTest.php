<?php

declare(strict_types=1);

namespace Tests\Feature\Library;

use App\Http\Middleware\AdminMiddleware;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryCategory;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Player\Infrastructure\Persistence\Models\InjuryType;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LibraryPublicPageTest extends TestCase
{
    use DatabaseTransactions;

    public function test_library_index_shows_only_published_articles(): void
    {
        $category = LibraryCategory::query()->create([
            'name' => 'Бестиарий',
            'slug' => 'bestiary-test',
            'is_active' => true,
        ]);

        $article = LibraryArticle::query()->create([
            'category_id' => $category->id,
            'title' => 'Лесной паук',
            'slug' => 'forest-spider-test',
            'content' => '<p>Описание монстра</p>',
            'status' => LibraryArticle::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        LibraryArticle::query()->create([
            'category_id' => $category->id,
            'title' => 'Пещерная крыса',
            'slug' => 'cave-rat-test',
            'content' => '<p>Описание крысы</p>',
            'status' => LibraryArticle::STATUS_PUBLISHED,
            'published_at' => now(),
            'sort_order' => 10,
        ]);

        LibraryArticle::query()->create([
            'category_id' => $category->id,
            'title' => 'Скрытый черновик',
            'slug' => 'hidden-draft-test',
            'content' => '<p>Черновик</p>',
            'status' => LibraryArticle::STATUS_DRAFT,
        ]);

        $this->get(route('library.index', ['category' => $category->slug]))
            ->assertRedirect(route('library.show', ['slug' => $article->slug, 'category' => $category->slug]));

        $this->get(route('library.show', $article->slug))
            ->assertOk()
            ->assertSee('Лесной паук')
            ->assertSee('Пещерная крыса')
            ->assertSee('library-header-select', false)
            ->assertSee('default-select', false)
            ->assertSee('library-article-options', false)
            ->assertSee('Материалы раздела')
            ->assertDontSee('Скрытый черновик');
    }

    public function test_published_article_is_available_by_slug_and_draft_is_not(): void
    {
        $category = LibraryCategory::query()->create([
            'name' => 'Профессии',
            'slug' => 'professions-test',
            'is_active' => true,
        ]);

        LibraryArticle::query()->create([
            'category_id' => $category->id,
            'title' => 'Рыбак',
            'slug' => 'fisher-test',
            'content' => '<p>Описание профессии</p>',
            'status' => LibraryArticle::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        LibraryArticle::query()->create([
            'category_id' => $category->id,
            'title' => 'Черновик',
            'slug' => 'draft-test',
            'content' => '<p>Скрытый текст</p>',
            'status' => LibraryArticle::STATUS_DRAFT,
        ]);

        $this->get(route('library.show', 'fisher-test'))
            ->assertOk()
            ->assertSee('Описание профессии', false);

        $this->get(route('library.show', 'draft-test'))->assertNotFound();
    }

    public function test_article_renders_game_entity_shortcode_as_a_card(): void
    {
        $category = LibraryCategory::query()->create([
            'name' => 'Репутации',
            'slug' => 'reputations-shortcode-test',
            'is_active' => true,
        ]);
        $reputation = Reputation::query()->create([
            'name' => 'Стражи старого города',
            'description' => 'Защитники городских ворот.',
        ]);
        $npc = Npc::query()->create([
            'name' => 'Хранитель библиотеки',
            'description' => 'Знает все древние легенды.',
        ]);
        $monster = Monster::query()->create([
            'name' => 'Книжный червь',
            'lvl' => 5,
            'hp' => 100,
            'armor' => 10,
            'dodge' => 5,
            'critical' => 3,
            'min_dmg' => 7,
            'max_dmg' => 12,
            'aggression' => 0,
        ]);
        $map = Map::query()->whereNotNull('slug')->firstOrFail();
        $location = Location::query()->where('map_id', $map->id)->firstOrFail();

        LibraryArticle::query()->create([
            'category_id' => $category->id,
            'title' => 'Городская стража',
            'slug' => 'city-guards-shortcode-test',
            'content' => "<p>Описание:</p>[[reputation:{$reputation->id}]][[npc:{$npc->id}]][[monster:{$monster->id}; display:block]][[map:{$map->id}; display:inline]][[location:{$location->id}; display:inline]]",
            'status' => LibraryArticle::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->get(route('library.show', 'city-guards-shortcode-test'))
            ->assertOk()
            ->assertSee('library-entity-card--reputation', false)
            ->assertSee('library-entity-card--npc', false)
            ->assertSee('.library-entity-card--npc .library-entity-card__image', false)
            ->assertSee('width: 200px', false)
            ->assertSee('height: 200px', false)
            ->assertSee('library-entity-card--monster', false)
            ->assertSee('library-entity-line--map', false)
            ->assertSee('library-entity-line--location', false)
            ->assertSee('data-library-info-popup', false)
            ->assertSee(route('info.monster.catalog', ['id' => $monster->id]), false)
            ->assertSee(route('map.public', ['slug' => $map->slug]), false)
            ->assertSee(route('map.public', ['slug' => $map->slug]).'#'.$location->id, false)
            ->assertSee('Стражи старого города')
            ->assertDontSee("[[map:{$map->id}; display:inline]]", false)
            ->assertDontSee("[[reputation:{$reputation->id}]]", false);
    }

    public function test_article_renders_item_shortcode_with_tooltip_data(): void
    {
        $category = LibraryCategory::query()->create([
            'name' => 'Предметы',
            'slug' => 'items-shortcode-test',
            'is_active' => true,
        ]);
        $item = ShareItem::query()->firstOrFail();

        LibraryArticle::query()->create([
            'category_id' => $category->id,
            'title' => 'Тестовый предмет',
            'slug' => 'item-shortcode-tooltip-test',
            'content' => "<p>[[item:{$item->id}]] [[item:{$item->id}; count:2]]</p>",
            'status' => LibraryArticle::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->get(route('library.show', 'item-shortcode-tooltip-test'))
            ->assertOk()
            ->assertSee('data-id="'.$item->id.'"', false)
            ->assertSee('onmouseover="showItemInfo(this,event,2)"', false)
            ->assertSee('window.itemTooltip', false)
            ->assertDontSee('art_alt["AA_news_', false)
            ->assertSee('<span class="news-shortcode-item__image"', false)
            ->assertSee(asset('main/images/user-reward-frame.png'), false)
            ->assertSee('background-size:72px 71px,60px 60px', false)
            ->assertSee('<span class="artifact-slot-qnt"', false)
            ->assertDontSee('<table width="60"', false)
            ->assertSee('js/item_tooltip.js', false)
            ->assertSee("onclick=\"window.open(this.href, '', 'width=730,height=700", false)
            ->assertSee("window.open(link.href, '', infoWindowOptions)", false);
    }

    public function test_library_editor_can_insert_game_frame(): void
    {
        $this->withoutMiddleware(AdminMiddleware::class);

        $this->get(route('admin.library.articles.create'))
            ->assertOk()
            ->assertSee('library-game-frame-insert', false)
            ->assertSee('Игровая рамка')
            ->assertSee('library-game-frame-title-insert', false)
            ->assertSee('Рамка с заголовком')
            ->assertDontSee('var tableSize', false)
            ->assertSee('<table class="library-game-frame"', false)
            ->assertSee('class="library-game-frame__title"', false)
            ->assertSee('Заголовок блока')
            ->assertSee('/img/bg/info/tbl-usi_label-left.gif', false)
            ->assertSee('/img/bg/info/tbl-usi_label-right.gif', false)
            ->assertSee('class="tbl-shp-sml lt"', false)
            ->assertSee('Текст блока')
            ->assertSee('css/library_game_frame.css', false);
    }

    public function test_article_loads_game_frame_styles(): void
    {
        $category = LibraryCategory::query()->create([
            'name' => 'Игровые блоки',
            'slug' => 'game-frames-test',
            'is_active' => true,
        ]);
        LibraryArticle::query()->create([
            'category_id' => $category->id,
            'title' => 'Статья с игровой рамкой',
            'slug' => 'game-frame-test',
            'content' => '<table class="library-game-frame"><tbody><tr><td class="tbl-usi_bg"><div class="structures">Содержимое рамки</div></td></tr></tbody></table>',
            'status' => LibraryArticle::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->get(route('library.show', 'game-frame-test'))
            ->assertOk()
            ->assertSee('css/library_game_frame.css', false)
            ->assertSee('class="library-game-frame"', false)
            ->assertSee('Содержимое рамки');

        $this->assertStringContainsString(
            '/img/bg/info/tbl-usi_label-center.gif',
            file_get_contents(public_path('css/library_game_frame.css'))
        );
    }

    public function test_article_renders_current_injury_catalog_with_all_icons(): void
    {
        $category = LibraryCategory::query()->create([
            'name' => 'Травмы',
            'slug' => 'injuries-shortcode-test',
            'is_active' => true,
        ]);
        LibraryArticle::query()->create([
            'category_id' => $category->id,
            'title' => 'Система травм',
            'slug' => 'injury-catalog-shortcode-test',
            'content' => '[[injury_catalog]]',
            'status' => LibraryArticle::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
        $injuries = InjuryType::query()
            ->where('is_active', true)
            ->where('drop_weight', '>', 0)
            ->get();

        $this->assertNotEmpty($injuries);

        $response = $this->get(route('library.show', 'injury-catalog-shortcode-test'))
            ->assertOk()
            ->assertSee('library-injury-catalog', false)
            ->assertSee('Часть тела')
            ->assertSee('Лёгкая')
            ->assertSee('Средняя')
            ->assertSee('Тяжёлая')
            ->assertSee('css/library_injury_catalog.css', false)
            ->assertDontSee('[[injury_catalog]]', false);

        foreach ($injuries as $injury) {
            $response->assertSee($injury->name);

            if ($injury->image) {
                $response->assertSee($injury->image, false);
            }
        }

        $this->assertSame(
            $injuries->count(),
            substr_count($response->getContent(), 'class="library-injury-catalog__entry '),
        );
    }

    public function test_article_renders_current_artifact_catalog_with_item_tooltips(): void
    {
        $category = LibraryCategory::query()->create([
            'name' => 'Артефакты',
            'slug' => 'artifacts-shortcode-test',
            'is_active' => true,
        ]);
        LibraryArticle::query()->create([
            'category_id' => $category->id,
            'title' => 'Артефакты и талисманы',
            'slug' => 'artifact-catalog-shortcode-test',
            'content' => '[[artifact_catalog]]',
            'status' => LibraryArticle::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
        $artifacts = ShareItem::query()
            ->where('type', ShareItemType::ARTIFACT->value)
            ->where('is_active', true)
            ->get();

        $this->assertNotEmpty($artifacts);

        $response = $this->get(route('library.show', 'artifact-catalog-shortcode-test'))
            ->assertOk()
            ->assertSee('library-artifact-catalog', false)
            ->assertSee('css/library_artifact_catalog.css', false)
            ->assertSee(asset('main/images/user-reward-frame.png'), false)
            ->assertSee('onmouseover="showItemInfo(this,event,2)"', false)
            ->assertSee('window.itemTooltip', false)
            ->assertDontSee('[[artifact_catalog]]', false);

        foreach ($artifacts as $artifact) {
            $response
                ->assertSee($artifact->name)
                ->assertSee('data-id="'.$artifact->id.'"', false);
        }

        $this->assertSame(
            $artifacts->count(),
            substr_count($response->getContent(), 'class="library-artifact-catalog__entry"'),
        );
    }
}
