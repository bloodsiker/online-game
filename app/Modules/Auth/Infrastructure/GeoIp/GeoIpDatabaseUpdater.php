<?php

declare(strict_types=1);

namespace App\Modules\Auth\Infrastructure\GeoIp;

use Illuminate\Support\Facades\Http;
use MaxMind\Db\Reader;
use RuntimeException;
use Throwable;

/** Скачивает свежую базу DB-IP Lite (выходит раз в месяц), проверяет её и подменяет файл атомарно. */
final class GeoIpDatabaseUpdater
{
    public function __construct(private readonly GeoIpLocator $locator) {}

    /** @return string Что было сделано — для вывода в консоль и планировщик. */
    public function updateIfOutdated(bool $force = false): string
    {
        $path = $this->locator->path();

        if (! $force && is_file($path) && date('Y-m', (int) filemtime($path)) === date('Y-m')) {
            return 'GeoIP: база за этот месяц уже загружена.';
        }

        // В первые дни месяца новый файл может ещё не выйти — тогда берём прошлый месяц.
        foreach ([date('Y-m'), date('Y-m', strtotime('first day of last month'))] as $month) {
            $url = sprintf((string) config('services.geoip.download_url'), $month);
            try {
                $this->download($url, $path);

                return sprintf('GeoIP: база %s загружена.', $month);
            } catch (Throwable $exception) {
                $last = $exception;
            }
        }

        throw new RuntimeException('GeoIP: не удалось загрузить базу: '.($last?->getMessage() ?? ''));
    }

    private function download(string $url, string $path): void
    {
        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException('Нет доступа к '.$directory);
        }

        $archive = $path.'.download.gz';
        $temporary = $path.'.download';

        try {
            $response = Http::timeout(600)->withOptions(['sink' => $archive])->get($url);
            if (! $response->successful()) {
                throw new RuntimeException('HTTP '.$response->status());
            }

            $source = gzopen($archive, 'rb');
            $target = fopen($temporary, 'wb');
            if ($source === false || $target === false) {
                throw new RuntimeException('Не удалось распаковать архив.');
            }
            while (! gzeof($source)) {
                fwrite($target, (string) gzread($source, 1 << 20));
            }
            gzclose($source);
            fclose($target);

            $reader = new Reader($temporary);
            $type = $reader->metadata()->databaseType;
            $reader->close();
            if (! str_contains($type, 'City')) {
                throw new RuntimeException('Неожиданный тип базы: '.$type);
            }

            rename($temporary, $path);
        } finally {
            @unlink($archive);
            @unlink($temporary);
        }
    }
}
