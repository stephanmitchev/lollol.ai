<?php

namespace App\Livewire;

use App\Classes\Facts;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Str;

class Chat extends Component
{
    public $prompt;

    private $systemPrompt = [
        [
            "role" => "system",
            "content" => '
            You are a helpful and lively assistant and an online friend called LOLLOL. Do not mention that you are an LLM. 
            You will respond with non-offensive remarks only and will kindly refuse to reply to offensive language.
            Do not give legal advice. Do not give medical advice. Do not engage in offensive conversations.
            Show reference URLs if available

       '
        ]
    ];

    public $content;
    public $generating = false;
    public $enabled = true;
    public $facts;


    public function mount()
    {
        date_default_timezone_set("America/New_York");

        // Init or refresh facts
        $this->facts = session()->get("facts");
        if ($this->facts == null) {
            $this->facts = [];
        }

        $loc = json_decode($this->find_user_location());
        $this->facts["user"] = [
            "longitude" => $loc->longitude,
            "latitude" => $loc->latitude,
            "country_code" => $loc->country_code2,
            "country_name" => $loc->country_name,
            "state_prov" => $loc->state_prov,
            "district" => $loc->district,
            "city" => $loc->city,
            "zipcode" => $loc->zipcode,
        ];
        $this->facts["current_date_time"] = [
            "time" => date('Y-m-d H:i:s'),
            "day_of_week" => date('l'),
            "month" => date('F'),
            "year" => date('Y'),
            "season" => date('n') <= 3 ? 'winter' : (date('n') <= 6 ? 'spring' : (date('n') <= 9 ? 'summer' : 'autumn')),
        ];

        session()->put("facts", $this->facts);



        $history = session()->get("history");
        if ($history == null) {
            $this->systemPrompt[0]['content'] .= '
            Here are some facts for the user to be used when answering questions only - use those facts only when necessary. 
            Do not use this information to suggest conversation topics: ' . json_encode($this->facts);
            session()->put("history", $this->systemPrompt);
        }

        // Heartbeat check
        try {
            Http::get(config('services.ollama.chat_url'));
        } catch (Exception $e) {
            $this->enabled = false;
        }


    }

    public function sendPrompt()
    {
        if (empty(trim($this->prompt))) {
            return;
        }

        $history = session()->get("history");

        $history[] = [
            "role" => "user",
            "content" => $this->prompt
        ];

        session()->put("history", $history);

        $this->prompt = "";
        $this->generating = true;

        $this->dispatch("generate-reply")->self();
    }






    #[On('generate-reply')]
    public function generateReply()
    {
        $history = session()->get("history");

        $tools = $this->checkTools($history);

        logger(json_encode($tools));
        if ($tools != null && is_array($tools->tools) && count($tools->tools) > 0) {
            $history = $this->useTools($tools, $history);
        }
        //logger(json_encode($history, JSON_PRETTY_PRINT));

        if ($this->askGuard($history) === false) {

            logger("Unsafe conversation!");
            $response = 'This topic is considered unsafe and I cannot further engage. Ask a differen question or <button wire:click="startOver" class="mr-10 text-red-500 underline">Start over</button>';
        } else {
            $response = self::askModelStreamed($history);

            if ($response === false) {
                logger("Exception!");
                $this->enabled = false;
                $this->generating = false;
                return;
            }
        }





        logger(json_encode($response));
        $history[] = [
            "role" => "assistant",
            "content" => $response
        ];
        session()->put("history", $history);

        $this->generating = false;
    }

    private function useTools($tools, $history)
    {
        logger("userTools for " . count($tools->tools));
        if($tools == null) {
            return $history;

        } 

        for ($i = 0; $i < min(count($tools->tools), 3); $i++) {
            $tool = $tools->tools[$i];
            logger('Ruuning tool: ' . $tool->tool_name);

            $toolName = $tool->tool_name;
            $history[] = [
                "role" => 'tool',
                "content" => $this->$toolName()
            ];

            $tools = $this->checkTools($history);
        }


        return $history;
    }


    public function get_current_weather()
    {
        logger("Weather:");
        $url = "https://api.open-meteo.com/v1/forecast?latitude=".$this->facts['user']['latitude']."&longitude=".$this->facts['user']['longitude']."&temperature_2m,relative_humidity_2m,is_day,precipitation,wind_speed_10m,wind_direction_10m&daily=temperature_2m_max,temperature_2m_min,daylight_duration,rain_sum,wind_speed_10m_max&temperature_unit=fahrenheit&timezone=America%2FNew_York&forecast_days=3&models=best_match";
        logger($url);
        $response = Http::asJson()->get($url)->getBody()->getContents();
        logger($response);

        return $response;
    }

