<?php

declare(strict_types=1);

use App\Modules\Share\Infrastructure\Localization\LocalizedContentMigration;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(LocalizedContentMigration::class)->convert('boss_phases', ['description']);
    }

    public function down(): void
    {
        throw new RuntimeException('Localized boss phase content is intentionally preserved.');
    }
};
