<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Scheduler;

use App\Modules\Scheduler\Application\Services\DatabaseBackupService;
use Carbon\CarbonImmutable;
use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

class DatabaseBackupServiceTest extends TestCase
{
    private Filesystem $files;

    private string $backupRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = new Filesystem;
        $this->backupRoot = storage_path('framework/testing/database-backups-'.bin2hex(random_bytes(6)));
        config()->set('database-backup.path', $this->backupRoot);
    }

    protected function tearDown(): void
    {
        $this->files->deleteDirectory($this->backupRoot);

        parent::tearDown();
    }

    public function test_it_keeps_current_day_backups_and_only_the_latest_previous_day_backup(): void
    {
        $old = $this->makeBackup('2026-09-24', 'database_2026-09-24_23-00-00.sql.gz', 100);
        $yesterdayEarly = $this->makeBackup('2026-09-25', 'database_2026-09-25_10-00-00.sql.gz', 200);
        $yesterdayLatest = $this->makeBackup('2026-09-25', 'database_2026-09-25_23-00-00.sql.gz', 300);
        $todayFirst = $this->makeBackup('2026-09-26', 'database_2026-09-26_00-00-00.sql.gz', 400);
        $todaySecond = $this->makeBackup('2026-09-26', 'database_2026-09-26_01-00-00.sql.gz', 500);

        $deleted = (new DatabaseBackupService($this->files))->pruneExpiredBackups(
            CarbonImmutable::parse('2026-09-26 01:00:00', 'Europe/Kiev'),
        );

        $this->assertSame(2, $deleted);
        $this->assertFileDoesNotExist($old);
        $this->assertFileDoesNotExist($yesterdayEarly);
        $this->assertFileExists($yesterdayLatest);
        $this->assertFileExists($todayFirst);
        $this->assertFileExists($todaySecond);
    }

    public function test_it_does_not_delete_unrelated_files_from_backup_directories(): void
    {
        $this->makeBackup('2026-09-24', 'database_2026-09-24_23-00-00.sql.gz', 100);
        $unrelated = $this->backupRoot.'/2026-09-24/readme.txt';
        $this->files->put($unrelated, 'keep');

        (new DatabaseBackupService($this->files))->pruneExpiredBackups(
            CarbonImmutable::parse('2026-09-26 01:00:00', 'Europe/Kiev'),
        );

        $this->assertFileExists($unrelated);
    }

    private function makeBackup(string $day, string $name, int $modifiedAt): string
    {
        $directory = $this->backupRoot.'/'.$day;
        $this->files->ensureDirectoryExists($directory);
        $path = $directory.'/'.$name;
        $this->files->put($path, 'backup');
        touch($path, $modifiedAt);

        return $path;
    }
}
