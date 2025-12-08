<?php

namespace App\Http\Controllers;

class ChatController extends Controller
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
