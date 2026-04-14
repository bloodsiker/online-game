<?php

declare(strict_types=1);

namespace App\Modules\Clan\Domain\Models;

use App\Modules\Clan\Domain\Enums\ClanPermission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClanRole extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'permissions', 'clan_id', 'is_leader', 'is_default'];

    protected $casts = [
        'is_leader'   => 'boolean',
        'is_default'  => 'boolean',
        'permissions' => 'integer',
    ];

    public function clan(): BelongsTo
    {
        return $this->belongsTo(Clan::class);
    }

    public function hasPermission(ClanPermission $permission): bool
    {
        return ($this->permissions & $permission->bit()) !== 0;
    }
}