<?php

declare(strict_types=1);

namespace App\Modules\Backpack\Domain\Models;

use App\Modules\Backpack\Domain\Models\Concerns\InsertsInventoryAtomically;
use Illuminate\Database\Eloquent\Relations\Pivot;

final class BackpackEntry extends Pivot
{
    use InsertsInventoryAtomically;

    protected $table = 'backpacks';

    public $timestamps = false;

    public $incrementing = true;
}
