<?php

declare(strict_types=1);

namespace App\Modules\Share\Infrastructure\Localization;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/** Keeps legacy content importers compatible with JSON columns; never used by game requests. */
class LocalizedItemSeederQuery extends Builder
{
    /** @var list<string> */
    protected array $localizedFields = ['name', 'description'];

    public static function items(): self
    {
        $connection = DB::connection();

        return (new self($connection, $connection->getQueryGrammar(), $connection->getPostProcessor()))->from('share_items');
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and'): static
    {
        if ($column === 'name') {
            $column = 'name->ru';
        }
        // Preserve Query Builder's two-argument where(column, value) convention.
        if (func_num_args() === 2) {
            parent::where($column, '=', $operator, $boolean);
        } else {
            parent::where($column, $operator, $value, $boolean);
        }

        return $this;
    }

    public function whereIn($column, $values, $boolean = 'and', $not = false): static
    {
        parent::whereIn($column === 'name' ? 'name->ru' : $column, $values, $boolean, $not);

        return $this;
    }

    /** @param array<string, mixed> $values */
    public function insertGetId(array $values, $sequence = null): int
    {
        return (int) parent::insertGetId($this->encode($values), $sequence);
    }

    /** @param array<string, mixed>|list<array<string, mixed>> $values */
    public function insert(array $values): bool
    {
        return parent::insert(array_is_list($values) ? array_map(fn ($row) => $this->encode($row), $values) : $this->encode($values));
    }

    /** @param array<string, mixed> $values */
    public function update(array $values): int
    {
        foreach ($this->localizedFields as $field) {
            if (array_key_exists($field, $values) && ($values[$field] === null || (is_string($values[$field]) && ! is_array(json_decode($values[$field], true))))) {
                $quoted = $this->connection->getPdo()->quote($values[$field] ?? '');
                $values[$field] = DB::raw("JSON_SET(COALESCE({$field}, '{}'), '$.ru', {$quoted})");
            }
        }

        return parent::update($values);
    }

    /** @param array<string, mixed> $attributes
     * @param  array<string, mixed>  $values
     */
    public static function firstOrCreate(array $attributes, array $values = []): ShareItem
    {
        $lookup = $attributes;
        if (isset($lookup['name'])) {
            $lookup['name->ru'] = $lookup['name'];
            unset($lookup['name']);
        }

        return ShareItem::query()->where($lookup)->first()
            ?? ShareItem::unguarded(fn () => ShareItem::query()->create($attributes + $values));
    }

    /** @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    private function encode(array $values): array
    {
        foreach ($this->localizedFields as $field) {
            if (isset($values[$field]) && is_array($values[$field])) {
                $values[$field] = json_encode($values[$field], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            } elseif (isset($values[$field]) && ! is_array(json_decode((string) $values[$field], true))) {
                $values[$field] = json_encode(['ru' => $values[$field]], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            }
        }

        return $values;
    }
}
