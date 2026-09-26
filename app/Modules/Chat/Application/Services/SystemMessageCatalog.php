<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Services;

use App\Modules\Chat\Domain\ChatMessageLifetime;
use App\Modules\Chat\Domain\Enums\ChatMessageType;

final class SystemMessageCatalog
{
    /**
     * @return list<array{
     *     type: string,
     *     title: string,
     *     color: string,
     *     appearance: string,
     *     audience: string,
     *     lifetime: string,
     *     icon: string,
     *     bold: bool,
     *     italic: bool,
     *     sendable: bool
     * }>
     */
    public function entries(): array
    {
        $personalLifetime = ChatMessageLifetime::PERSONAL_MINUTES.' минут';

        return [
            [
                'type' => ChatMessageType::System->value,
                'title' => 'Глобальное системное',
                'color' => '#cc00ff',
                'appearance' => 'Фиолетовое, жирное, значок ★',
                'audience' => 'Все игроки во всех каналах',
                'lifetime' => ChatMessageLifetime::SYSTEM_MINUTES.' минут',
                'icon' => '★',
                'bold' => true,
                'italic' => false,
                'sendable' => true,
            ],
            [
                'type' => ChatMessageType::Information->value,
                'title' => 'Информационное',
                'color' => '#df5d03',
                'appearance' => 'Оранжевое, жирное, значок ✔',
                'audience' => 'Один игрок',
                'lifetime' => $personalLifetime,
                'icon' => '✔',
                'bold' => true,
                'italic' => false,
                'sendable' => true,
            ],
            [
                'type' => ChatMessageType::WorldEvent->value,
                'title' => 'Событие',
                'color' => '#000000',
                'appearance' => 'Чёрное, жирное',
                'audience' => 'Все игроки во всех каналах',
                'lifetime' => ChatMessageLifetime::SYSTEM_MINUTES.' минут',
                'icon' => '',
                'bold' => true,
                'italic' => false,
                'sendable' => false,
            ],
            [
                'type' => ChatMessageType::PartyInvite->value,
                'title' => 'Приглашение в группу',
                'color' => '#000000',
                'appearance' => 'Чёрное, курсив',
                'audience' => 'Один игрок',
                'lifetime' => $personalLifetime,
                'icon' => '',
                'bold' => false,
                'italic' => true,
                'sendable' => false,
            ],
            [
                'type' => ChatMessageType::PartyNotice->value,
                'title' => 'Уведомление группы',
                'color' => '#000000',
                'appearance' => 'Чёрное, курсив',
                'audience' => 'Один игрок или канал группы',
                'lifetime' => '10 минут для персонального; в группе — без таймера',
                'icon' => '',
                'bold' => false,
                'italic' => true,
                'sendable' => false,
            ],
            [
                'type' => ChatMessageType::Quest->value,
                'title' => 'Квестовое',
                'color' => '#000000',
                'appearance' => 'Чёрное, курсив',
                'audience' => 'Один игрок',
                'lifetime' => $personalLifetime,
                'icon' => '',
                'bold' => false,
                'italic' => true,
                'sendable' => true,
            ],
            [
                'type' => ChatMessageType::QuestItem->value,
                'title' => 'Квестовый предмет',
                'color' => '#009900',
                'appearance' => 'Зелёное, жирное, значок ✦',
                'audience' => 'Один игрок',
                'lifetime' => $personalLifetime,
                'icon' => '✦',
                'bold' => true,
                'italic' => false,
                'sendable' => true,
            ],
            [
                'type' => ChatMessageType::Loot->value,
                'title' => 'Добыча',
                'color' => '#000000',
                'appearance' => 'Чёрное, курсив',
                'audience' => 'Один игрок',
                'lifetime' => $personalLifetime,
                'icon' => '',
                'bold' => false,
                'italic' => true,
                'sendable' => true,
            ],
        ];
    }

    /** @return list<array<string, bool|string>> */
    public function sendableEntries(): array
    {
        return array_values(array_filter(
            $this->entries(),
            static fn (array $entry): bool => $entry['sendable'],
        ));
    }

    /** @return list<string> */
    public function sendableValues(): array
    {
        return array_column($this->sendableEntries(), 'type');
    }
}
