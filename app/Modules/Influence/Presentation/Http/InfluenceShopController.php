<?php

declare(strict_types=1);

namespace App\Modules\Influence\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Commerce\Application\Services\PurchaseLogger;
use App\Modules\Commerce\Domain\Enums\PurchaseSourceType;
use App\Modules\Influence\Domain\Services\MapInfluenceRequirementService;
use App\Modules\Influence\Infrastructure\Persistence\Models\InfluenceShopItem;
use App\Modules\Influence\Infrastructure\Persistence\Models\InfluenceShopSection;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class InfluenceShopController extends Controller
{
    public function __construct(
        private readonly MapInfluenceRequirementService $requirements,
        private readonly BackpackService $backpackService,
        private readonly PurchaseLogger $purchaseLogger,
    ) {}

    public function index(int $structure): View
    {
        /** @var User $user */
        $user = Auth::user();
        $shop = Structure::query()->whereKey($structure)->where('type', Structure::TYPE_SHOP)->firstOrFail();
        $sections = InfluenceShopSection::query()
            ->where('structure_id', $shop->id)
            ->where('is_active', true)
            ->with(['map', 'items' => fn ($query) => $query->where('is_active', true)->with('item')])
            ->orderBy('sort_order')
            ->get()
            ->map(function (InfluenceShopSection $section) use ($user): InfluenceShopSection {
                $points = $this->requirements->has($user->id, $section->map_id, 0)
                    ? (int) DB::table('map_influences')->where('user_id', $user->id)->where('map_id', $section->map_id)->value('influence')
                    : 0;
                $section->setAttribute('player_influence', $points);

                return $section;
            });

        return view('influence::shop', compact('shop', 'sections', 'user'));
    }

    public function buy(Request $request, int $structure, InfluenceShopItem $shopItem): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $quantity = max(1, min(999, $request->integer('quantity', 1)));

        $message = DB::transaction(function () use ($user, $structure, $shopItem, $quantity): string {
            $item = InfluenceShopItem::query()->with(['section', 'item'])->lockForUpdate()->findOrFail($shopItem->id);
            abort_unless((int) $item->section->structure_id === $structure && $item->is_active && $item->section->is_active, 404);
            $required = max((int) $item->required_influence, (int) $item->section->required_influence);
            if (! $this->requirements->has($user->id, (int) $item->section->map_id, $required)) {
                return 'Недостаточно влияния для покупки этого товара.';
            }

            /** @var User $lockedUser */
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $purchase = DB::table('influence_shop_purchases')
                ->where('user_id', $user->id)
                ->where('influence_shop_item_id', $item->id)
                ->lockForUpdate()
                ->first();
            if ($item->purchase_limit !== null && ((int) ($purchase->quantity ?? 0) + $quantity) > $item->purchase_limit) {
                return 'Превышен лимит покупки этого товара.';
            }

            $money = $item->price * $quantity;
            $diamond = $item->diamond * $quantity;
            if ($lockedUser->money < $money || $lockedUser->diamond < $diamond) {
                return 'Недостаточно средств.';
            }

            $lockedUser->decrement('money', $money);
            $lockedUser->decrement('diamond', $diamond);
            $this->backpackService->addItemByShareItem($lockedUser, $item->item, $quantity);
            DB::table('influence_shop_purchases')->updateOrInsert(
                ['user_id' => $user->id, 'influence_shop_item_id' => $item->id],
                ['quantity' => (int) ($purchase->quantity ?? 0) + $quantity, 'created_at' => $purchase->created_at ?? now(), 'updated_at' => now()],
            );

            $shop = Structure::query()->findOrFail($structure);
            $this->purchaseLogger->record(
                user: $lockedUser,
                sourceType: PurchaseSourceType::InfluenceShop,
                lines: [[
                    'item' => $item->item,
                    'quantity' => $quantity,
                    'unit_price' => (int) $item->price,
                    'unit_diamond' => (int) $item->diamond,
                    'metadata' => [
                        'required_influence' => $required,
                        'section_name' => $item->section->name,
                    ],
                ]],
                structure: $shop,
                sourceId: (int) $item->section->map_id,
                metadata: ['map_id' => (int) $item->section->map_id],
            );

            return 'Товар куплен.';
        });

        return back()->with('message', $message);
    }
}
