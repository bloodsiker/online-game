<?php

declare(strict_types=1);

use App\Modules\Share\Infrastructure\Localization\LocalizedContentMigration;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $migration = app(LocalizedContentMigration::class);
        $migration->convert('library_categories', ['name', 'description']);
        $migration->convert('library_articles', ['title', 'excerpt', 'content']);
    }

    public function down(): void
    {
        throw new RuntimeException('Localized library content is intentionally preserved.');
    }
};
