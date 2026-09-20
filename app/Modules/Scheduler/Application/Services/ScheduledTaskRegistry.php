<?php

declare(strict_types=1);

namespace App\Modules\Scheduler\Application\Services;

use App\Modules\Clan\Application\UseCases\ProcessExpiredClanTaxes;
use App\Modules\Interface\Application\UseCases\ProcessDuePlayerStates;
use App\Modules\Player\Application\UseCases\PruneExpiredPlayerInjuries;
use App\Modules\Scheduler\Domain\DTOs\ScheduledTaskDefinition;
use App\Modules\Scheduler\Domain\Enums\ScheduledTaskFrequency;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Output\BufferedOutput;

class ScheduledTaskRegistry
{
    public const DELETE_EXPIRED_LOCATION_ITEMS = 'items.delete_expired_location';

    public const PRUNE_EXPIRED_INJURIES = 'players.prune_expired_injuries';

    public const PROCESS_EXPIRED_CLAN_TAXES = 'clans.process_expired_taxes';

    public const PROCESS_PLAYER_STATES = 'players.process_state';

    public function __construct(
        private readonly ConsoleKernel $console,
        private readonly PruneExpiredPlayerInjuries $pruneExpiredPlayerInjuries,
        private readonly ProcessExpiredClanTaxes $processExpiredClanTaxes,
        private readonly ProcessDuePlayerStates $processDuePlayerStates,
    ) {}

    /** @return array<string, ScheduledTaskDefinition> */
    public function all(): array
    {
        return [
            self::DELETE_EXPIRED_LOCATION_ITEMS => new ScheduledTaskDefinition(
                key: self::DELETE_EXPIRED_LOCATION_ITEMS,
                name: 'Очистка предметов на локациях',
                description: 'Удаляет просроченные предметы, оставленные на игровых локациях.',
                defaultFrequency: ScheduledTaskFrequency::EVERY_MINUTE,
                allowedFrequencies: [
                    ScheduledTaskFrequency::EVERY_MINUTE,
                    ScheduledTaskFrequency::EVERY_FIVE_MINUTES,
                    ScheduledTaskFrequency::EVERY_FIFTEEN_MINUTES,
                    ScheduledTaskFrequency::HOURLY,
                ],
            ),
            self::PRUNE_EXPIRED_INJURIES => new ScheduledTaskDefinition(
                key: self::PRUNE_EXPIRED_INJURIES,
                name: 'Завершение травм',
                description: 'Снимает истёкшие травмы и освобождает занятые бинтами слоты экипировки.',
                defaultFrequency: ScheduledTaskFrequency::EVERY_MINUTE,
                allowedFrequencies: [
                    ScheduledTaskFrequency::EVERY_MINUTE,
                    ScheduledTaskFrequency::EVERY_FIVE_MINUTES,
                    ScheduledTaskFrequency::EVERY_FIFTEEN_MINUTES,
                ],
            ),
            self::PROCESS_EXPIRED_CLAN_TAXES => new ScheduledTaskDefinition(
                key: self::PROCESS_EXPIRED_CLAN_TAXES,
                name: 'Просроченные налоги кланов',
                description: 'Применяет ограничения к кланам, у которых закончился оплаченный период.',
                defaultFrequency: ScheduledTaskFrequency::EVERY_MINUTE,
                allowedFrequencies: [
                    ScheduledTaskFrequency::EVERY_MINUTE,
                    ScheduledTaskFrequency::EVERY_FIVE_MINUTES,
                    ScheduledTaskFrequency::EVERY_FIFTEEN_MINUTES,
                    ScheduledTaskFrequency::HOURLY,
                ],
            ),
            self::PROCESS_PLAYER_STATES => new ScheduledTaskDefinition(
                key: self::PROCESS_PLAYER_STATES,
                name: 'Состояния игроков',
                description: 'Обрабатывает регенерацию, периодический урон и истечение активных эффектов.',
                defaultFrequency: ScheduledTaskFrequency::EVERY_FIVE_SECONDS,
                allowedFrequencies: [ScheduledTaskFrequency::EVERY_FIVE_SECONDS],
                settingsMutable: false,
                lockSeconds: 60,
            ),
        ];
    }

    public function find(string $key): ScheduledTaskDefinition
    {
        return $this->all()[$key] ?? throw new InvalidArgumentException('Неизвестная задача планировщика.');
    }

    public function execute(string $key): string
    {
        return match ($key) {
            self::DELETE_EXPIRED_LOCATION_ITEMS => $this->deleteExpiredLocationItems(),
            self::PRUNE_EXPIRED_INJURIES => sprintf(
                'Удалено истёкших травм: %d.',
                $this->pruneExpiredPlayerInjuries->execute(),
            ),
            self::PROCESS_EXPIRED_CLAN_TAXES => sprintf(
                'Обработано кланов: %d.',
                $this->processExpiredClanTaxes->execute(now()),
            ),
            self::PROCESS_PLAYER_STATES => sprintf(
                'Обработано игроков: %d.',
                $this->processDuePlayerStates->execute(now()),
            ),
            default => throw new InvalidArgumentException('Неизвестная задача планировщика.'),
        };
    }

    private function deleteExpiredLocationItems(): string
    {
        $output = new BufferedOutput;
        $exitCode = $this->console->call('items:delete-expired-location', [], $output);

        if ($exitCode !== 0) {
            throw new RuntimeException('Команда очистки предметов завершилась с кодом '.$exitCode.'.');
        }

        return trim($output->fetch()) ?: 'Очистка завершена.';
    }
}
