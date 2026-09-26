<?php

declare(strict_types=1);

namespace App\Modules\Scheduler\Application\Services;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

final class DatabaseBackupService
{
    public function __construct(private readonly Filesystem $files) {}

    /**
     * @return array{path: string, size: int, deleted: int}
     */
    public function execute(?CarbonInterface $now = null): array
    {
        $now = $now === null ? CarbonImmutable::now() : CarbonImmutable::instance($now);
        $root = $this->backupRoot();
        $dayDirectory = $root.DIRECTORY_SEPARATOR.$now->format('Y-m-d');

        $this->files->ensureDirectoryExists($dayDirectory, 0700, true);

        $connection = DB::connection();
        $configuration = $connection->getConfig();
        $driver = (string) ($configuration['driver'] ?? '');

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException('Резервное копирование поддерживает только MySQL/MariaDB.');
        }

        $database = (string) $connection->getDatabaseName();
        if ($database === '') {
            throw new RuntimeException('Не удалось определить имя базы данных.');
        }

        $databaseFileName = preg_replace('/[^A-Za-z0-9_.-]+/', '_', $database) ?: 'database';
        $fileName = sprintf('%s_%s.sql.gz', $databaseFileName, $now->format('Y-m-d_H-i-s'));
        $path = $this->uniquePath($dayDirectory.DIRECTORY_SEPARATOR.$fileName);
        $temporaryPath = $path.'.part';

        try {
            $this->dumpDatabase($configuration, $database, $temporaryPath);

            if (! $this->files->exists($temporaryPath) || $this->files->size($temporaryPath) === 0) {
                throw new RuntimeException('mysqldump создал пустой файл резервной копии.');
            }

            if (! $this->files->move($temporaryPath, $path)) {
                throw new RuntimeException('Не удалось завершить запись резервной копии.');
            }

            @chmod($path, 0600);
        } catch (Throwable $exception) {
            $this->files->delete($temporaryPath);

            throw $exception;
        }

        $deleted = $this->pruneExpiredBackups($now);

        return [
            'path' => $path,
            'size' => $this->files->size($path),
            'deleted' => $deleted,
        ];
    }

    public function pruneExpiredBackups(CarbonInterface $now): int
    {
        $now = CarbonImmutable::instance($now);
        $root = $this->backupRoot();

        if (! $this->files->isDirectory($root)) {
            return 0;
        }

        $today = $now->format('Y-m-d');
        $yesterday = $now->subDay()->format('Y-m-d');
        $deleted = 0;

        foreach ($this->files->directories($root) as $directory) {
            $day = basename($directory);

            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $day) || $day === $today) {
                continue;
            }

            $backupFiles = array_values(array_filter(
                $this->files->files($directory),
                static fn (\SplFileInfo $file): bool => str_ends_with($file->getFilename(), '.sql.gz'),
            ));

            usort(
                $backupFiles,
                static fn (\SplFileInfo $left, \SplFileInfo $right): int => $right->getMTime() <=> $left->getMTime()
                    ?: strcmp($right->getFilename(), $left->getFilename()),
            );

            $filesToDelete = $day === $yesterday ? array_slice($backupFiles, 1) : $backupFiles;

            foreach ($filesToDelete as $file) {
                if ($this->files->delete($file->getPathname())) {
                    $deleted++;
                }
            }

            foreach ($this->files->files($directory) as $file) {
                if (str_ends_with($file->getFilename(), '.part') && $this->files->delete($file->getPathname())) {
                    $deleted++;
                }
            }

            if ($this->files->isEmptyDirectory($directory)) {
                $this->files->deleteDirectory($directory);
            }
        }

        return $deleted;
    }

    /** @param array<string, mixed> $configuration */
    private function dumpDatabase(array $configuration, string $database, string $temporaryPath): void
    {
        if (! function_exists('gzopen')) {
            throw new RuntimeException('Для резервного копирования требуется расширение PHP zlib.');
        }

        $binary = (string) config('database-backup.mysqldump_binary', 'mysqldump');
        $command = [
            $binary,
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            '--events',
            '--no-tablespaces',
            '--default-character-set='.(string) ($configuration['charset'] ?? 'utf8mb4'),
            '--user='.(string) ($configuration['username'] ?? ''),
        ];

        if ($this->supportsOption($binary, '--set-gtid-purged')) {
            $command[] = '--set-gtid-purged=OFF';
        }

        $socket = (string) ($configuration['unix_socket'] ?? '');
        if ($socket !== '') {
            $command[] = '--socket='.$socket;
        } else {
            $command[] = '--host='.(string) ($configuration['host'] ?? '127.0.0.1');
            $command[] = '--port='.(string) ($configuration['port'] ?? 3306);
        }

        $command[] = $database;

        $environment = [];
        $password = (string) ($configuration['password'] ?? '');
        if ($password !== '') {
            $environment['MYSQL_PWD'] = $password;
        }

        $process = new Process(
            command: $command,
            env: $environment,
            timeout: max(60, (int) config('database-backup.timeout_seconds', 1800)),
        );
        $stream = gzopen($temporaryPath, 'wb9');

        if ($stream === false) {
            throw new RuntimeException('Не удалось открыть временный файл резервной копии.');
        }

        $errors = '';

        try {
            $process->start();

            foreach ($process as $type => $buffer) {
                if ($type === Process::OUT) {
                    if (gzwrite($stream, $buffer) === false) {
                        $process->stop();

                        throw new RuntimeException('Ошибка записи сжатой резервной копии.');
                    }

                    continue;
                }

                if (strlen($errors) < 8000) {
                    $errors .= $buffer;
                }
            }
        } finally {
            gzclose($stream);
        }

        if (! $process->isSuccessful()) {
            $message = trim($errors);

            throw new RuntimeException(
                $message === ''
                    ? 'mysqldump завершился с кодом '.(string) $process->getExitCode().'.'
                    : 'mysqldump: '.$message,
            );
        }
    }

    private function backupRoot(): string
    {
        $configuredPath = trim((string) config('database-backup.path'));

        return rtrim(
            $configuredPath !== '' ? $configuredPath : storage_path('app/backups/database'),
            DIRECTORY_SEPARATOR,
        );
    }

    private function supportsOption(string $binary, string $option): bool
    {
        $process = new Process([$binary, '--help']);
        $process->setTimeout(30);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Не удалось запустить '.$binary.'. Проверьте установку MySQL-клиента.');
        }

        return str_contains($process->getOutput().$process->getErrorOutput(), $option);
    }

    private function uniquePath(string $path): string
    {
        if (! $this->files->exists($path) && ! $this->files->exists($path.'.part')) {
            return $path;
        }

        $base = substr($path, 0, -strlen('.sql.gz'));
        $suffix = 1;

        do {
            $candidate = sprintf('%s_%d.sql.gz', $base, $suffix++);
        } while ($this->files->exists($candidate) || $this->files->exists($candidate.'.part'));

        return $candidate;
    }
}
