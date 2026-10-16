<?php

declare(strict_types=1);

use App\Modules\Share\Infrastructure\Localization\LocalizedContentMigration;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(LocalizedContentMigration::class)->convert('event_activities', ['title', 'description']);
    }

    public function down(): void
    {
        throw new RuntimeException('Localized event activity content is intentionally preserved.');
    }
};
