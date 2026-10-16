<?php

declare(strict_types=1);

use App\Modules\Share\Infrastructure\Localization\LocalizedContentMigration;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $localizer = app(LocalizedContentMigration::class);
        $localizer->convert('world_events', ['title', 'description', 'influence_name']);
        $localizer->convert('world_event_stages', ['title']);
    }

    public function down(): void
    {
        throw new RuntimeException('Localized world event content is intentionally preserved.');
    }
};
