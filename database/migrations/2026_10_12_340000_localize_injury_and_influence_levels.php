<?php

declare(strict_types=1);

use App\Modules\Share\Infrastructure\Localization\LocalizedContentMigration;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $migration = app(LocalizedContentMigration::class);
        $migration->convert('injury_types', ['name', 'description']);
        $migration->convert('map_influence_levels', ['name']);
    }

    public function down(): void
    {
        throw new RuntimeException('Localized injury and influence level content is intentionally preserved.');
    }
};
