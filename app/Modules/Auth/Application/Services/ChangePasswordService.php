<?php

declare(strict_types=1);

namespace App\Modules\Auth\Application\Services;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class ChangePasswordService
{
    public function execute(User $user, string $currentPassword, string $newPassword): User
    {
        return DB::transaction(function () use ($user, $currentPassword, $newPassword): User {
            $lockedUser = User::query()->without('player')->lockForUpdate()->findOrFail($user->id);

            if (! Hash::check($currentPassword, $lockedUser->password)) {
                throw ValidationException::withMessages([
                    'current_password' => 'Текущий пароль указан неверно.',
                ]);
            }

            // Модель использует cast hashed: открытый пароль в БД не попадает.
            $lockedUser->password = $newPassword;
            $lockedUser->setRememberToken(Str::random(60));
            $lockedUser->save();

            return $lockedUser;
        });
    }
}
