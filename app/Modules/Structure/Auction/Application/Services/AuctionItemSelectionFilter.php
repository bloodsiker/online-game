<?php

declare(strict_types=1);

namespace App\Modules\Structure\Auction\Application\Services;

use Illuminate\Database\Eloquent\Builder;

final class AuctionItemSelectionFilter
{
    public function apply(Builder $query, array $filter): Builder
    {
        if (($filter['name'] ?? '') !== '') {
            $locale = app()->getLocale();
            $name = '%'.$filter['name'].'%';
            $query->where(function (Builder $query) use ($locale, $name): void {
                $query->where('share_items.name->'.$locale, 'like', $name);
                if ($locale !== 'ru') {
                    $query->orWhere('share_items.name->ru', 'like', $name);
                }
            });
        }

        if (($filter['type'] ?? '') !== '') {
            $query->where('share_items.type', $filter['type']);
        }

        return $query;
    }
}
