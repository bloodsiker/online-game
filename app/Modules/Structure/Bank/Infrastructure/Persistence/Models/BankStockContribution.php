<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankStockContribution extends Model
{
    protected $fillable = ['stock_id', 'user_id', 'amount'];

    protected $casts = [
        'amount' => 'float',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(BankStock::class, 'stock_id');
    }
}