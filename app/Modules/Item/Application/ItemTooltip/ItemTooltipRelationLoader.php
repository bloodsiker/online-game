<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\ItemTooltip;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection as SupportCollection;
use Traversable;

final class ItemTooltipRelationLoader
{
    /**
     * Loads tooltip relations for the whole model set in fixed-size batches.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  iterable<int, TModel>  $models
     * @param  list<string>  $relations
     * @return EloquentCollection<int, TModel>
     */
    public static function load(iterable $models, array $relations): EloquentCollection
    {
        $collection = match (true) {
            $models instanceof EloquentCollection => $models,
            $models instanceof SupportCollection => new EloquentCollection($models->all()),
            $models instanceof Traversable => new EloquentCollection(iterator_to_array($models, false)),
            default => new EloquentCollection($models),
        };

        if ($collection->isNotEmpty()) {
            $collection->loadMissing($relations);
        }

        return $collection;
    }
}
