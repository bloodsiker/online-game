<?php

namespace app\Http\Controllers;

class GameController extends Controller
{
    public function index()
    {
        return view('chat.index');
    }

    public function chatAction()
    {
        return view('chat.chat-action');
    }
}
