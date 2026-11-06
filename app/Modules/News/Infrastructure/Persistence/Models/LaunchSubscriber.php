<?php

declare(strict_types=1);

namespace App\Modules\News\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

final class LaunchSubscriber extends Model
{
    protected $table = 'game_launch_subscribers';

    protected $fillable = ['email', 'locale'];

    protected $casts = ['notified_at' => 'datetime'];
}
