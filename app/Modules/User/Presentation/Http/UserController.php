<?php

declare(strict_types=1);

namespace App\Modules\User\Presentation\Http;

use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ItemModelTooltipStrategy;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Infrastructure\Persistence\Models\PlayerReputation;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class UserController
{
    public function __construct(
        private readonly PlayerStatService $statService,
        private readonly ItemTooltipCollector $tooltipCollector,
        private readonly ReputationService $reputationService,
    ) {}

    public function info(int $id): Response
    {
        $user = User::with([
            'player.race',
            'player.playerEquip',
            'player.skills',
            'player.reputations.reputation.tiers',
            'clanMembership.clan',
            'clanMembership.role',
            'currentLocation.map',
        ])->findOrFail($id);
        $stats = $this->statService->resolve($user->player);
        $this->tooltipCollector->collectFrom(new ItemModelTooltipStrategy($this->equippedItems($user)));

        $isOnline = $user->last_online_at !== null
            && Carbon::parse($user->last_online_at)->gt(Carbon::now()->subMinutes(10));

        return response()->view('user::info', [
            'user' => $user,
            'stats' => $stats,
            'age' => $this->formatAge($user->created_at),
            'isOnline' => $isOnline,
            'locationPath' => $this->buildLocationPath($user),
            'itemTooltipScript' => $this->tooltipCollector->renderScript(),
            'reputationMedals' => $this->reputationMedals($user->player),
        ]);
    }

    /** @return array<int, array{image: string, name: string, reputation: string}> */
    private function reputationMedals(Player $player): array
    {
        return $player->reputations
            ->flatMap(function (PlayerReputation $playerReputation) use ($player) {
                $reputation = $playerReputation->reputation;
                if ($reputation === null) {
                    return [];
                }

                $regularMedals = $this->reputationService
                    ->getEarnedMedals($reputation, $playerReputation->points, $player)
                    ->map(fn ($tier) => [
                        'image' => $this->medalImageUrl($tier->medal_icon),
                        'name' => $tier->medal_name,
                        'reputation' => $reputation->name,
                    ]);

                $featMedals = $this->reputationService
                    ->getEarnedFeatMedals($reputation, $playerReputation->points, $player)
                    ->map(fn ($tier) => [
                        'image' => $this->medalImageUrl($tier->feat_medal_icon),
                        'name' => $tier->feat_medal_name,
                        'reputation' => $reputation->name,
                    ]);

                return $regularMedals->concat($featMedals);
            })
            ->filter(fn (array $medal): bool => $medal['image'] !== '')
            ->values()
            ->all();
    }

    private function medalImageUrl(?string $image): string
    {
        if ($image === null || $image === '') {
            return '';
        }

        return str_starts_with($image, 'http://') || str_starts_with($image, 'https://') || str_starts_with($image, '/')
            ? $image
            : asset($image);
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
