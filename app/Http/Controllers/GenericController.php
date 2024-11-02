<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GenericController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function empty(Request $request)
    {
        if(request()->httpHost() == 'lollol.ai') {
            return response()->redirectToRoute('chat.index');
            
        }
       
        return;
        
    }
}
