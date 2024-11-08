<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function js(Request $request)
    {
        return view('integration');
        
    }
}
