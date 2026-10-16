<?php

declare(strict_types=1);

namespace App\Modules\Chat\Application\Listeners;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Player\Domain\Events\PlayerInjured;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerInjury;

final readonly class SendPlayerInjuryMessage
{
    public function __construct(private ChatService $chatService) {}

    public function handle(PlayerInjured $event): void
    {
        $injury = $event->injury;
        $nameTranslations = $injury->injuryType?->getTranslations('name') ?? [];
        $nameTranslations['ru'] ??= $injury->displayName();
        if (empty($nameTranslations['uk'])) {
            $nameTranslations['uk'] = $this->defaultUkrainianInjuryName($injury, $nameTranslations['ru']);
        }
        $durationMinutes = max(
            1,
            (int) ceil($injury->applied_at->diffInSeconds($injury->expires_at) / 60),
        );

        $this->chatService->sendSystemToUser(
            $event->player->user,
            new LocalizedChatMessage('chat.injury', [
                'name' => ['translations' => $nameTranslations],
                'minutes' => $durationMinutes,
            ]),
        );
    }

    private function defaultUkrainianInjuryName(PlayerInjury $injury, string $russianName): string
    {
        $parts = [
            'head' => ['ru' => 'головы', 'uk' => 'голови'],
            'shoulders' => ['ru' => 'плеч', 'uk' => 'плечей'],
            'forearms' => ['ru' => 'предплечий', 'uk' => 'передпліччя'],
            'left_hand' => ['ru' => 'левой руки', 'uk' => 'лівої руки'],
            'right_hand' => ['ru' => 'правой руки', 'uk' => 'правої руки'],
            'torso' => ['ru' => 'туловища', 'uk' => 'тулуба'],
            'chest' => ['ru' => 'груди', 'uk' => 'грудей'],
            'legs' => ['ru' => 'ног', 'uk' => 'ніг'],
            'feet' => ['ru' => 'ступней', 'uk' => 'стоп'],
        ];
        $severityNames = [
            1 => ['ru' => 'Лёгкая', 'uk' => 'Легка'],
            2 => ['ru' => 'Средняя', 'uk' => 'Середня'],
            3 => ['ru' => 'Тяжёлая', 'uk' => 'Важка'],
        ];
        $part = $parts[$injury->body_part->value] ?? null;
        $severity = $severityNames[$injury->severity->value] ?? null;

        if ($part === null || $severity === null) {
            return $russianName;
        }

        $defaultRussianName = $severity['ru'].' травма '.$part['ru'];
        if (mb_strtolower(trim($russianName)) !== mb_strtolower($defaultRussianName)) {
            return $russianName;
        }

        return $severity['uk'].' травма '.$part['uk'];
    }
}
