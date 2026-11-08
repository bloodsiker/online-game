<?php

declare(strict_types=1);

namespace App\Modules\Telegram\Application\Listeners;

use App\Modules\Referral\Infrastructure\Persistence\Models\Referral;
use App\Modules\Telegram\Application\Jobs\SendAdminTelegramMessage;
use App\Modules\User\Domain\Events\UserRegistered;
use App\Modules\User\Infrastructure\Persistence\Models\User;

final class NotifyAdminAboutRegistration
{
    public function handle(UserRegistered $event): void
    {
        if (! SendAdminTelegramMessage::enabled()) {
            return;
        }

        SendAdminTelegramMessage::dispatch($this->message(user: $event->user->load(['player.race', 'profile'])));
    }

    public function message(User $user): string
    {
        $race = $user->player?->race?->getTranslation('name', 'ru');
        $sex = match ($user->sex ?? $user->profile?->sex) {
            'male' => 'муж.',
            'female' => 'жен.',
            default => null,
        };
        $referrer = Referral::query()->with('referrer')->where('referred_user_id', $user->id)->first()?->referrer;

        $lines = [
            '🆕 <b>Новая регистрация</b>',
            '',
            '👤 <a href="'.e(route('info.user', ['id' => $user->id])).'">'.e($user->name).'</a> (#'.$user->id.')',
            '✉️ '.e((string) $user->email),
        ];
        if ($race !== null || $sex !== null) {
            $lines[] = '🧬 '.e(implode(', ', array_filter([$race, $sex])));
        }
        if ($referrer !== null) {
            $lines[] = '🤝 Пришёл по рефералке от '.e($referrer->name);
        }
        $lines[] = '';
        $lines[] = '📊 Всего игроков: '.User::query()->count();

        return implode("\n", $lines);
    }
}
