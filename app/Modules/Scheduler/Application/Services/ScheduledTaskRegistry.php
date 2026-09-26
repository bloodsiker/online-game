<?php

declare(strict_types=1);

namespace App\Modules\Scheduler\Application\Services;

use App\Modules\Clan\Application\UseCases\ProcessExpiredClanTaxes;
use App\Modules\Dungeon\Application\UseCases\ProcessExpiredDungeonRuns;
use App\Modules\Event\Domain\Services\WorldEventLifecycleService;
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

    public const PROCESS_WORLD_EVENTS = 'events.process_world_events';

    public const PROCESS_DUNGEON_RUNS = 'dungeons.process_expired_runs';

    public const DATABASE_BACKUP = 'system.database_backup';

    public function __construct(
        private readonly ConsoleKernel $console,
        private readonly PruneExpiredPlayerInjuries $pruneExpiredPlayerInjuries,
        private readonly ProcessExpiredClanTaxes $processExpiredClanTaxes,
        private readonly ProcessDuePlayerStates $processDuePlayerStates,
        private readonly WorldEventLifecycleService $worldEventLifecycleService,
        private readonly ProcessExpiredDungeonRuns $processExpiredDungeonRuns,
        private readonly DatabaseBackupService $databaseBackupService,
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
            self::PROCESS_WORLD_EVENTS => new ScheduledTaskDefinition(
                key: self::PROCESS_WORLD_EVENTS,
                name: 'Мировые события',
                description: 'Запускает и завершает события, создаёт событийные предметы и удаляет просроченные предметы из рюкзаков.',
                defaultFrequency: ScheduledTaskFrequency::EVERY_MINUTE,
                allowedFrequencies: [ScheduledTaskFrequency::EVERY_MINUTE],
                settingsMutable: false,
                lockSeconds: 60,
            ),
            self::PROCESS_DUNGEON_RUNS => new ScheduledTaskDefinition(
                key: self::PROCESS_DUNGEON_RUNS,
                name: 'Таймеры этажей данжей',
                description: 'Завершает просроченные этажи и выводит участников из данжа.',
                defaultFrequency: ScheduledTaskFrequency::EVERY_FIVE_SECONDS,
                allowedFrequencies: [ScheduledTaskFrequency::EVERY_FIVE_SECONDS],
                settingsMutable: false,
                lockSeconds: 60,
            ),
            self::DATABASE_BACKUP => new ScheduledTaskDefinition(
                key: self::DATABASE_BACKUP,
                name: 'Резервная копия базы данных',
                description: 'Каждый час сохраняет сжатый дамп в storage/app/backups/database. За текущий день хранит все копии, за вчера — только последнюю; более старые удаляет.',
                defaultFrequency: ScheduledTaskFrequency::HOURLY,
                allowedFrequencies: [ScheduledTaskFrequency::HOURLY],
                settingsMutable: false,
                lockSeconds: 3600,
                successHistoryIntervalSeconds: 3600,
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
            self::PROCESS_WORLD_EVENTS => $this->processWorldEvents(),
            self::PROCESS_DUNGEON_RUNS => sprintf(
                'Завершено просроченных прохождений: %d.',
                $this->processExpiredDungeonRuns->execute(now()),
            ),
            self::DATABASE_BACKUP => $this->createDatabaseBackup(),
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

    private function processWorldEvents(): string
    {
        $now = now();
        $stats = $this->worldEventLifecycleService->tick($now);
        $expiredItems = $this->worldEventLifecycleService->deleteExpiredInventoryItems($now);

        return sprintf(
            'Запущено: %d; завершено: %d; создано целей: %d; удалено просроченных предметов: %d.',
            $stats['started'],
            $stats['finished'],
            $stats['spawned'],
            $expiredItems,
        );
    }

    private function createDatabaseBackup(): string
    {
        $result = $this->databaseBackupService->execute();

        return sprintf(
            'Создана копия %s (%s); удалено старых файлов: %d.',
            $this->relativeBackupPath($result['path']),
            $this->formatBytes($result['size']),
            $result['deleted'],
        );
    }

    private function relativeBackupPath(string $path): string
    {
        $storagePath = rtrim(storage_path(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        return str_starts_with($path, $storagePath)
            ? 'storage/'.str_replace(DIRECTORY_SEPARATOR, '/', substr($path, strlen($storagePath)))
            : $path;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' Б';
        }

        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1, ',', ' ').' КБ';
        }

        return number_format($bytes / (1024 * 1024), 1, ',', ' ').' МБ';
    }
}
