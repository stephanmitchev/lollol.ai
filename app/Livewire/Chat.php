<?php

namespace App\Livewire;

use App\Classes\Facts;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;

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
            Include available URLs in your responses if they are relevant.
            Be kind, be funny, and have fun.
            
           

                 
                    '
        ]
    ];

    public $tools = [
        [
            "type" => "function",
            "function" => [
                "name" => "get_current_weather",
                "description" => "To get the current weather and the 5-day forecast ",
                "parameters" => null
            ]
            
        ],
        [
            
            "type" => "function",
            "function" => [
                "name" => "get_current_news_top_headlines",
                "description" => "To get the recent top news headlines",
                "parameters" => null
            ]
        ],
        [
            "type" => "function",
            "function" => [
                "name" => "get_current_news_by_topic",
                "description" => "Use only to get the recent news for a SPECIFIC TOPIC - do not use without topic",
                "parameters" => [
                    "type" => "object",     
                    "required" => [
                        "topic"
                    ],
                    "properties" => [
                        "topic" => [
                            "type" => "string",
                            "description" => "The topic for the news the user is interested in"
                        ]
                    ]
                ],
            ],
                
        ],
        
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
        /*try {
            Http::get(config('services.ollama.chat_url'));
        } catch (Exception $e) {
            $this->enabled = false;
        }*/


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

        //$tools = $this->checkTools($history);

        //logger(json_encode($tools));
        //if ($tools != null && is_array($tools->tools) && count($tools->tools) > 0) {
        //    $history = $this->useTools($tools, $history);
        //}
        //logger(json_encode($history, JSON_PRETTY_PRINT));

        if (false && $this->askGuard($history) === false) {

            logger("Unsafe conversation!");
            $response = 'This topic is considered unsafe and I cannot further engage. Ask a differen question or <button wire:click="startOver" class="mr-10 text-red-500 underline">Start over</button>';
        } else {
            $response = self::askModel($history);

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
            logger('Running tool: ' . $tool->name);

            $name = $tool->name;
            $parameters = $tool->parameters ?? null;
            $history[] = [
                "role" => 'tool',
                "content" => $parameters ? $this->$name($parameters) : $this->$name()
            ];

            $tools = $this->checkTools($history);
        }


        return $history;
    }


    public function get_current_weather()
    {
        logger("Weather:");
        $url = "https://api.open-meteo.com/v1/forecast?latitude=".$this->facts['user']['latitude']."&longitude=".$this->facts['user']['longitude']."&current=temperature_2m,rain,wind_speed_10m,wind_direction_10m&daily=temperature_2m_max,temperature_2m_min,precipitation_sum,wind_speed_10m_max,wind_direction_10m_dominant&temperature_unit=fahrenheit&wind_speed_unit=mph&precipitation_unit=inch&timezone=America%2FNew_York&models=best_match";
        logger($url);
        $response = json_decode(Http::asJson()->get($url)->getBody()->getContents());
        $weather = '';
        for($i = 0; $i < 5; $i ++) {
            $weather .= "
            Day: " . $response->daily->time[$i] ."
            Max Temperature: " . $response->daily->temperature_2m_max[$i] ." ". $response->daily_units->temperature_2m_max ." 
            Min Temperature: " . $response->daily->temperature_2m_min[$i] ." ". $response->daily_units->temperature_2m_min ." 
            Total Precipitation: " . $response->daily->precipitation_sum[$i] ." ". $response->daily_units->precipitation_sum ." 
            Wind Speed: " . $response->daily->wind_speed_10m_max[$i] ." ". $response->daily_units->wind_speed_10m_max ." 
            Wind Direction: " . $response->daily->wind_direction_10m_dominant[$i] ." ". $response->daily_units->wind_direction_10m_dominant ." 
            
            ";
        }
        
        logger($weather);

        return $weather;
    }

    public function get_current_news_top_headlines()
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

    public function get_current_news_by_topic($topic)
    {
        logger("News: $topic");
        $url = "https://newsapi.org/v2/top-headlines?q=".urlencode($topic)."&pageSize=10&apiKey=".env('NEWS_API_KEY');
        logger($url);

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
                [
                    {
                        "name": "get_current_weather",
                        "description": "To get the current weather and the 5-day forecast ",
                        "parameters": null
                    },
                    {
                        "name": "get_current_news_top_headlines",
                        "description": "To get the recent top news headlines",
                        "parameters": null
                    },
                    {
                        "name": "get_current_news_by_topic",
                        "description": "Use only to get the recent news for a SPECIFIC TOPIC - do not use without topic",
                        "parameters": {
                           "required": [
                                "topic"
                            ], 
                            "properties": {
                                "topic": {
                                    "type": "string",
                                    "description": "The topic for the news the user is interested in"
                                }
                            }
                        },
                            
                    },
                    
                ]
                 
                Use tools only when necessary to answer the last question. List the tools in the order of dependency.

                Your response will be a JSON object only - DO NOT USE PLAIN TEXT. 
                Do not use variables. The "tools" property is required - use empty array if no tools necessary. 
                Answer only in JSON using the following format:
                    
                { 
                    "tools": [
                        {
                            "name": string
                            "parameters": string
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
            "model" => 'gpt-4o-mini',
            "messages" => $history,
            "tools" => $this->tools,
        ];
        $response = OpenAI::chat()->create($data);

        $r = '';
        if (count($response->choices[0]->message->toolCalls) > 0) {
            $history[] = [
                "role" => "assistant",
                "tool_calls" => $response->choices[0]->message->toolCalls,
            ];
            foreach($response->choices[0]->message->toolCalls as $toolCall) {
                if ($toolCall->type == 'function') {
                    $fn = $toolCall->function->name;
                    $args = $toolCall->function->arguments;
                    $result = $this->$fn($args);
                    
                    $history[] = [
                        "role" => "tool",
                        "tool_call_id" => $toolCall->id,
                        "content" => $result
                    ];
                }
                //dd($toolCall);
            }

            $data = [
                "model" => 'gpt-4o-mini',
                "messages" => $history,
                "tools" => $this->tools,
            ];
            $responses = OpenAI::chat()->createStreamed($data);
            
            $this->stream(to: 'response', content: Str::markdown($r), replace: true);
            foreach ($responses as $response) {
                $r .= $response->choices[0]->delta->content;
                $this->stream(to: 'response', content: Str::markdown($r), replace: true);
            }


        }
        else {
            $r = $response->choices[0]->message->content;
        }
        

        session()->put("history", $history);
        return $r;
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
        return $response->message->content == 'safe' || str_contains($response->message->content, 'S7') || str_contains($response->message->content, 'S1')|| str_contains($response->message->content, 'S8') || str_contains($response->message->content, 'S5');
    }


    public function askModelStreamed($history)
    {


        $data = [
            "model" => 'gpt-4o-mini',
            "messages" => $history,
            "tools" => $this->tools
        ];
        

        $r = "";

        $responses = OpenAI::chat()->createStreamed($data);
        $r = '';
        $this->stream(to: 'response', content: Str::markdown($r), replace: true);
        $shouldStream = true;

        foreach ($responses as $response) {
            //dd($response);
            $r .= $response->choices[0]->delta->content;

            $this->stream(to: 'response', content: Str::markdown($r), replace: true);
        }

        dd($responses);
        


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
            if (isset($item['content'])) {
                if ($item['role'] == 'user') {
                    $this->content = "<div class='py-1'><div style='text-align:right'>" . $item['content'] . "</div></div>\n$this->content";
                } else if ($item['role'] == 'assistant') {
                    $this->content = "<div class='w-full md:w-4/5 lg:w-2/3 px-5 pb-4 pt-1 mb-5  border rounded-xl p-3 shadow-lg'>" . Str::markdown($item['content']) . "</div>\n$this->content";
                }
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
