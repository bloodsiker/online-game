<?php

declare(strict_types=1);

use App\Modules\Share\Infrastructure\Localization\LocalizedContentMigration;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(LocalizedContentMigration::class)->convert('trade_posts', ['name']);
    }

    public function down(): void
    {
        throw new RuntimeException('Localized trade post names are intentionally preserved.');
    }
};
