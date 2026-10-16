<?php

declare(strict_types=1);

use App\Modules\Share\Infrastructure\Localization\LocalizedContentMigration;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $localizer = app(LocalizedContentMigration::class);
        $localizer->convert('structures', ['name', 'description']);
        $localizer->convert('share_actions', ['name']);
        $localizer->convert('share_structure_categories', ['name']);
    }

    public function down(): void
    {
        throw new RuntimeException('Localized structure content is intentionally preserved.');
    }
};
