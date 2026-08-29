<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Battle;

use App\Modules\Battle\Application\Services\Combat\HitCalculator;
use App\Modules\Battle\Application\Services\Combat\Strategies\OneHandWeaponStrategy;
use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\Battle\Domain\Contracts\RandomizerInterface;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerEquipment;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Mockery;
use Tests\TestCase;

class OneHandWeaponStrategyTest extends TestCase
{
    public function test_it_uses_the_weapon_when_the_other_hand_holds_a_tool(): void
    {
        $toolInfo = (new ShareItem)->forceFill([
            'name' => 'Серп',
            'type' => ShareItemType::TOOL,
        ]);
        $weaponInfo = (new ShareItem)->forceFill([
            'name' => 'Меч',
            'type' => ShareItemType::WEAPON,
        ]);
        $weaponInfo->setRelation('skill', null);

        $tool = (new Item)->forceFill(['id' => 10]);
        $tool->setRelation('itemInfo', $toolInfo);
        $weapon = (new Item)->forceFill(['id' => 11]);
        $weapon->setRelation('itemInfo', $weaponInfo);

        $equipment = new PlayerEquipment;
        $equipment->setRelation('handLeft', $tool);
        $equipment->setRelation('handRight', $weapon);

        $player = Mockery::mock(FightHitInterface::class)->shouldIgnoreMissing();
        $player->shouldReceive('getRightHandMinDmg')->once()->andReturn(7);
        $player->shouldReceive('getRightHandMaxDmg')->once()->andReturn(9);
        $monster = Mockery::mock(FightHitInterface::class)->shouldIgnoreMissing();

        $random = Mockery::mock(RandomizerInterface::class);
        $random->shouldReceive('chance')->once()->andReturnTrue();
        $calculator = new HitCalculator($random);

        $result = (new OneHandWeaponStrategy($calculator, $player, $monster, $equipment))->getHits()[0];

        $this->assertSame('Меч', $result->getWeaponName());
        $this->assertSame('right', $result->getHandSide());
        $this->assertSame($weaponInfo, $result->getWeapon());
    }
}
