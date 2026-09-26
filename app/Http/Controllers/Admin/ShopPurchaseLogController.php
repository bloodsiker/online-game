<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Commerce\Domain\Enums\PurchaseSourceType;
use App\Modules\Commerce\Infrastructure\Persistence\Models\ShopPurchaseLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopPurchaseLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'source_type' => (string) $request->query('source_type', ''),
            'user' => trim((string) $request->query('user', '')),
            'structure' => trim((string) $request->query('structure', '')),
            'item' => trim((string) $request->query('item', '')),
            'date_from' => (string) $request->query('date_from', ''),
            'date_to' => (string) $request->query('date_to', ''),
        ];

        $logs = ShopPurchaseLog::query()
            ->with(['user', 'structure', 'shareItem'])
            ->when(
                PurchaseSourceType::tryFrom($filters['source_type']) !== null,
                fn ($query) => $query->where('source_type', $filters['source_type']),
            )
            ->when($filters['user'] !== '', function ($query) use ($filters): void {
                $query->where(function ($nested) use ($filters): void {
                    $nested->where('user_name', 'like', '%'.$filters['user'].'%');
                    if (ctype_digit($filters['user'])) {
                        $nested->orWhere('user_id', (int) $filters['user']);
                    }
                });
            })
            ->when($filters['structure'] !== '', function ($query) use ($filters): void {
                $query->where(function ($nested) use ($filters): void {
                    $nested->where('structure_name', 'like', '%'.$filters['structure'].'%');
                    if (ctype_digit($filters['structure'])) {
                        $nested->orWhere('structure_id', (int) $filters['structure']);
                    }
                });
            })
            ->when($filters['item'] !== '', function ($query) use ($filters): void {
                $query->where(function ($nested) use ($filters): void {
                    $nested->where('item_name', 'like', '%'.$filters['item'].'%');
                    if (ctype_digit($filters['item'])) {
                        $nested->orWhere('share_item_id', (int) $filters['item']);
                    }
                });
            })
            ->when($filters['date_from'] !== '', fn ($query) => $query->whereDate('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', fn ($query) => $query->whereDate('created_at', '<=', $filters['date_to']))
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.shop_purchase_log.index', [
            'logs' => $logs,
            'filters' => $filters,
            'sourceTypes' => PurchaseSourceType::cases(),
        ]);
    }
}
