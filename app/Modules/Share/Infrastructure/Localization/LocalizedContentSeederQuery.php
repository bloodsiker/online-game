<?php

declare(strict_types=1);

namespace App\Modules\Share\Infrastructure\Localization;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class LocalizedContentSeederQuery extends LocalizedItemSeederQuery
{
    /** @var array<string, list<string>> */
    private const TABLE_FIELDS = [
        'structures' => ['name', 'description'],
        'share_actions' => ['name'],
        'share_structure_categories' => ['name'],
        'dungeons' => ['name', 'description'],
        'dungeon_stages' => ['name'],
        'boss_phases' => ['description'],
        'world_events' => ['title', 'description', 'influence_name'],
        'world_event_stages' => ['title'],
        'event_activities' => ['title', 'description'],
        'clan_skill_definitions' => ['name', 'description'],
        'news_posts' => ['title', 'description'],
        'influence_shop_sections' => ['name'],
        'trade_posts' => ['name'],
        'library_categories' => ['name', 'description'],
        'library_articles' => ['title', 'excerpt', 'content'],
        'injury_types' => ['name', 'description'],
        'map_influence_levels' => ['name'],
        'races' => ['name'],
        'maps' => ['name'],
        'locations' => ['name', 'description'],
        'monsters' => ['name', 'description'],
        'npcs' => ['name', 'description'],
        'skills' => ['name', 'description'],
        'magic_skills' => ['name', 'description'],
        'effects' => ['name', 'description'],
        'reputations' => ['name', 'description'],
        'influence_medals' => ['name', 'description'],
        'quests' => ['title', 'description'],
        'quest_dialogues' => ['description', 'reply_text'],
        'quest_stages' => ['title', 'description', 'waiting_text', 'ready_text'],
        'quest_objectives' => ['description'],
        'npc_dialogue_nodes' => ['title', 'description'],
        'npc_dialogue_options' => ['button_text'],
    ];

    public static function forTable(string $table): self
    {
        if (! isset(self::TABLE_FIELDS[$table])) {
            throw new InvalidArgumentException('Unsupported localized content table');
        }
        $connection = DB::connection();
        $query = (new self($connection, $connection->getQueryGrammar(), $connection->getPostProcessor()))->from($table);
        $query->localizedFields = self::TABLE_FIELDS[$table];

        return $query;
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and'): static
    {
        if (is_string($column) && in_array($column, $this->localizedFields, true)) {
            $column .= '->ru';
        }

        if (func_num_args() === 2) {
            parent::where($column, '=', $operator, $boolean);
        } else {
            parent::where($column, $operator, $value, $boolean);
        }

        return $this;
    }

    /** @param class-string<Model> $modelClass
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $values
     */
    public static function firstOrNewFor(string $modelClass, array $attributes, array $values = []): Model
    {
        $lookup = $attributes;
        $model = new $modelClass;
        foreach ($model->getTranslatableAttributes() as $field) {
            if (array_key_exists($field, $lookup)) {
                $lookup[$field.'->ru'] = $lookup[$field];
                unset($lookup[$field]);
            }
        }

        return $modelClass::query()->where($lookup)->first() ?? $model->forceFill($attributes + $values);
    }

    /** @param class-string<Model> $modelClass
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $values
     */
    public static function firstOrCreateFor(string $modelClass, array $attributes, array $values = []): Model
    {
        $model = self::firstOrNewFor($modelClass, $attributes, $values);
        if (! $model->exists) {
            $model->save();
        }

        return $model;
    }

    /** @param class-string<Model> $modelClass
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $values
     */
    public static function updateOrCreateFor(string $modelClass, array $attributes, array $values = []): Model
    {
        $model = self::firstOrNewFor($modelClass, $attributes);
        $model->forceFill($values)->save();

        return $model;
    }
}
