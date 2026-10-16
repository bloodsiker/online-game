<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Resume safely if a MySQL DDL operation was interrupted during deployment.
        foreach (['name', 'description'] as $field) {
            if (Schema::hasColumn('share_items', $field.'_legacy')) {
                if ($field === 'name') {
                    Schema::table('share_items', fn (Blueprint $table) => $table->string('name_legacy')->nullable()->change());
                }
                if (Schema::hasColumn('share_items', $field.'_localized')) {
                    Schema::table('share_items', fn (Blueprint $table) => $table->renameColumn($field.'_localized', $field));
                }

                continue;
            }
            if (! Schema::hasColumn('share_items', $field.'_localized')) {
                Schema::table('share_items', fn (Blueprint $table) => $table->json($field.'_localized')->nullable());
            }

            DB::table('share_items')->select(['id', $field])->chunkById(200, function ($rows) use ($field): void {
                foreach ($rows as $row) {
                    $original = $row->{$field};
                    $encoded = $original === null ? null : json_encode(['ru' => $original], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                    DB::table('share_items')->where('id', $row->id)->update([$field.'_localized' => $encoded]);
                    $stored = DB::table('share_items')->where('id', $row->id)->value($field.'_localized');
                    if (($stored === null ? null : json_decode($stored, true, flags: JSON_THROW_ON_ERROR)['ru']) !== $original) {
                        throw new RuntimeException('Content migration verification failed for share_items #'.$row->id);
                    }
                }
            });

            // Keep the original data as a safety copy until the migration has been checked in production.
            Schema::table('share_items', fn (Blueprint $table) => $table->renameColumn($field, $field.'_legacy'));
            Schema::table('share_items', function (Blueprint $table) use ($field): void {
                if ($field === 'name') {
                    $table->string('name_legacy')->nullable()->change();
                }
            });
            Schema::table('share_items', fn (Blueprint $table) => $table->renameColumn($field.'_localized', $field));
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Automatic rollback would discard translations. Restore a verified backup or prepare an explicit content rollback.');
    }
};
