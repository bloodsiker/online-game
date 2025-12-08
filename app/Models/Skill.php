<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    public const SKILL_HAND_ID = 1;

    protected $fillable = ['name', 'description', 'type'];
}
