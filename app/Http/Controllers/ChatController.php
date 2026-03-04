<?php

namespace App\Http\Controllers;

class ChatController extends Controller
{
    public function index()
    {
        return view('chat.index');
    }

    public function chat()
    {
        return view('chat.chat');
    }

    public function chatLog()
    {
        return view('chat.log');
    }

    public function chatAction()
    {
        return view('chat.chat-action');
    }
}
