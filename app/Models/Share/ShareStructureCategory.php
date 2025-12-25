<?php

namespace App\Models\Share;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShareStructureCategory extends Model
{
    use HasFactory;

    protected $table = 'share_structure_categories';

    protected $fillable = ['name', 'is_active'];

    protected $attributes = [
        'is_active' => true,
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
