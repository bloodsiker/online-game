<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryArticleLink extends Model
{
    protected $fillable = [
        'entity_type',
        'entity_id',
        'custom_label',
        'sort_order',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(LibraryArticle::class, 'article_id');
    }
}
