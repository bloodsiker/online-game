<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Application\Mappers;

use App\Modules\Structure\Bank\Application\DTOs\BankPageDTO;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class BankPageViewMapper
{
    public function map(User $user, LengthAwarePaginator $logs): BankPageDTO
    {
        $currentPage = $logs->currentPage();
        $lastPage = $logs->lastPage();
        $pageFrom = max(1, $currentPage - 5);
        $pageTo = min($lastPage, $currentPage + 5);
        $urls = [];

        for ($page = 1; $page <= $lastPage; $page++) {
            $urls[$page] = $logs->url($page);
        }

        return new BankPageDTO(
            bankAccount: (string) $user->bank_account,
            money: (int) $user->money,
            diamond: (int) $user->diamond,
            bankBalance: (int) $user->bank_balance,
            logs: collect($logs->items())->map(fn ($log) => [
                'createdAt' => $log->created_at->format('d.m.Y H:i'),
                'amount' => $log->amount,
                'balanceAfter' => $log->balance_after,
                'relatedUserName' => $log->relatedUser?->name ?? '—',
                'actionValue' => $log->action->value,
                'actionLabel' => $log->action->label(),
            ])->all(),
            pagination: [
                'hasPages' => $logs->hasPages(),
                'currentPage' => $currentPage,
                'lastPage' => $lastPage,
                'pageFrom' => $pageFrom,
                'pageTo' => $pageTo,
                'urls' => $urls,
            ],
        );
    }
}
