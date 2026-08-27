<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\Mappers;

use App\Modules\Location\Application\DTOs\LocationDungeonSessionDTO;
use App\Modules\Location\Application\DTOs\LocationMonsterDTO;
use App\Modules\Location\Application\DTOs\LocationMoveDirectionDTO;
use App\Modules\Location\Application\DTOs\LocationNpcDTO;
use App\Modules\Location\Application\DTOs\LocationPageDTO;
use App\Modules\Location\Application\DTOs\LocationPlayerFrameDTO;
use App\Modules\Location\Application\DTOs\LocationStructureActionDTO;
use App\Modules\Location\Application\DTOs\LocationStructureDTO;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Location\Infrastructure\Persistence\Models\LocationGate;
use App\Modules\Player\Domain\DTO\StatSheet;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class LocationPageViewMapper
{
    public function map(
        User $user,
        Location $location,
        StatSheet $playerDecorator,
        ?int $battleId,
        Collection $monsters,
        Collection $locationUsers,
        ?object $dungeonSession,
        int $itemsOnLocationCount,
    ): LocationPageDTO {
        return new LocationPageDTO(
            locationId: $location->id,
            name: (string) $location->name,
            description: (string) $location->description,
            image: $location->image,
            dungeonSession: $this->mapDungeonSession($dungeonSession, $location->id),
            hasBattle: $battleId !== null,
            battleUrl: $battleId !== null ? route('fight', ['id' => $battleId]) : null,
            monsters: $monsters->map(
                static fn ($monster): LocationMonsterDTO => new LocationMonsterDTO(
                    id: (int) $monster->id,
                    name: (string) $monster->monster->name,
                    isBoss: (bool) $monster->monster->isBoss(),
                    infoUrl: route('info.monster', ['id' => $monster->id]),
                    attackUrl: route('fight.attack.monster', ['id' => $monster->id]),
                )
            )->all(),
            npcs: $location->npcs->map(
                static fn ($npc): LocationNpcDTO => new LocationNpcDTO(
                    id: (int) $npc->id,
                    name: (string) $npc->name,
                    infoUrl: route('info.npc', ['uuid' => $npc->uuid]),
                    talkUrl: route('npc', ['id' => $npc->id]),
                )
            )->all(),
            moves: $this->mapMoves($location),
            itemsOnLocationCount: $itemsOnLocationCount,
            takeItemsUrl: route('take_items'),
            structures: $this->mapStructures($location, $user),
            gateActions: $this->mapGateActions($location),
            locationUsersJson: $this->mapLocationUsersJson($locationUsers),
            playerFrame: new LocationPlayerFrameDTO(
                hpCurrent: (int) $user->player->hp_now,
                hpMax: (int) $playerDecorator->getHpMax(),
                mpCurrent: (int) $user->player->mp_now,
                mpMax: (int) $playerDecorator->getMpMax(),
                experience: (float) $user->player->getPercentExp(),
                level: (int) $user->player->lvl,
            ),
            hasClanMembership: $user->clanMembership !== null,
        );
    }

    private function mapDungeonSession(?object $dungeonSession, int $locationId): ?LocationDungeonSessionDTO
    {
        if ($dungeonSession === null) {
            return null;
        }

        $isSurvival = $dungeonSession->dungeon->isSurvival();
        $waveCount = $dungeonSession->dungeon->wave_count;
        $currentWave = $isSurvival ? (int) $dungeonSession->current_wave : null;
        $wavesDone = $isSurvival ? (int) $dungeonSession->current_wave - 1 : null;
        $allCleared = $isSurvival && $waveCount !== null && $wavesDone >= $waveCount;
        $canExit = $dungeonSession->dungeon->exit_location_id === $locationId
            || ($isSurvival
                && $waveCount !== null
                && (int) $dungeonSession->current_wave > $waveCount);

        return new LocationDungeonSessionDTO(
            name: (string) $dungeonSession->dungeon->name,
            isSurvival: $isSurvival,
            currentWave: $currentWave,
            waveCount: $waveCount !== null ? (int) $waveCount : null,
            allCleared: $allCleared,
            expiresAtTimestamp: $dungeonSession->expires_at?->timestamp,
            canExit: $canExit,
            exitUrl: route('dungeon.exit'),
        );
    }

    /**
     * @return array<string, LocationMoveDirectionDTO>
     */
    private function mapMoves(Location $location): array
    {
        return [
            'up' => $this->mapMove($location->up, 'up', 'вверх'),
            'north' => $this->mapMove($location->north, 'north', 'север', 'move-north'),
            'west' => $this->mapMove($location->west, 'west', 'запад', 'move-west'),
            'east' => $this->mapMove($location->east, 'east', 'восток', 'move-east'),
            'south' => $this->mapMove($location->south, 'south', 'юг', 'move-south'),
            'down' => $this->mapMove($location->down, 'down', 'вниз'),
        ];
    }

    private function mapMove(?int $targetId, string $direction, string $label, ?string $elementId = null): LocationMoveDirectionDTO
    {
        return new LocationMoveDirectionDTO(
            available: $targetId !== null,
            url: $targetId !== null ? route('move-to', ['direction' => $direction]) : null,
            label: $label,
            elementId: $elementId,
        );
    }

    /**
     * @return list<LocationStructureActionDTO>
     */
    private function mapGateActions(Location $location): array
    {
        $actions = [];

        $gates = LocationGate::where('from_location_id', $location->id)
            ->where('mode', 'presence_pass')
            ->with('shareItem')
            ->get();

        foreach ($gates as $gate) {
            if ($gate->shareItem === null) {
                continue;
            }

            $actions[] = new LocationStructureActionDTO(
                label: $gate->button_label ?? 'Пройти',
                url: route('gate-pass', ['gateId' => $gate->id]),
            );
        }

        return $actions;
    }

    /**
     * @return list<LocationStructureDTO>
     */
    private function mapStructures(Location $location, User $user): array
    {
        $structures = [];

        foreach ($location->structures as $structure) {
            if ($structure->isClanWarehouse() && $user->clanMembership === null) {
                continue;
            }

            if ($structure->isClanBank() && $user->clanMembership === null) {
                continue;
            }

            if ($structure->isClanSkillHall() && $user->clanMembership === null) {
                continue;
            }

            $entryUrl = $this->resolveEntryUrl($structure);
            if ($entryUrl === null) {
                continue;
            }

            $actions = [];
            foreach ($structure->actions as $action) {
                $url = $this->resolveActionUrl($structure, (string) $action->alias);
                if ($url === null) {
                    continue;
                }

                $actions[] = new LocationStructureActionDTO(
                    label: (string) $action->name,
                    url: $url,
                    isHeal: $structure->isHeal(),
                );
            }

            $structures[] = new LocationStructureDTO(
                name: (string) $structure->name,
                entryUrl: $entryUrl,
                actions: $actions,
                isHeal: $structure->isHeal(),
            );
        }

        return $structures;
    }

    private function resolveEntryUrl(object $structure): ?string
    {
        return match (true) {
            $structure->isShop() => route('shop', ['id' => $structure->id]),
            $structure->isWarehouse() => route('warehouse', ['id' => $structure->id]),
            $structure->isClanWarehouse() => route('clan.warehouse', ['id' => $structure->id]),
            $structure->isBank() => route('bank', ['id' => $structure->id]),
            $structure->isClanBank() => route('clan.treasury', ['id' => $structure->id]),
            $structure->isClanSkillHall() => route('clan_skill_hall', ['id' => $structure->id]),
            $structure->isAuction() => route('auction', ['id' => $structure->id]),
            $structure->isAuctionExchange() => route('auction.exchange', ['id' => $structure->id]),
            $structure->isBlacksmith() => route('blacksmith', ['id' => $structure->id]),
            $structure->isHeal() => route('heal', ['id' => $structure->id]),
            default => null,
        };
    }

    private function resolveActionUrl(object $structure, string $alias): ?string
    {
        return match ($alias) {
            'buy' => route('shop', ['id' => $structure->id]),
            'sell' => route('shop.sell_item', ['id' => $structure->id]),
            'put_item' => $structure->isClanWarehouse()
                ? route('clan.warehouse', ['id' => $structure->id])
                : route('warehouse', ['id' => $structure->id]),
            'take_item' => $structure->isClanWarehouse()
                ? route('clan.warehouse.take', ['id' => $structure->id])
                : route('warehouse.take_item', ['id' => $structure->id]),
            'auction_buy' => route('auction', ['id' => $structure->id]),
            'auction_sell' => route('auction.new_lot', ['id' => $structure->id]),
            'auction_my_lot' => route('auction.my_lot', ['id' => $structure->id]),
            'auction_exchange' => route('auction.exchange', ['id' => $structure->id]),
            'auction_my_orders' => route('auction.my_orders', ['id' => $structure->id]),
            'auction_new_order' => route('auction.new_order', ['id' => $structure->id]),
            'auction_claims' => route('auction.claims', ['id' => $structure->id]),
            'kraft_item' => route('blacksmith', ['id' => $structure->id]),
            'break_item' => route('blacksmith.break', ['id' => $structure->id]),
            'upgrade_item' => route('blacksmith.upgrade', ['id' => $structure->id]),
            'gem_item' => route('blacksmith.gems', ['id' => $structure->id]),
            'rune_item' => route('blacksmith.runes', ['id' => $structure->id]),
            default => $structure->isHeal() ? route('heal', ['id' => $structure->id]) : null,
        };
    }

    private function mapLocationUsersJson(Collection $users): string
    {
        $tenMinutesAgo = Carbon::now()->subMinutes(10);

        return json_encode(
            $users->map(function (User $user) use ($tenMinutesAgo): array {
                $clan = $user->clanMembership?->clan;

                return [
                    'clan_icon' => $clan?->icon ? Storage::disk('public')->url($clan->icon) : null,
                    'clan_name' => $clan?->name,
                    'id' => $user->id,
                    'info_url' => route('info.user', ['id' => $user->id]),
                    'is_online' => ($user->last_online_at?->timestamp ?? 0) > $tenMinutesAgo->timestamp,
                    'lvl' => $user->player->lvl,
                    'name' => $user->name,
                    'time' => $user->last_online_at?->format('H:i') ?? '',
                ];
            })->values(),
            JSON_UNESCAPED_UNICODE,
        );
    }
}
