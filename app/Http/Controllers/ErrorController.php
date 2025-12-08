<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class ErrorController extends Controller
{
    public function index(Request $request)
    {
        $message = $request->get('message');

        return view('error', compact('message'));
    }
}
