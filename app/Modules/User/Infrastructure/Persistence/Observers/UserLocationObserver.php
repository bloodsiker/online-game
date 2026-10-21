<?php

declare(strict_types=1);

namespace App\Modules\User\Infrastructure\Persistence\Observers;

use App\Modules\User\Domain\Events\UserLocationChanged;
use App\Modules\User\Infrastructure\Persistence\Models\User;

final class UserLocationObserver
{
    public function updated(User $user): void
    {
        if ($user->wasChanged('location_id')) {
            UserLocationChanged::dispatch((int) $user->id);
        }
    }
}
