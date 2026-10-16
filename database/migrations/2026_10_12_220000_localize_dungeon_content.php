<?php

declare(strict_types=1);

use App\Modules\Share\Infrastructure\Localization\LocalizedContentMigration;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $localizer = app(LocalizedContentMigration::class);
        $localizer->convert('dungeons', ['name', 'description']);
        $localizer->convert('dungeon_stages', ['name']);
    }

    public function down(): void
    {
        throw new RuntimeException('Localized dungeon content is intentionally preserved.');
    }
};
