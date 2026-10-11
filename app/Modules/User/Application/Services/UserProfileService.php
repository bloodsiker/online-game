<?php

declare(strict_types=1);

namespace App\Modules\User\Application\Services;

use App\Modules\User\Infrastructure\Persistence\Models\UserProfile;

final class UserProfileService
{
    public function save(int $userId, array $data): UserProfile
    {
        return UserProfile::query()->updateOrCreate(['user_id' => $userId], [
            'real_name' => $data['real_name'] ?? null,
            'residence' => $data['residence'] ?? null,
            'motto' => $data['motto'] ?? null,
            'about' => $data['about'] ?? null,
            'birth_date' => empty($data['birth_year']) ? null : sprintf('%04d-%02d-%02d', $data['birth_year'], $data['birth_month'], $data['birth_day']),
        ]);
    }
}
