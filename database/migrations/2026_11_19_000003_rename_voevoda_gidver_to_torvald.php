<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Воевода Гидвер → Воевода Торвальд: имя НПС и все упоминания в текстах (падежи меняются вместе с основой).
 */
return new class extends Migration
{
    /** @var array<string, list<string>> */
    private const COLUMNS = [
        'npcs' => ['name', 'name_legacy'],
        'quests' => ['description', 'description_legacy'],
        'quest_dialogues' => ['description', 'description_legacy'],
        'quest_objectives' => ['description', 'description_legacy'],
        'reputations' => ['description', 'description_legacy'],
        'share_items' => ['description', 'description_legacy'],
        'library_articles' => ['excerpt', 'excerpt_legacy', 'content', 'content_legacy'],
    ];

    public function up(): void
    {
        $this->rename(['Гидвер' => 'Торвальд', 'Гідвер' => 'Торвальд']);
    }

    public function down(): void
    {
        // Украинскую форму не восстановить однозначно — откат только русской.
        $this->rename(['Торвальд' => 'Гидвер']);
    }

    /** @param array<string, string> $map */
    private function rename(array $map): void
    {
        foreach (self::COLUMNS as $table => $columns) {
            foreach ($columns as $column) {
                foreach ($map as $from => $to) {
                    DB::table($table)
                        ->where($column, 'like', '%'.$from.'%')
                        ->update([$column => DB::raw('REPLACE(`'.$column.'`, '.DB::getPdo()->quote($from).', '.DB::getPdo()->quote($to).')')]);
                }
            }
        }
    }
};
