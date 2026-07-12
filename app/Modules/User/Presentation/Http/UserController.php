<?php

declare(strict_types=1);

namespace App\Modules\User\Presentation\Http;

use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Services\ItemTooltip\ItemTooltipCollector;
use App\Services\ItemTooltip\Strategy\ItemModelTooltipStrategy;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class UserController
{
    public function __construct(
        private readonly PlayerStatService $statService,
        private readonly ItemTooltipCollector $tooltipCollector,
    ) {}

    public function info(int $id): Response
    {
        $user = User::with(['player.race', 'player.playerEquip', 'player.skills', 'clanMembership.clan', 'clanMembership.role', 'currentLocation.map'])->findOrFail($id);
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
        ]);
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
            $equip->handLeft,
            $equip->handRight,
            $equip->armorSlot,
            $equip->chainArmorSlot,
            $equip->cloakSlot,
            $equip->shoesSlot,
            $equip->glovesSlot,
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
