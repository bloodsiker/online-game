<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('library_categories')->updateOrInsert(
            ['slug' => 'karta'],
            [
                'parent_id' => null,
                'name' => 'Карта',
                'description' => 'Карты игрового мира и переходы между ними.',
                'content_type' => 'maps',
                'sort_order' => 40,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        DB::table('library_categories')
            ->where('slug', 'karta')
            ->where('content_type', 'maps')
            ->update([
                'content_type' => 'articles',
                'updated_at' => now(),
            ]);
    }
};
