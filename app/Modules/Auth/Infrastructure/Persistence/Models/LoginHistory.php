<?php

declare(strict_types=1);

namespace App\Modules\Auth\Infrastructure\Persistence\Models;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginHistory extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'remembered' => 'boolean',
        'save_data' => 'boolean',
        'pixel_ratio' => 'float',
        'downlink' => 'float',
        'device_memory' => 'float',
        'client_reported_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function placeLabel(): string
    {
        $parts = array_filter([$this->country, $this->city !== $this->region ? $this->region : null, $this->city]);

        return $parts === [] ? '—' : implode(', ', $parts);
    }

    /** Экран, сеть и прочее одной строкой для таблиц админки. */
    public function screenLabel(): string
    {
        return $this->screen_width ? $this->screen_width.'×'.$this->screen_height : '—';
    }

    public function connectionLabel(): string
    {
        if ($this->connection_type === null) {
            return '—';
        }

        $parts = [$this->connection_type];
        if ($this->downlink !== null) {
            $parts[] = rtrim(rtrim(number_format($this->downlink, 2, '.', ''), '0'), '.').' Мбит/с';
        }
        if ($this->rtt !== null) {
            $parts[] = $this->rtt.' мс';
        }
        if ($this->save_data) {
            $parts[] = 'экономия трафика';
        }

        return implode(', ', $parts);
    }
}
