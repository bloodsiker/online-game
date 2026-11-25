<?php

declare(strict_types=1);

namespace App\Modules\SecretKnowledge\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

final class SecretKnowledgeStudy extends Model
{
    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'completes_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function isRunning(): bool
    {
        return $this->expires_at->isFuture();
    }
}
