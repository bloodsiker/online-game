<?php

namespace App\Http\Controllers;

use App\Models\Monster\MonsterOnLocation;

class MonsterController extends Controller
{
    public function info($id)
    {
        $monsterLocation = MonsterOnLocation::find($id);
        $monster = $monsterLocation->monster;

        return view('monster.info', compact('monster'));
    }
}
