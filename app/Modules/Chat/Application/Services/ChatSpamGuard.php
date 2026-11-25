<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Services;

use App\Modules\Chat\Domain\Events\ChatMessageExpired;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Moderation\Application\Services\CommunicationMuteService;
use App\Modules\Moderation\Domain\Enums\CommunicationScope;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

/**
 * Автоматическое молчание за спам в чате. Срабатывает после отправки сообщения игроком:
 *  - флуд: FLOOD_MESSAGES и больше сообщений за FLOOD_SECONDS секунд;
 *  - повтор: REPEAT_MESSAGES одинаковых сообщений за REPEAT_SECONDS секунд
 *    (сравниваются только буквы и цифры — приписки «+», «--», «*-*» не спасают).
 * При срабатывании игроку накладывается молчание, а его сообщения за последние REPEAT_SECONDS секунд
 * удаляются из чата (у открытых чатов они исчезают сразу, без перезагрузки).
 */
final readonly class ChatSpamGuard
{
    public const FLOOD_MESSAGES = 6;

    public const FLOOD_SECONDS = 10;

    public const REPEAT_MESSAGES = 4;

    public const REPEAT_SECONDS = 60;

    public const MUTE_MINUTES = 30;

    public const REASON = 'Спам';

    public function __construct(private CommunicationMuteService $mutes) {}

    /** @return bool true — игроку наложено молчание */
    public function inspect(User $sender): bool
    {
        if ($sender->is_admin || $this->mutes->activeMute($sender, CommunicationScope::Chat) !== null) {
            return false;
        }

        $recent = ChatMessage::query()
            ->where('user_id', $sender->id)
            ->where('created_at', '>=', now()->subSeconds(self::REPEAT_SECONDS))
            ->latest('id')
            ->limit(50)
            ->get();

        $flood = $recent->filter(fn (ChatMessage $message): bool => $message->created_at->gte(now()->subSeconds(self::FLOOD_SECONDS)))->count();
        $repeats = (int) ($recent
            ->map(fn (ChatMessage $message): string => self::fingerprint((string) $message->message))
            ->filter(fn (string $fingerprint): bool => $fingerprint !== '')
            ->countBy()
            ->max() ?? 0);

        if ($flood < self::FLOOD_MESSAGES && $repeats < self::REPEAT_MESSAGES) {
            return false;
        }

        $this->mutes->mute($sender, null, CommunicationScope::Chat, self::MUTE_MINUTES, self::REASON);
        $this->removeSpam($recent);

        return true;
    }

    /** @param  Collection<int, ChatMessage>  $messages */
    private function removeSpam(Collection $messages): void
    {
        foreach ($messages as $message) {
            ChatMessageExpired::dispatch($message);
            $message->delete();
        }
    }

    /** Текст без регистра, пробелов и знаков: «Играйте с нами +» и «играйте с нами --» совпадут. */
    public static function fingerprint(string $message): string
    {
        return mb_strtolower((string) preg_replace('/[^\pL\pN]+/u', '', strip_tags($message)));
    }
}
