<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Structure\Blacksmith;

use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipRenderer;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipStatsBuilder;
use App\Modules\Item\Application\ItemTooltip\Strategy\ItemModelTooltipStrategy;
use App\Modules\Item\Application\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerArtifact;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemStat;
use App\Modules\Structure\Blacksmith\Domain\Services\ArtifactUpgradeRules;
use Illuminate\Database\Eloquent\Collection;
use ReflectionMethod;
use Tests\TestCase;

final class ArtifactUpgradeStatsTest extends TestCase
{
    public function test_all_own_stats_scale_linearly_and_tooltip_matches_player_modifiers(): void
    {
        app()->setLocale('ru');
        $source = (new ShareItem)->forceFill(['type' => 'artifact', 'name' => 'Артефакт']);
        $source->setRelation('stats', new Collection([
            (new ShareItemStat)->forceFill(['stat_type' => 'armor', 'value' => 40, 'value_type' => 'flat']),
            (new ShareItemStat)->forceFill(['stat_type' => 'critical', 'value' => 10, 'value_type' => 'percent']),
        ]));
        $source->setRelation('effects', new Collection);
        $item = (new Item)->forceFill(['upgrade_lvl' => 25])->setRelation('itemInfo', $source);
        $player = (new Player)->setRelation('artifacts', new Collection([
            (new PlayerArtifact)->setRelation('item', $item),
        ]));
        $modifiers = (new ReflectionMethod(PlayerStatService::class, 'fromArtifacts'))->invoke(app(PlayerStatService::class), $player);
        $armor = collect($modifiers)->where('stat', 'armor');
        $crit = collect($modifiers)->where('stat', 'critical');
        $this->assertEquals(90, $armor->sum('value'));
        $this->assertEquals(22.5, $crit->sum('value'));
        $this->assertTrue($crit->every(fn ($modifier) => $modifier->isPercent));
        $tooltip = collect(ItemTooltipStatsBuilder::build($source, 25))->pluck('value', 'title');
        $this->assertSame('+90', $tooltip[__('Броня')]);
        $this->assertSame('+22.5%', $tooltip[__('Критический удар')]);
        $this->assertEquals(50, ArtifactUpgradeRules::bonus(40, 25));
        $this->assertEquals(100, ArtifactUpgradeRules::bonus(40, 50));
        $this->assertEquals(200, ArtifactUpgradeRules::bonus(40, 100));
    }

    public function test_low_values_keep_fractional_bonus(): void
    {
        $this->assertEquals(0.25, ArtifactUpgradeRules::bonus(5, 1));
        $this->assertEquals(0, ArtifactUpgradeRules::bonus(5, 0));
    }

    public function test_current_copy_and_preview_have_separate_tooltips_and_correct_stats(): void
    {
        app()->setLocale('ru');
        $source = (new ShareItem)->forceFill(['id' => 10, 'type' => 'artifact', 'name' => 'Артефакт', 'rarity' => 'common']);
        $source->setRelation('stats', new Collection([
            (new ShareItemStat)->forceFill(['stat_type' => 'armor', 'value' => 40, 'value_type' => 'flat']),
        ]));
        foreach (['effects', 'requirements'] as $relation) {
            $source->setRelation($relation, new Collection);
        }
        $source->setRelation('useLimit', null)->setRelation('lockpickConfig', null);
        $item = (new Item)->forceFill(['id' => 10, 'upgrade_lvl' => 11])->setRelation('itemInfo', $source)
            ->setRelation('gems', new Collection)->setRelation('runes', new Collection);
        $preview = clone $item;
        $preview->upgrade_lvl = 12;
        $collector = new ItemTooltipCollector(new ItemTooltipRenderer);
        $collector->collectFrom(new ItemModelTooltipStrategy([$item]))
            ->collectFrom(new ShareItemTooltipStrategy([$source], idPrefix: 'artifact_template_'))
            ->collectFrom(new ItemModelTooltipStrategy([$preview], idPrefix: 'artifact_preview_'));
        $tooltips = $collector->all();

        $this->assertCount(3, $tooltips);
        $this->assertSame('+62', $tooltips[10]['stats'][0]['value']);
        $this->assertSame('+40', $tooltips['artifact_template_10']['stats'][0]['value']);
        $this->assertSame('+64', $tooltips['artifact_preview_10']['stats'][0]['value']);
        $this->assertStringContainsString('+12', $tooltips['artifact_preview_10']['title']);
        $this->assertSame(11, $item->upgrade_lvl);
    }
}
