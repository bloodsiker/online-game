<?php

declare(strict_types=1);

namespace App\Modules\Auth\Application\Listeners;

use App\Modules\Auth\Domain\Services\UserAgentParser;
use App\Modules\Auth\Infrastructure\GeoIp\GeoIpLocator;
use App\Modules\Auth\Infrastructure\Persistence\Models\LoginHistory;
use Illuminate\Auth\Events\Login;
use Throwable;

/**
 * Пишет одну строку в login_histories при каждом входе (в том числе по «запомнить меня»).
 * Это один INSERT и разбор User-Agent в памяти; ошибка записи не должна ломать вход.
 */
final class RecordLoginHistory
{
    public const SESSION_KEY = 'login_history_id';

    public function __construct(private readonly UserAgentParser $parser, private readonly GeoIpLocator $geo) {}

    public function handle(Login $event): void
    {
        if ($event->guard !== 'web' || ! request()->hasSession()) {
            return;
        }

        try {
            $request = request();
            $userAgent = $request->userAgent();

            $history = LoginHistory::query()->create([
                'user_id' => (int) $event->user->getAuthIdentifier(),
                'remembered' => (bool) $event->remember,
                'ip_address' => $request->ip(),
                'user_agent' => $userAgent !== null ? mb_substr($userAgent, 0, 1000) : null,
                'accept_language' => $this->cut($request->header('Accept-Language'), 120),
                'referer' => $this->cut($request->header('Referer'), 500),
            ] + $this->geo->locate($request->ip()) + $this->parser->parse($userAgent));

            $request->session()->put(self::SESSION_KEY, $history->id);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function cut(?string $value, int $length): ?string
    {
        return $value === null || $value === '' ? null : mb_substr($value, 0, $length);
    }
}
