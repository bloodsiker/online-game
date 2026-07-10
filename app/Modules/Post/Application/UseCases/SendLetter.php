<?php

declare(strict_types=1);

namespace App\Modules\Post\Application\UseCases;

use App\Modules\Post\Application\DTOs\PostActionResultDTO;
use App\Modules\Post\Infrastructure\Persistence\Models\PostLetter;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class SendLetter
{
    /** Фиксированный налог за отправку письма, в монетах */
    public const TAX = 1;

    public function __construct(
        private readonly GetMailbox $mailbox,
    ) {}

    public function execute(User $sender, string $nick, string $subject, string $text, int $money): PostActionResultDTO
    {
        $nick = trim($nick);
        $subject = trim($subject);
        $text = trim($text);

        if ($subject === '' || $nick === '' || $text === '') {
            return new PostActionResultDTO(false, 'Заполните тему, получателя и текст письма.');
        }

        if ($money < 0) {
            return new PostActionResultDTO(false, 'Неверная сумма.');
        }

        $recipient = User::query()->where('name', $nick)->first();

        if ($recipient === null) {
            return new PostActionResultDTO(false, 'Персонаж не найден.');
        }

        if ($recipient->id === $sender->id) {
            return new PostActionResultDTO(false, 'Нельзя отправить письмо самому себе.');
        }

        $cost = $money + self::TAX;

        if ((int) $sender->money < $cost) {
            return new PostActionResultDTO(false, 'Недостаточно монет (нужно '.$cost.').');
        }

        if ($this->mailbox->inboxCount($recipient) >= GetMailbox::CAPACITY) {
            return new PostActionResultDTO(false, 'Почтовый ящик персонажа '.$recipient->name.' переполнен.');
        }

        DB::transaction(function () use ($sender, $recipient, $subject, $text, $money): void {
            $sender->decrement('money', $money + self::TAX);

            PostLetter::create([
                'sender_user_id' => $sender->id,
                'recipient_user_id' => $recipient->id,
                'subject' => $subject,
                'text' => $text,
                'money' => $money,
            ]);
        });

        return new PostActionResultDTO(true, 'Письмо отправлено персонажу '.$recipient->name.'.');
    }
}
