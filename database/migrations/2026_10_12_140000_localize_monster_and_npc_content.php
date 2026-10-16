<?php

declare(strict_types=1);

use App\Modules\Share\Infrastructure\Localization\LocalizedContentMigration;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['monsters', 'npcs'] as $table) {
            (new LocalizedContentMigration)->convert($table);
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Automatic rollback would discard translations. Restore a verified backup or prepare an explicit content rollback.');
    }
};
