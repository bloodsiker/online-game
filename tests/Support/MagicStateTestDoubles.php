<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Modules\Player\Domain\DTO\StatSheet;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Support\Facades\Event;

final class MagicStateTestDoubles
{
    public static function bind(): void
    {
        // Keep model hooks real: HP/MP saves must update regeneration baselines.
        Event::fake([static fn (string $name): bool => ! str_starts_with($name, 'eloquent.')]);
        app()->instance(PlayerStatService::class, new class extends PlayerStatService
        {
            public function __construct() {}

            public function resolve(Player $player): StatSheet
            {
                $sheet = new StatSheet;
                $sheet->hpMax = (int) $player->hp_max;
                $sheet->mpMax = (int) $player->mp_max;

                return $sheet;
            }
        });
    }
}
