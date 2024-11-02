<?php

namespace App\Jobs;

use App\Models\Ip;
use Http;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GeolocateIp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $ip)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        logger("Geolocating " . $this->ip);
        if ($this->ip == null) {
            return;
        }
        $ip = Ip::where("ip", $this->ip)->first();
        if ($ip != null) {
            return;
        }

        $apiKey = env('GEOCODING_API_KEY');
        $url = "https://api.ipgeolocation.io/ipgeo?apiKey="
            .$apiKey
            ."&ip=".$this->ip
            ;
        $response = json_decode(Http::asJson()->get($url), true);
        if (array_key_exists("message", $response)) {
            logger($response['message']);
        }
        else {
            logger ($response);
            $ip = new Ip();
            $ip->fill($response);
            $ip->save();
        }
        
    }

    function get_geolocation($apiKey, $ip, $lang = "en", $fields = "*", $excludes = "") {
        
        
    }
}
