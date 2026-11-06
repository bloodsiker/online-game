<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Infrastructure\Persistence;

use App\Modules\Telegram\Domain\Contracts\TelegramUpdateRepository;
use Closure;
use Illuminate\Support\Facades\DB;

final class DatabaseTelegramUpdateRepository implements TelegramUpdateRepository
{
    public function once(int $updateId, Closure $reply): array
    {
        return DB::transaction(function () use ($updateId, $reply): array {
            DB::table('telegram_webhook_updates')->insertOrIgnore([
                'update_id' => $updateId, 'created_at' => now(),
            ]);
            $update = DB::table('telegram_webhook_updates')->where('update_id', $updateId)->lockForUpdate()->firstOrFail();
            if ($update->response !== null) {
                return json_decode($update->response, true, flags: JSON_THROW_ON_ERROR);
            }
            $response = $reply();
            DB::table('telegram_webhook_updates')->where('update_id', $updateId)
                ->update(['response' => json_encode($response, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)]);

            return $response;
        }, attempts: 3);
    }

    public function prune(): int
    {
        $deleted = DB::table('telegram_webhook_updates')->where('created_at', '<', now()->subDays(7))->delete();
        $deleted += DB::table('telegram_link_tokens')->where('expires_at', '<', now())->delete();

        return $deleted;
    }
}
