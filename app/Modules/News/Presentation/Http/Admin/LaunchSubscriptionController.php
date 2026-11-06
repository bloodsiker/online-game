<?php

declare(strict_types=1);

namespace App\Modules\News\Presentation\Http\Admin;

use App\Modules\News\Application\UseCases\GetLaunchSubscriptions;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class LaunchSubscriptionController
{
    public function __invoke(Request $request, GetLaunchSubscriptions $subscriptions): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:254'],
            'status' => ['nullable', 'in:all,pending,sent'],
            'locale' => ['nullable', 'in:all,ru,uk'],
        ]);

        return view('news::admin.subscriptions.index', [
            'subscribers' => $subscriptions->execute(
                search: trim($filters['q'] ?? ''),
                status: $filters['status'] ?? 'all',
                locale: $filters['locale'] ?? 'all',
            ),
            'filters' => $filters,
        ]);
    }
}
