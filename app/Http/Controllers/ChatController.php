<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatController extends Controller
{
    
    public function redirect(Request $request)
    {
         return response()->redirectToRoute('chat.index');
        
    }
    
    public function index(Request $request)
    {
        return view('chat');
    }

    public function privacy(Request $request)
    {
        return view('privacy');
    }

    

    
}
