<?php

declare(strict_types=1);

use App\Modules\Share\Infrastructure\Localization\LocalizedContentMigration;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(LocalizedContentMigration::class)->convert('news_posts', ['title', 'description']);
    }

    public function down(): void
    {
        throw new RuntimeException('Localized news post content is intentionally preserved.');
    }
};
