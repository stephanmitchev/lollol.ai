<?php

namespace App\Http\Controllers\API;

use App\Classes\Suggester;
use App\Http\Controllers\Controller;
use App\Jobs\GeolocateIp;
use App\Models\Event;
use App\Models\Suggestion;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class AIController extends Controller
{

    public function suggest(Request $request)
    {
        logger("Ask for suggest using $request->cssguid");
        Suggester::shouldSuggestBySessionID($request->cssguid);

        $s = Suggestion::where('cssguid', $request->cssguid)->whereNotNull('suggestion')->orderBy('created_at', 'desc')->first();
        $sPending = Suggestion::where('cssguid', $request->cssguid)->whereNull('suggestion')->orderBy('created_at', 'desc')->first();

        
        $isPending = ($s == null && $sPending != null) || ($s != null && $sPending != null && $s->created_at < $sPending->created_at);
        
        if ($s != null) {
            return ['text' => $s->suggestion, 'products' => json_decode($s->suggested_products), 'pending' => $isPending];
        }
        return ['text' => "", 'products' => null, 'pending' => $isPending];

    }

}




