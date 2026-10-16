<?php

declare(strict_types=1);

use App\Modules\Share\Infrastructure\Localization\LocalizedContentMigration;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $localizer = app(LocalizedContentMigration::class);
        $localizer->convert('maps', ['name']);
        $localizer->convert('locations', ['name', 'description']);
    }

    public function down(): void
    {
        throw new RuntimeException('Localized map and location data is intentionally preserved.');
    }
};
