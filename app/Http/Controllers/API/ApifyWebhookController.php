<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Jobs\GeolocateIp;
use App\Models\Event;
use App\Models\UsptoStatusTelemetry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class ApifyWebhookController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function submitResults(Request $request)
    {
        $datasetId = $request->resource['defaultDatasetId'];
        $key = "apify_api_UAXmepfJkYrfzRGUyknzGgVYdfG55k0Zb31S";

        $response = Http::asJson()->get("https://api.apify.com/v2/datasets/{$datasetId}/items?token={$key}");
        logger($response);

        $t = new UsptoStatusTelemetry();
        $t->actor_id = $request->eventData['actorId'];
        $t->status = $response[0]['status'];
        $t->response_time = $response[0]['requestTime'];
        $t->save();
    }
}
