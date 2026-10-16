<?php

declare(strict_types=1);

namespace App\Modules\Structure\Prison\Application\Services;

use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Prison\Infrastructure\Persistence\Models\PlayerDetention;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DetentionService
{
    public function activeFor(User $user): ?PlayerDetention
    {
        return PlayerDetention::query()
            ->with('prison')
            ->where('user_id', $user->id)
            ->whereNull('released_at')
            ->where('release_at', '>', now())
            ->latest('id')
            ->first();
    }

    public function impose(
        User $user,
        Structure $prison,
        User $admin,
        int $minutes,
        string $reason,
        int $fineMoney = 0,
        float $fineDiamond = 0.0,
    ): PlayerDetention {
        if (! $prison->isPrison() || $prison->location_id === null) {
            throw ValidationException::withMessages(['prison_structure_id' => 'Выберите Темницу с назначенной локацией.']);
        }

        if ($fineMoney < 0 || $fineDiamond < 0) {
            throw ValidationException::withMessages(['fine_money' => 'Сумма штрафа не может быть отрицательной.']);
        }

        return DB::transaction(function () use ($user, $prison, $admin, $minutes, $reason, $fineMoney, $fineDiamond): PlayerDetention {
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $activeDetention = PlayerDetention::query()
                ->where('user_id', $lockedUser->id)
                ->whereNull('released_at')
                ->where('release_at', '>', now())
                ->lockForUpdate()
                ->first();

            if ($activeDetention !== null) {
                throw ValidationException::withMessages([
                    'player' => 'Игрок уже отбывает наказание до '.$activeDetention->release_at->format('d.m.Y H:i').'.',
                ]);
            }

            $detention = PlayerDetention::query()->create([
                'user_id' => $lockedUser->id,
                'user_name' => $lockedUser->name,
                'prison_structure_id' => $prison->id,
                'prison_location_id' => $prison->location_id,
                'prison_name' => $prison->name,
                'imposed_by_user_id' => $admin->id,
                'imposed_by_name' => $admin->name,
                'previous_location_id' => $lockedUser->location_id,
                'reason' => $reason,
                'fine_money' => $fineMoney,
                'fine_diamond' => $fineDiamond,
                'starts_at' => now(),
                'release_at' => now()->addMinutes($minutes),
            ]);

            // Detention is a virtual structure context; keep the player's real location intact.
            Cache::forget('who:users_on_location:'.$detention->previous_location_id);

            return $detention;
        });
    }

    /** Returns the user to the saved location once their term has elapsed. */
    public function releaseIfExpired(User $user): bool
    {
        return DB::transaction(function () use ($user): bool {
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $detention = PlayerDetention::query()
                ->where('user_id', $user->id)
                ->whereNull('released_at')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($detention === null || $detention->release_at->isFuture()) {
                return false;
            }

            $detention->released_at = now();
            $detention->save();

            // Restore legacy detainees who were physically moved to the prison location.
            // New detentions never change location_id, so expiry leaves them where they were.
            if ($detention->previous_location_id !== null
                && (int) $lockedUser->location_id === (int) $detention->prison_location_id) {
                $lockedUser->prev_location_id = $lockedUser->location_id;
                $lockedUser->location_id = $detention->previous_location_id;
                $lockedUser->save();
                $user->location_id = $lockedUser->location_id;
                $user->prev_location_id = $lockedUser->prev_location_id;
                $user->unsetRelation('currentLocation');
                $this->forgetLocationCaches((int) $detention->prison_location_id, (int) $detention->previous_location_id);
            }

            return true;
        });
    }

    /** @return array{money:int, diamond:float} */
    public function buyOut(User $user, int $prisonStructureId): array
    {
        return DB::transaction(function () use ($user, $prisonStructureId): array {
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $detention = PlayerDetention::query()
                ->where('user_id', $lockedUser->id)
                ->where('prison_structure_id', $prisonStructureId)
                ->whereNull('released_at')
                ->where('release_at', '>', now())
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($detention === null) {
                throw ValidationException::withMessages(['detention' => 'Активное заключение не найдено.']);
            }

            $amounts = $detention->currentBuyoutAmounts();
            if ($amounts['money'] <= 0 && $amounts['diamond'] <= 0) {
                throw ValidationException::withMessages(['detention' => 'Для этого наказания выкуп недоступен.']);
            }

            if ((int) $lockedUser->money < $amounts['money']) {
                throw ValidationException::withMessages(['money' => 'Недостаточно монет для выкупа. Нужно '.$amounts['money'].'.']);
            }

            if ((float) $lockedUser->diamond < $amounts['diamond']) {
                throw ValidationException::withMessages(['diamond' => 'Недостаточно бриллиантов для выкупа. Нужно '.number_format($amounts['diamond'], 2, '.', '').'.']);
            }

            if ($amounts['money'] > 0) {
                $lockedUser->decrement('money', $amounts['money']);
            }
            if ($amounts['diamond'] > 0) {
                $lockedUser->decrement('diamond', $amounts['diamond']);
            }
            $detention->released_at = now();
            $detention->save();

            return $amounts;
        });
    }

    public function blockLeaving(User $user): ?string
    {
        $this->releaseIfExpired($user);
        $detention = $this->activeFor($user);
        if ($detention === null) {
            return null;
        }

        return 'Вы находитесь в Темнице. Освобождение: '.$detention->release_at->format('d.m.Y H:i').'.';
    }

    private function forgetLocationCaches(int $oldLocationId, int $newLocationId): void
    {
        Cache::forget('who:users_on_location:'.$oldLocationId);
        Cache::forget('who:users_on_location:'.$newLocationId);
    }
}
