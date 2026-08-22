<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Item\Domain\Enums\ItemActionType;
use App\Modules\Item\Infrastructure\Persistence\Models\ItemActionLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemActionLogController extends Controller
{
    public function list(Request $request): View
    {
        $filters = [
            'action' => (string) $request->query('action', ''),
            'user' => trim((string) $request->query('user', '')),
            'item' => trim((string) $request->query('item', '')),
        ];

        $logs = ItemActionLog::query()
            ->with(['user', 'targetUser'])
            ->when(ItemActionType::tryFrom($filters['action']) !== null, fn ($query) => $query->where('action', $filters['action']))
            ->when($filters['user'] !== '', fn ($query) => $query->whereHas('user', fn ($q) => $q->where('name', 'like', '%'.$filters['user'].'%')))
            ->when($filters['item'] !== '', fn ($query) => $query->where('item_name', 'like', '%'.$filters['item'].'%'))
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $actionTypes = ItemActionType::cases();

        return view('admin.item_log.list', compact('logs', 'filters', 'actionTypes'));
    }
}
