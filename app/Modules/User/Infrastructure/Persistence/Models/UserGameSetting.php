<?php

declare(strict_types=1);

namespace App\Modules\User\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

final class UserGameSetting extends Model
{
    protected $fillable = ['user_id', 'settings'];

    protected function casts(): array
    {
        return ['settings' => 'array'];
    }
}
