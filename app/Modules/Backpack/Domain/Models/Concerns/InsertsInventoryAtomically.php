<?php

declare(strict_types=1);

namespace App\Modules\Backpack\Domain\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait InsertsInventoryAtomically
{
    protected function performInsert(Builder $query)
    {
        return $this->getConnection()->transaction(fn () => parent::performInsert($query));
    }
}
