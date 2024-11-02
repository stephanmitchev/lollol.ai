<?php

namespace App\Http\Controllers\API;

use App\Classes\Suggester;
use App\Http\Controllers\Controller;
use App\Jobs\GeolocateIp;
use App\Models\Event;
use Illuminate\Http\Request;

class AnalyticsPingController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function ping(Request $request)
    {
        $ip = $request->getClientIp();
        $key = $request->key;
        $value = $request->value;
        $referrer = $request->referrer;
        $userAgent = $request->userAgent;
        $cssguid = $request->cssguid;
        $cssgsuid = $request->cssgsuid;
        if ($ip != null && $key != null && $value != null) {
            $event = new Event();
            $event->ip = $ip;
            $event->key = $key;
            $event->value = $value;
            $event->referrer = $referrer;
            $event->user_agent = $userAgent;
            $event->cssguid = $cssguid;
            $event->cssgsuid = $cssgsuid;
            $event->save();

           
        }
        GeolocateIp::dispatch($request->getClientIp());



        return '{}';
    }

    public function tag(Request $request)
    {
        $token = $this->getToken(32);
        return '

function getCookie(name) {
    const parts = `; ${document.cookie}`.split(`; ${name}=`);
    return (parts.length === 2) ? parts.pop().split(";").shift() : null;
}

if (getCookie("_cssguid") == null) document.cookie="_cssguid='.$token.'; max-age=31536000; samesite=lax; path=/; Secure";
if (getCookie("_cssgsuid") == null) document.cookie="_cssgsuid='.$token.'; samesite=lax; path=/; Secure";

fetch("https://analytics.capitolssg.com/api/ping", {
    method: "post",
    body: JSON.stringify({"key": "page_view", "value": window.location.href, "referrer": document.referrer, "userAgent": window.navigator.userAgent, "cssguid": getCookie("_cssguid") , "cssgsuid": getCookie("_cssgsuid") }),
    headers: {
        "Accept": "application/json",
        "Content-Type": "application/json"
    }
}).catch((error) => {
    console.log(error)
});


';
    }


    static function crypto_rand_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) return $min; // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd > $range);
        return $min + $rnd;
    }

    public static function getToken($length)
    {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        $max = strlen($codeAlphabet); // edited

        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[self::crypto_rand_secure(0, $max-1)];
        }

        return $token;
    }

}
