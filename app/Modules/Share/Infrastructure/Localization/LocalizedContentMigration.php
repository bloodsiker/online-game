<?php

declare(strict_types=1);

namespace App\Modules\Share\Infrastructure\Localization;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

final class LocalizedContentMigration
{
    /** @param list<string> $fields */
    public function convert(string $tableName, array $fields = ['name', 'description']): void
    {
        // Resume safely if a MySQL DDL operation was interrupted during deployment.
        foreach ($fields as $field) {
            if (Schema::hasColumn($tableName, $field.'_legacy')) {
                if ($field === 'name') {
                    Schema::table($tableName, fn (Blueprint $table) => $table->string('name_legacy')->nullable()->change());
                }
                if (Schema::hasColumn($tableName, $field.'_localized')) {
                    Schema::table($tableName, fn (Blueprint $table) => $table->renameColumn($field.'_localized', $field));
                }

                continue;
            }
            if (! Schema::hasColumn($tableName, $field.'_localized')) {
                Schema::table($tableName, fn (Blueprint $table) => $table->json($field.'_localized')->nullable());
            }

            DB::table($tableName)->select(['id', $field])->chunkById(200, function ($rows) use ($field, $tableName): void {
                foreach ($rows as $row) {
                    $original = $row->{$field};
                    $decoded = is_string($original) ? json_decode($original, true) : null;
                    $data = $original === null ? null : (is_array($decoded) && array_key_exists('ru', $decoded) ? $decoded : ['ru' => $original]);
                    $encoded = $data === null ? null : json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                    DB::table($tableName)->where('id', $row->id)->update([$field.'_localized' => $encoded]);
                    $stored = DB::table($tableName)->where('id', $row->id)->value($field.'_localized');
                    if (($stored === null ? null : json_decode($stored, true, flags: JSON_THROW_ON_ERROR)) != $data) {
                        throw new RuntimeException('Content migration verification failed for '.$tableName.' #'.$row->id);
                    }
                }
            });

            // Keep the original data as a safety copy until the migration has been checked in production.
            Schema::table($tableName, fn (Blueprint $table) => $table->renameColumn($field, $field.'_legacy'));
            Schema::table($tableName, function (Blueprint $table) use ($field): void {
                if ($field === 'name') {
                    $table->string('name_legacy')->nullable()->change();
                }
            });
            Schema::table($tableName, fn (Blueprint $table) => $table->renameColumn($field.'_localized', $field));
        }
    }
}
