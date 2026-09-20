<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Item;

use App\Modules\Battle\Application\Services\Combat\BattleEffectService;
use App\Modules\Item\Application\Services\LockpickingService;
use App\Modules\Item\Domain\Services\ItemService;
use App\Modules\Player\Domain\Services\PeacefulProfessionExperienceService;
use App\Modules\Player\Domain\Services\PlayerStatService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LockpickingServiceTest extends TestCase
{
    #[DataProvider('chanceCases')]
    public function test_success_chance_depends_on_skill_difference_and_trap(
        int $skill,
        int $required,
        int $trapPenalty,
        int $minimumChance,
        float $expected,
    ): void {
        $this->assertSame($expected, $this->service()->successChance($skill, $required, $trapPenalty, $minimumChance));
    }

    public static function chanceCases(): array
    {
        return [
            'equal level' => [100, 100, 0, 5, 80.0],
            'higher lock is still possible' => [90, 100, 0, 5, 65.0],
            'far higher lock has configured floor' => [1, 100, 0, 12, 12.0],
            'trap reduces chance' => [100, 100, 20, 5, 60.0],
            'chance has global ceiling' => [300, 1, 0, 5, 95.0],
            'trap penalty respects configured floor' => [100, 100, 99, 8, 8.0],
        ];
    }

    #[DataProvider('durationCases')]
    public function test_skill_can_reduce_duration_by_at_most_forty_percent(
        int $base,
        int $skill,
        int $required,
        int $expected,
    ): void {
        $this->assertSame($expected, $this->service()->durationSeconds($base, $skill, $required));
    }

    public static function durationCases(): array
    {
        return [
            'below lock does not slow down' => [20, 1, 100, 20],
            'small advantage' => [20, 110, 100, 20],
            'large advantage' => [20, 200, 100, 16],
            'maximum bonus' => [20, 300, 1, 12],
            'minimum duration' => [2, 300, 1, 2],
        ];
    }

    public function test_lower_tier_lockpick_reduces_chance_and_increases_duration(): void
    {
        $service = $this->service();

        $this->assertSame(60.0, $service->lockpickSuccessChance(100, 100, 0, 1, 5));
        $this->assertSame(18.0, $service->lockpickSuccessChance(1, 300, 95, 1, 18));
        $this->assertSame(26, $service->lockpickDurationSeconds(20, 100, 100, 1, 0));
        $this->assertSame(22, $service->lockpickDurationSeconds(20, 100, 100, 2, 5));
    }

    #[DataProvider('difficultyCases')]
    public function test_chance_is_presented_as_difficulty(float $chance, string $label, string $level): void
    {
        $this->assertSame(
            ['label' => $label, 'level' => $level],
            $this->service()->difficultyForChance($chance),
        );
    }

    public static function difficultyCases(): array
    {
        return [
            'easy' => [80.0, 'Легко', 'easy'],
            'medium' => [66.5, 'Среднее', 'medium'],
            'hard' => [30.0, 'Сложно', 'hard'],
            'very hard' => [29.99, 'Очень сложно', 'very-hard'],
        ];
    }

    #[DataProvider('lockTierCases')]
    public function test_lock_tier_boundaries(int $requiredSkill, int $expectedTier): void
    {
        $this->assertSame($expectedTier, $this->service()->lockTier($requiredSkill));
    }

    public static function lockTierCases(): array
    {
        return [
            [1, 1], [49, 1],
            [50, 2], [99, 2],
            [100, 3], [149, 3],
            [150, 4], [199, 4],
            [200, 5], [249, 5],
            [250, 6], [300, 6],
        ];
    }

    private function service(): LockpickingService
    {
        return new LockpickingService(
            $this->createMock(ItemService::class),
            $this->createMock(PeacefulProfessionExperienceService::class),
            $this->createMock(BattleEffectService::class),
            $this->createMock(PlayerStatService::class),
        );
    }
}
