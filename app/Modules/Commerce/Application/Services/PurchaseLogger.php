<?php

declare(strict_types=1);

namespace App\Modules\Commerce\Application\Services;

use App\Modules\Commerce\Domain\Enums\PurchaseSourceType;
use App\Modules\Commerce\Infrastructure\Persistence\Models\ShopPurchaseLog;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Str;

class PurchaseLogger
{
    /**
     * @param  iterable<array{
     *     item: object,
     *     quantity: int,
     *     unit_price?: int,
     *     unit_diamond?: int,
     *     requirements?: array<int, array<string, mixed>>,
     *     metadata?: array<string, mixed>
     * }>  $lines
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        User $user,
        PurchaseSourceType $sourceType,
        iterable $lines,
        ?Structure $structure = null,
        ?int $sourceId = null,
        array $metadata = [],
    ): string {
        $purchaseUuid = (string) Str::uuid();

        foreach ($lines as $line) {
            $quantity = max(1, (int) $line['quantity']);
            $unitPrice = max(0, (int) ($line['unit_price'] ?? 0));
            $unitDiamond = max(0, (int) ($line['unit_diamond'] ?? 0));
            $item = $line['item'];

            ShopPurchaseLog::query()->create([
                'purchase_uuid' => $purchaseUuid,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'structure_id' => $structure?->id,
                'structure_name' => $structure?->name,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'share_item_id' => $item->id,
                'item_name' => $item->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'unit_diamond' => $unitDiamond,
                'total_price' => $unitPrice * $quantity,
                'total_diamond' => $unitDiamond * $quantity,
                'money_balance_after' => max(0, (int) $user->money),
                'diamond_balance_after' => max(0, (int) $user->diamond),
                'requirements' => $line['requirements'] ?? null,
                'metadata' => array_merge($metadata, $line['metadata'] ?? []),
            ]);
        }

        return $purchaseUuid;
    }
}
