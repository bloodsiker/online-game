<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Infrastructure\Persistence;

use App\Modules\Telegram\Domain\Contracts\TelegramLinkRepository;
use App\Modules\Telegram\Domain\DTO\TelegramAccount;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DatabaseTelegramLinkRepository implements TelegramLinkRepository
{
    public function forUser(int $userId): ?TelegramAccount
    {
        return $this->account(DB::table('telegram_links')->where('user_id', $userId)->first());
    }

    public function forTelegram(int $telegramUserId): ?TelegramAccount
    {
        return $this->account(DB::table('telegram_links')->where('telegram_user_id', $telegramUserId)->first());
    }

    public function createToken(int $userId): string
    {
        return DB::transaction(function () use ($userId): string {
            DB::table('users')->where('id', $userId)->lockForUpdate()->firstOrFail();
            if ($this->forUser(userId: $userId)) {
                throw ValidationException::withMessages(['telegram' => __('telegram.already_linked')]);
            }
            $token = bin2hex(random_bytes(24));
            DB::table('telegram_link_tokens')->updateOrInsert(['user_id' => $userId], [
                'token_hash' => hash('sha256', $token),
                'expires_at' => now()->addMinutes(config('telegram.link_ttl_minutes')),
            ]);

            return $token;
        }, attempts: 3);
    }

    public function consumeToken(string $token, int $telegramUserId, ?string $username): TelegramAccount
    {
        if (! preg_match('/^[a-f0-9]{48}$/D', $token) || $telegramUserId <= 0) {
            throw ValidationException::withMessages(['telegram' => __('telegram.invalid_link')]);
        }
        $hash = hash('sha256', $token);
        $userId = DB::table('telegram_link_tokens')->where('token_hash', $hash)->value('user_id');
        if (! $userId) {
            throw ValidationException::withMessages(['telegram' => __('telegram.invalid_link')]);
        }

        try {
            return DB::transaction(function () use ($userId, $hash, $telegramUserId, $username): TelegramAccount {
                // All settings/link operations lock the owner first, then the token/link rows.
                $user = DB::table('users')->where('id', $userId)->lockForUpdate()->first();
                $pending = DB::table('telegram_link_tokens')->where('user_id', $userId)
                    ->where('token_hash', $hash)->lockForUpdate()->first();
                if (! $user || ! $user->email_verified_at || ! $pending || CarbonImmutable::parse($pending->expires_at)->isPast()) {
                    throw ValidationException::withMessages(['telegram' => __('telegram.invalid_link')]);
                }
                if ($this->forUser(userId: (int) $userId) || $this->forTelegram(telegramUserId: $telegramUserId)) {
                    throw ValidationException::withMessages(['telegram' => __('telegram.account_in_use')]);
                }
                DB::table('telegram_links')->insert([
                    'user_id' => $userId, 'telegram_user_id' => $telegramUserId,
                    'username' => $username, 'linked_at' => now(),
                ]);
                DB::table('telegram_link_tokens')->where('user_id', $userId)->delete();

                return new TelegramAccount(userId: (int) $userId, telegramUserId: $telegramUserId, username: $username);
            }, attempts: 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['telegram' => __('telegram.account_in_use')]);
        }
    }

    public function unlink(int $userId): void
    {
        DB::transaction(function () use ($userId): void {
            DB::table('users')->where('id', $userId)->lockForUpdate()->firstOrFail();
            DB::table('telegram_link_tokens')->where('user_id', $userId)->delete();
            DB::table('telegram_links')->where('user_id', $userId)->delete();
        }, attempts: 3);
    }

    private function account(?object $row): ?TelegramAccount
    {
        return $row ? new TelegramAccount(userId: (int) $row->user_id,
            telegramUserId: (int) $row->telegram_user_id, username: $row->username) : null;
    }
}
