<?php

namespace App\Http\Controllers;

class ClanController extends Controller
{
    public function index()
    {
        return view('clan.index');
    }

    public function member()
    {
        return view('clan.member');
    }
}
