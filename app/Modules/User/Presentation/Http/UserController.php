<?php

declare(strict_types=1);

namespace App\Modules\User\Presentation\Http;

use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ItemModelTooltipStrategy;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Player\Application\ItemTooltip\PlayerInjuryTooltipStrategy;
use App\Modules\Player\Domain\Services\PlayerInjuryService;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Infrastructure\Persistence\Models\PlayerReputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\PlayerReputationMedal;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class UserController
{
    public function __construct(
        private readonly PlayerStatService $statService,
        private readonly PlayerInjuryService $injuryService,
        private readonly ItemTooltipCollector $tooltipCollector,
        private readonly ReputationService $reputationService,
    ) {}

    public function info(int $id): Response
    {
        $user = User::with([
            'player.race',
            'player.playerEquip',
            'player.artifacts.item.itemInfo',
            'player.skills',
            'player.reputations.reputation.tiers',
            'clanMembership.clan',
            'clanMembership.role',
            'currentLocation.map',
        ])->findOrFail($id);
        $stats = $this->statService->resolve($user->player);
        $injuriesBySlot = $this->injuryService->activeByEquipmentColumn($user->player);
        $playerArtifacts = $user->player->artifacts;
        $this->tooltipCollector
            ->collectFrom(new ItemModelTooltipStrategy([
                ...$this->equippedItems($user),
                ...$playerArtifacts->pluck('item')->filter()->values()->all(),
            ]))
            ->collectFrom(new PlayerInjuryTooltipStrategy($injuriesBySlot));

        $isOnline = $user->last_online_at !== null
            && Carbon::parse($user->last_online_at)->gt(Carbon::now()->subMinutes(10));

        return response()->view('user::info', [
            'user' => $user,
            'stats' => $stats,
            'injuriesBySlot' => $injuriesBySlot,
            'playerArtifacts' => $playerArtifacts,
            'age' => $this->formatAge($user->created_at),
            'isOnline' => $isOnline,
            'locationPath' => $this->buildLocationPath($user),
            'itemTooltipScript' => $this->tooltipCollector->renderScript(),
            'reputationMedals' => $this->reputationMedals($user->player),
        ]);
    }

    /**
     * @return array<int, array{
     *     image: string,
     *     name: string,
     *     reputation: string,
     *     type: string,
     *     rating: int,
     *     minPoints: int,
     *     earnedAt: ?string,
     *     description: ?string
     * }>
     */
    private function reputationMedals(Player $player): array
    {
        $earnedAtByTier = PlayerReputationMedal::where('player_id', $player->id)
            ->get(['tier_id', 'is_feat', 'earned_at'])
            ->keyBy(fn (PlayerReputationMedal $medal): string => $medal->tier_id.'_'.($medal->is_feat ? 'feat' : 'regular'));

        return $player->reputations
            ->map(function (PlayerReputation $playerReputation) use ($player, $earnedAtByTier) {
                $reputation = $playerReputation->reputation;
                if ($reputation === null) {
                    return null;
                }

                $regularMedals = $this->reputationService
                    ->getEarnedMedals($reputation, $playerReputation->points, $player)
                    ->map(fn ($tier) => [
                        'image' => $tier->medalIconUrl(),
                        'name' => $tier->medal_name,
                        'reputation' => $reputation->name,
                        'rating' => $tier->regularMedalRating(),
                        'minPoints' => $tier->min_points,
                        'earnedAt' => $earnedAtByTier->get($tier->id.'_regular')?->earned_at,
                        'type' => 'Медаль репутации',
                        'description' => null,
                    ]);

                $featMedals = $this->reputationService
                    ->getEarnedFeatMedals($reputation, $playerReputation->points, $player)
                    ->map(fn ($tier) => [
                        'image' => $tier->featMedalIconUrl(),
                        'name' => $tier->feat_medal_name,
                        'reputation' => $reputation->name,
                        'rating' => $tier->featMedalRating(),
                        'minPoints' => $tier->min_points,
                        'earnedAt' => $earnedAtByTier->get($tier->id.'_feat')?->earned_at,
                        'type' => 'Медаль за подвиг',
                        'description' => $tier->feat_description,
                    ]);

                // В карусели — только самая престижная медаль репутации
                // (feat-медаль всегда весомее обычной, см. FEAT_MEDAL_RATING).
                return $regularMedals->concat($featMedals)
                    ->filter(fn (array $medal): bool => is_string($medal['image']) && $medal['image'] !== '')
                    ->sortByDesc(fn (array $medal) => [$medal['rating'], $medal['minPoints']])
                    ->first();
            })
            ->filter()
            // Недавно полученные медали — первыми в карусели. Медали без записи
            // о времени получения (не было события, редкий случай) уходят в конец.
            ->sortByDesc(fn (array $medal) => $medal['earnedAt']?->timestamp ?? -1)
            ->map(fn (array $medal) => [
                'image' => $medal['image'],
                'name' => $medal['name'],
                'reputation' => $medal['reputation'],
                'type' => $medal['type'],
                'rating' => $medal['rating'],
                'minPoints' => $medal['minPoints'],
                'earnedAt' => $medal['earnedAt']?->format('d.m.Y H:i'),
                'description' => $medal['description'],
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, Item>
     */
    private function equippedItems(User $user): array
    {
        $equip = $user->player->playerEquip;
        if ($equip === null) {
            return [];
        }

        return array_values(array_filter([
            $equip->helmetSlot,
            $equip->shoulderSlot,
            $equip->forearmSlot,
            $equip->handLeft,
            $equip->handRight,
            $equip->armorSlot,
            $equip->leggingSlot,
            $equip->chainArmorSlot,
            $equip->shoesSlot,
            $equip->beltFirstSlot,
            $equip->beltSecondSlot,
            $equip->bagFirstSlot,
            $equip->bagSecondSlot,
        ]));
    }

    private function buildLocationPath(User $user): string
    {
        $location = $user->currentLocation;
        if ($location === null) {
            return '';
        }

        $path = [];
        $map = $location->map;
        while ($map !== null) {
            array_unshift($path, $map->name);
            $map = $map->parent;
        }
        $path[] = $location->name.' ('.$location->id.')';

        return implode(' / ', $path);
    }

    private function formatAge(?Carbon $createdAt): string
    {
        if ($createdAt === null) {
            return '—';
        }

        $diff = $createdAt->diff(Carbon::now());

        $parts = [];
        if ($diff->y > 0) {
            $parts[] = $diff->y.' Год';
        }
        if ($diff->m > 0) {
            $parts[] = $diff->m.' Мес.';
        }
        $parts[] = $diff->d.' Дн.';

        return implode(' ', $parts);
    }

    public function logout(Request $request): Response
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response("<script>window.top.location.href='".route('index')."';</script>");
    }
}
