<?php

declare(strict_types=1);

namespace App\Modules\User\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

final class UserProfile extends Model
{
    protected $fillable = ['user_id', 'sex', 'real_name', 'birth_date', 'residence', 'motto', 'about', 'avatar'];

    protected function casts(): array
    {
        return ['birth_date' => 'date'];
    }
}
