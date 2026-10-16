<?php

declare(strict_types=1);

namespace App\Modules\Structure\Presentation\Http\Middleware;

use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureStructureActionIsActive
{
    /** More specific routes must precede their parent route. */
    private const ACTIONS = [
        'shop.sell_item' => 'sell', 'shop' => 'buy', 'barter_shop' => 'buy', 'influence.shop' => 'buy',
        'warehouse.take_item' => 'take_item', 'warehouse' => 'put_item',
        'clan.warehouse.take' => 'take_item', 'clan.warehouse' => 'put_item',
        'auction.my_lot' => 'auction_my_lot', 'auction.new_lot' => 'auction_sell',
        'auction.new_order' => 'auction_new_order', 'auction.my_orders' => 'auction_my_orders',
        'auction.order.cancel' => 'auction_my_orders', 'auction.order.fulfill' => 'auction_exchange',
        'auction.claims' => 'auction_claims', 'auction.claim' => 'auction_claims', 'auction.exchange' => 'auction_exchange',
        'auction' => 'auction_buy',
        'blacksmith.break' => 'break_item', 'blacksmith.upgrade_transfer' => 'upgrade_transfer',
        'blacksmith.rarity_upgrade' => 'rarity_upgrade', 'blacksmith.upgrade' => 'upgrade_item',
        'blacksmith.gems' => 'gem_item', 'blacksmith.runes' => 'rune_item', 'blacksmith' => 'kraft_item',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName() ?? '';
        if ($routeName === 'blacksmith.kraft') {
            // This route receives a recipe instance ID, not a structure ID.
            $structures = Structure::query()->where('location_id', $request->user()?->location_id)
                ->where('type', Structure::TYPE_BLACKSMITH)->get();
            foreach ($structures as $structure) {
                $this->ensureAllowed($structure, 'kraft_item');
            }

            return $next($request);
        }
        $alias = null;
        foreach (self::ACTIONS as $prefix => $action) {
            if ($routeName === $prefix || str_starts_with($routeName, $prefix.'.')) {
                $alias = $action;
                break;
            }
        }
        if ($alias !== null || $routeName === 'heal') {
            $id = $request->route('id');
            if (is_numeric($id)) {
                $structure = Structure::query()->find((int) $id);
                if ($structure !== null) {
                    $this->ensureAllowed($structure, $alias);
                }
            }
        }

        return $next($request);
    }

    private function ensureAllowed(Structure $structure, ?string $alias): void
    {
        $actions = $structure->actions();
        if ($alias === null) {
            // All healing actions resolve to the same endpoint.
            $states = $actions->pluck('structure_actions.is_active');
            $disabled = $states->isNotEmpty() && ! $states->contains(fn ($state) => (bool) $state);
        } else {
            $disabled = $actions->where('alias', $alias)->wherePivot('is_active', false)->exists();
        }
        abort_if($disabled, 403, app()->getLocale() === 'uk' ? 'Дія тимчасово недоступна.' : 'Действие временно недоступно.');
    }
}
