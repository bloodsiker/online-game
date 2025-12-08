<?php

namespace App\Services\Battle;

use Illuminate\Support\Collection;

class AggressionChecker {
    public function getAggressive(Collection $monsters): Collection
    {
        return $monsters->filter(function ($locMonster) {
            return mt_rand(0, 100) < $locMonster->monster->aggression;
        });
    }
}
