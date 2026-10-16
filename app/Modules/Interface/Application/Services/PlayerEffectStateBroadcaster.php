<?php

declare(strict_types=1);

namespace App\Modules\Interface\Application\Services;

use App\Modules\Interface\Application\Mappers\HeroPageViewMapper;
use App\Modules\Interface\Domain\Contracts\InterfaceReadRepository;
use App\Modules\Interface\Domain\Events\PlayerStateUpdated;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\User\Application\Services\GameSettingsService;

class PlayerEffectStateBroadcaster
{
    public function __construct(
        private readonly InterfaceReadRepository $readRepository,
        private readonly HeroPageViewMapper $heroMapper,
    ) {}

    public function broadcast(Player $player): void
    {
        $locale = $player->user_id
            ? app(GameSettingsService::class)->get((int) $player->user_id)['language']
            : app()->getLocale();
        $effects = $this->heroMapper->mapEffects(
            $this->readRepository->getPlayerActiveEffects((int) $player->id),
            now(),
            locale: $locale,
        );

        PlayerStateUpdated::dispatch((int) $player->id, [
            'effects' => array_map(
                static fn ($effect): array => $effect->toArray(),
                $effects,
            ),
        ]);
    }
}
