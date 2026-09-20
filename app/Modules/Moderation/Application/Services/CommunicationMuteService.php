<?php

declare(strict_types=1);

namespace App\Modules\Moderation\Application\Services;

use App\Modules\Moderation\Domain\Enums\CommunicationScope;
use App\Modules\Moderation\Domain\Events\ChatMuteImposed;
use App\Modules\Moderation\Domain\Events\CommunicationMuteChanged;
use App\Modules\Moderation\Domain\Exceptions\UserMutedException;
use App\Modules\Moderation\Domain\Models\UserCommunicationMute;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CommunicationMuteService
{
    public function __construct(
        private readonly ChatMuteEffectService $chatMuteEffectService,
    ) {}

    public function activeMute(User|int $user, CommunicationScope $scope): ?UserCommunicationMute
    {
        $userId = $user instanceof User ? (int) $user->id : $user;

        return UserCommunicationMute::query()
            ->where('user_id', $userId)
            ->where('scope', $scope)
            ->active()
            ->latest('expires_at')
            ->first();
    }

    public function throwIfMuted(User $user, CommunicationScope $scope): void
    {
        $mute = $this->activeMute($user, $scope);

        if ($mute !== null) {
            throw new UserMutedException($mute);
        }
    }

    public function mute(
        User $user,
        User $moderator,
        CommunicationScope $scope,
        int $durationMinutes,
        ?string $reason = null,
    ): UserCommunicationMute {
        $mute = DB::transaction(function () use ($user, $moderator, $scope, $durationMinutes, $reason): UserCommunicationMute {
            User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            UserCommunicationMute::query()
                ->where('user_id', $user->id)
                ->where('scope', $scope)
                ->active()
                ->update([
                    'revoked_at' => now(),
                    'revoked_by_user_id' => $moderator->id,
                    'updated_at' => now(),
                ]);

            $mute = UserCommunicationMute::query()->create([
                'user_id' => $user->id,
                'imposed_by_user_id' => $moderator->id,
                'scope' => $scope,
                'reason' => filled($reason) ? trim((string) $reason) : null,
                'starts_at' => now(),
                'expires_at' => now()->addMinutes($durationMinutes),
            ]);

            if ($scope === CommunicationScope::Chat) {
                $this->chatMuteEffectService->apply($user->player, $mute->expires_at);
            }

            return $mute;
        });

        if ($scope === CommunicationScope::Chat) {
            $this->refreshPresence($user, $mute->tooltip(), $mute->expires_at->toIso8601String());
            ChatMuteImposed::dispatch(
                (int) $user->id,
                $durationMinutes,
                $mute->reason,
            );
        }

        return $mute;
    }

    public function revoke(UserCommunicationMute $mute, User $moderator): void
    {
        if (! $mute->isActive()) {
            return;
        }

        DB::transaction(function () use ($mute, $moderator): void {
            $mute->update([
                'revoked_at' => now(),
                'revoked_by_user_id' => $moderator->id,
            ]);

            if ($mute->scope === CommunicationScope::Chat) {
                $this->chatMuteEffectService->remove($mute->user->player);
            }
        });

        if ($mute->scope === CommunicationScope::Chat) {
            $this->refreshPresence($mute->user, null, null);
        }
    }

    private function refreshPresence(User $user, ?string $chatMuteTitle, ?string $chatMuteExpiresAt): void
    {
        Cache::forget('who:users_on_location:'.(int) $user->location_id);
        Cache::forget('who:online_users:'.(int) floor(time() / 60));
        CommunicationMuteChanged::dispatch((int) $user->id, $chatMuteTitle, $chatMuteExpiresAt);
    }
}
