<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Structure;

use App\Modules\Structure\Blacksmith\Application\DTOs\KraftPageDTO;
use App\Modules\Structure\Blacksmith\Application\UseCases\GetKraftPage;
use App\Modules\Structure\Blacksmith\Presentation\Http\BlacksmithController;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

final class BlacksmithCraftSectionsTest extends TestCase
{
    public function test_artifacts_have_four_components_equality_random_pool_and_assembly_button(): void
    {
        $html = $this->renderSection('artifacts', true);
        $this->assertStringContainsString('class="craft-menu"', $html);
        $this->assertStringContainsString('<table class="artifact-assembly" data-assembly-id="100" width="100%" border="0" cellspacing="0" cellpadding="0">', $html);
        $this->assertStringContainsString('class="gp-frame-bg"', $html);
        $this->assertStringContainsString('class="gp-frame-br"', $html);
        $this->assertStringContainsString('section=artifacts', $html);
        $this->assertSame(4, substr_count($html, 'class="artifact-assembly__ingredient"'));
        $this->assertStringContainsString('class="artifact-assembly__equals" aria-hidden="true">=</span>', $html);
        $this->assertStringContainsString('value="Собрать"', $html);
        $this->assertStringContainsString('data-href="'.route('blacksmith.assemble', ['id' => 8, 'assembly' => 100]).'"', $html);
        $this->assertStringContainsString('Кулон из пула', $html);
        $this->assertStringContainsString('data-result-id="200"', $html);
        $this->assertStringContainsString('js/artifact-assembly.js', $html);
        $this->assertStringNotContainsString('Обычный рецепт', $html);
        $this->assertSame(5, substr_count($html, 'onmouseover="showItemInfo(this,event,2)"'));
    }

    public function test_assembly_stays_visible_but_disabled_when_parts_are_missing(): void
    {
        $html = $this->renderSection('artifacts', false);
        $this->assertStringContainsString('value="Собрать"', $html);
        $this->assertStringContainsString(' disabled ', $html);
        $this->assertStringNotContainsString('data-href=', $html);
        $this->assertStringContainsString('0 / 1', $html);
    }

    public function test_regular_craft_does_not_duplicate_random_artifact_recipes(): void
    {
        $html = $this->renderSection('craft', true);
        $this->assertStringContainsString('Обычный рецепт', $html);
        $this->assertStringNotContainsString('Кулон из пула', $html);
        $this->assertStringNotContainsString('value="Собрать"', $html);
        $this->assertStringNotContainsString('Случайный рецепт в рюкзаке', $html);
    }

    private function renderSection(string $section, bool $canCraft): string
    {
        app()->setLocale('ru');
        $user = (new User)->forceFill(['id' => 1, 'money' => 100, 'diamond' => 0])->setRelation('player', null);
        $this->actingAs($user);
        $recipe = [
            'recipeItemId' => 20, 'recipeName' => 'Обычный рецепт', 'recipeImage' => '/recipe.png',
            'recipeRarityColor' => '#666', 'chancePercent' => 100, 'ingredients' => [],
            'isRandomResult' => false, 'resultId' => 21, 'resultName' => 'Обычный результат',
            'resultImage' => '/result.png', 'resultRarityColor' => '#666', 'resultPool' => [], 'canCraft' => true,
        ];
        $catalog = [
            'id' => 100, 'resultMode' => 'random', 'rarityColor' => '#666', 'canCraft' => $canCraft,
            'ingredients' => array_map(fn (int $id): array => [
                'id' => $id, 'name' => 'Осколок '.$id, 'image' => '/shard.png',
                'availableCount' => $canCraft ? 1 : 0, 'requiredCount' => 1, 'active' => $canCraft,
            ], [100, 101, 102, 103]),
            'resultPool' => [['id' => 200, 'name' => 'Кулон из пула', 'image' => '/amulet.png', 'rarityColor' => '#666', 'chancePercent' => 100]],
        ];
        $page = new KraftPageDTO(
            blacksmith: (new Structure)->forceFill(['id' => 8]),
            recipes: [$recipe],
            resources: [], itemTooltipScript: '', assemblies: [$catalog],
        );
        $useCase = Mockery::mock(GetKraftPage::class);
        $useCase->shouldReceive('execute')->once()->with($user, 8)->andReturn($page);
        $this->app->instance(GetKraftPage::class, $useCase);

        return app(BlacksmithController::class)->index(Request::create('/blacksmith/8', 'GET', ['section' => $section]), 8)->render();
    }
}