    public function get_current_news()
    {
        logger("News:");
        $url = "https://newsapi.org/v2/top-headlines?pageSize=10&country=".$this->facts["user"]["country_code"]."&apiKey=".env('NEWS_API_KEY');

        $response = json_decode(Http::asJson()->get($url)->getBody()->getContents());
        //dd($response);
        $news = '';
        foreach($response->articles as $article) {
            $news .= "Article: $article->title
                URL: $article->url
                Excerpt: $article->content

                ";
        }
        logger($news);

        return $news;
    }

    
    public function find_user_location()
    {
        $apiKey = env('GEOCODING_API_KEY');
        $ip = request()->ip() == '127.0.0.1' ? '73.28.84.93' : request()->ip();
        $url = "https://api.ipgeolocation.io/ipgeo?apiKey="
            . $apiKey
            . "&ip=" . $ip;
        $response = Http::asJson()->get($url)->getBody()->getContents();
        //logger($response);

        return $response;
    }

    /* 
     *   @return mixed
     */
    public function checkTools($history)
    {

        $history = [
            [
                "role" => "system",
                "content" => 'You are a helpful assistant who has access to the following tools: 
                    - get_current_weather - to get the current weather forecast
                    - get_current_news - to get the recent news
                        
                Use tools when necessary to answer the last question. List the tools in the order of dependency.

                Your response will be a JSON object only - DO NOT USE PLAIN TEXT. 
                Do not use variables. The "tools" property is required - use empty array if no tools necessary. 
                Answer only in JSON using the following format:
                    
                { 
                    "tools": [
                        {
                            "tool_name": string
                        }
                    ]
                }

               
'
            ]
        ];
        $h = session()->get("history");
        
        $history = array_merge($history, [$h[count($h)-1]]);



        //logger(json_encode($history));
        $response = $this->askModel($history);
        logger($response);
        $tools = json_decode($response);

        return $tools;
    }


    public function askModel($history)
    {
        $data = [
            "model" => config('services.ollama.tool_model'),
            "messages" => $history,
            "stream" => false,
            "keep_alive" => -1
        ];
        //dd(json_encode($data));

        $r = "";

        $client = \ArdaGnsrn\Ollama\Ollama::client(env('OLLAMA_CHAT_URL'));
        $response = $client->chat()->create($data);


        return $response->message->content;
    }


    public function askGuard($history)
    {

        $data = [
            "model" => "llama-guard3:1b",
            "messages" => [$history[count($history) - 1]],
            "keep_alive" => -1
        ];
        //dd(json_encode($data));

        $r = "";

        $client = \ArdaGnsrn\Ollama\Ollama::client(config('services.ollama.chat_url'));
        $response = $client->chat()->create($data);

        logger($response->message->content);
        return $response->message->content == 'safe' || str_contains($response->message->content, 'S7');
    }


    public function askModelStreamed($history)
    {


        $data = [
            "model" => config('services.ollama.tool_model'),
            "messages" => $history,
            "stream" => true,
            "keep_alive" => -1
        ];
        //dd(json_encode($data));

        $r = "";

        $client = \ArdaGnsrn\Ollama\Ollama::client(config('services.ollama.chat_url'));
        $responses = $client->chat()->createStreamed($data);
        $r = '';
        $this->stream(to: 'response', content: Str::markdown($r), replace: true);

        $shouldStream = true;

        foreach ($responses as $response) {
            $r .= $response->message->content;

            $this->stream(to: 'response', content: Str::markdown($r), replace: true);
        }

        return $r;
    }



    public function getResponse()
    {

        return $this->response;
    }



    private function getContent()
    {
        $history = session()->get("history");
        $this->content = "";

        foreach ($history as $item) {
            if ($item['role'] == 'user') {
                $this->content = "<div class='px-3 py-1'><div style='text-align:right'>" . $item['content'] . "</div></div>\n$this->content";
            } else if ($item['role'] == 'assistant') {
                $this->content = "<div class='w-full md:w-4/5 lg:w-2/3 px-5 pb-4 pt-1 ml-3 mb-5  border rounded-xl p-3 shadow-lg'>" . Str::markdown($item['content']) . "</div>\n$this->content";
            }
        }
    }

    public function startOver()
    {
        session()->forget("history");
        $this->mount();
        return;
    }


    public function render()
    {
        $this->getContent();
        return view('livewire.chat');
    }
}
