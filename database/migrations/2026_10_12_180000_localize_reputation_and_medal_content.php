<?php

declare(strict_types=1);

use App\Modules\Share\Infrastructure\Localization\LocalizedContentMigration;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $migration = new LocalizedContentMigration;
        $migration->convert(tableName: 'reputations');
        $migration->convert(tableName: 'reputation_tiers', fields: ['medal_name', 'feat_description', 'feat_medal_name']);
        $migration->convert(tableName: 'influence_medals');
    }

    public function down(): void
    {
        throw new RuntimeException('Automatic rollback would discard translations. Restore a verified backup or prepare an explicit content rollback.');
    }
};
