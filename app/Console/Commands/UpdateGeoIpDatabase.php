<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Modules\Auth\Infrastructure\GeoIp\GeoIpDatabaseUpdater;
use Illuminate\Console\Command;

class UpdateGeoIpDatabase extends Command
{
    protected $signature = 'geoip:update {--force : Скачать заново, даже если база за этот месяц уже есть}';

    protected $description = 'Скачивает свежую базу DB-IP Lite для определения страны и города по IP';

    public function handle(GeoIpDatabaseUpdater $updater): int
    {
        $this->info($updater->updateIfOutdated((bool) $this->option('force')));

        return self::SUCCESS;
    }
}
