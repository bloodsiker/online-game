<?php

declare(strict_types=1);

namespace App\Modules\Post\Application\UseCases;

use App\Modules\Post\Application\DTOs\PostActionResultDTO;
use App\Modules\Post\Application\Services\BroadcastMailboxUnreadState;
use App\Modules\Post\Infrastructure\Persistence\Models\PostLetter;
use App\Modules\User\Domain\Services\WalletBalanceLimits;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class SendLetter
{
    /** Фиксированный налог за отправку письма, в монетах */
    public const TAX = 1;

    public function __construct(
        private readonly GetMailbox $mailbox,
        private readonly BroadcastMailboxUnreadState $unreadState,
    ) {}

    public function execute(User $sender, string $nick, string $subject, string $text, int $money): PostActionResultDTO
    {
        $nick = trim($nick);
        $subject = trim($subject);
        $text = trim($text);

        if ($subject === '' || $nick === '' || $text === '') {
            return new PostActionResultDTO(false, 'Заполните тему, получателя и текст письма.');
        }

        if ($money < 0 || $money > WalletBalanceLimits::MAX_BALANCE - self::TAX) {
            return new PostActionResultDTO(false, 'Неверная сумма.');
        }

        $recipient = User::without('player')->where('name', $nick)->first();

        if ($recipient === null) {
            return new PostActionResultDTO(false, 'Персонаж не найден.');
        }

        if ($recipient->id === $sender->id) {
            return new PostActionResultDTO(false, 'Нельзя отправить письмо самому себе.');
        }

        $cost = $money + self::TAX;

        return DB::transaction(function () use ($sender, $recipient, $subject, $text, $money, $cost): PostActionResultDTO {
            // Same ordering as bank transfers; prevents reciprocal sends from deadlocking.
            $users = collect([$sender->id, $recipient->id])->unique()->sort()->mapWithKeys(
                fn ($id): array => [$id => User::without('player')->whereKey($id)->lockForUpdate()->firstOrFail()],
            );
            $lockedSender = $users->get($sender->id);
            $lockedRecipient = $users->get($recipient->id);
            if ((int) $lockedSender->money < $cost) {
                return new PostActionResultDTO(false, 'Недостаточно монет (нужно '.$cost.').');
            }
            if ($this->mailbox->inboxCount($lockedRecipient) >= GetMailbox::CAPACITY) {
                return new PostActionResultDTO(false, 'Почтовый ящик персонажа '.$lockedRecipient->name.' переполнен.');
            }
            $spent = User::query()->whereKey($lockedSender->id)->where('money', '>=', $cost)->decrement('money', $cost);
            if ($spent !== 1) {
                return new PostActionResultDTO(false, 'Недостаточно монет (нужно '.$cost.').');
            }
            PostLetter::create([
                'sender_user_id' => $lockedSender->id,
                'recipient_user_id' => $lockedRecipient->id,
                'subject' => $subject,
                'text' => $text,
                'money' => $money,
            ]);
            $this->unreadState->markUnread($lockedRecipient);

            return new PostActionResultDTO(true, 'Письмо отправлено персонажу '.$lockedRecipient->name.'.');
        });
    }
}
