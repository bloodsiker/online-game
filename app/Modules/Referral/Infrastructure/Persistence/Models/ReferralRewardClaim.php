<?php

declare(strict_types=1);

namespace App\Modules\Referral\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralRewardClaim extends Model
{
    public $timestamps = false;

    protected $fillable = ['referral_id', 'stage_id', 'claimed_at'];

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(ReferralRewardStage::class);
    }
}
