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

    private $systemPrompt = [[
        "role" => "system",
        "content" => "You are a helpful and lively assistant and an online friend. 
        You will respond with non-offensive remarks only and will kindly refuse to reply to offensive language.
        Do not give legal advice. Do not give medical advice. Do not engage in offensive conversations.
        do not engage in the following classes of conversations:
        S1 - Violent Crimes

Responses that enable, encourage, or endorse the commission of violent crimes, including: (1) unlawful violence toward people (ex: terrorism, genocide, murder, hate-motivated violent crimes, child abuse, assault, battery, kidnapping) and (2) unlawful violence toward animals (ex: animal abuse)

S2 - Non-Violent Crimes

Responses that enable, encourage, or endorse the commission of non-violent crimes, including: (1) personal crimes (ex: labor trafficking, threats, intimidation, hate-motivated non-violent crimes), (2) financial crimes (ex: fraud, scams, money laundering), (3) property crimes (ex: theft, arson, vandalism), (4) drug crimes (ex: creating or using regulated substances), (5) weapons crimes (ex: producing unlicensed firearms), and (6) cyber crimes (ex: hacking)

S3 - Sex-Related Crimes

Responses that enable, encourage, or endorse the commission of sex-related crimes, including: (1) sex trafficking, (2) sexual assault (ex: rape), (3) sexual harassment (physical, verbal, or visual), and (4) prostitution

S4 - Child Sexual Exploitation

Responses that contain, describe, enable, encourage, or endorse the sexual abuse of children

S5 - Defamation

Responses that are both verifiably false and likely to injure a living person’s reputation

S6 - Specialized Advice

Responses that contain specialized financial, medical, or legal advice, or that indicate dangerous activities or objects are safe

S7 - Privacy

Responses that contain sensitive, nonpublic personal information that could undermine someone’s physical, digital, or financial security

S8 - Intellectual Property

Responses that may violate the intellectual property rights of any third party

S9 - Indiscriminate Weapons

Responses that enable, encourage, or endorse the creation of indiscriminate weapons, including: (1) chemical weapons (ex: nerve gas), (2) biological weapons (ex: anthrax), (3) radiological weapons (ex: cobalt bombs), (4) nuclear weapons (ex: fission bombs), and (5) high-yield explosive weapons (ex: cluster munitions)

S10 - Hate

Responses that demean or dehumanize people on the basis of their sensitive, personal characteristics (i.e., race, color, ethnicity, national origin, disability, religious affiliation, caste, sexual orientation, sex, gender identity, and/or serious disease)

S11 - Suicide & Self-Harm

Responses that enable, encourage, or endorse acts of intentional self-harm, including: (1) suicide, (2) self-injury (ex: cutting), and (3) disordered eating

S12 - Sexual Content

Responses that contain erotica

S13 - Elections

Responses that contain factually incorrect information about electoral systems and processes, including in the time, place, or manner of voting in civic elections"
    ]];

    public $content;
    public $generating = false;
    public $enabled = true;
    public $facts;


    public function mount()
    {
        $loc = json_decode($this->find_user_location());
        $facts = [
            
                "longitude" => $loc->longitude,
                "latitude" => $loc->latitude,
                "country_name" => $loc->country_name,
                "state_prov" => $loc->state_prov,
                "district" => $loc->district,
                "city" => $loc->city,
                "zipcode" => $loc->zipcode,

            
        ];
        
        $history = session()->get("history");
        if ($history == null) {
            //  $this->systemPrompt[0]['content'] .= 'Here are some user facts to be used when answering questions. Do not use this information to suggest conversation topics: '.json_encode($facts);
            session()->put("history", $this->systemPrompt);
        }

        $facts = session()->get("facts");
        if ($facts == null) {
            session()->put("facts", []);
        }



        try {
            Http::get(config('services.ollama.chat_url'));
        } catch (Exception $e) {
            $this->enabled = false;
        }

        
        session()->put("facts", $facts);
        

        
    }

    public function sendPrompt()
    {
        if (empty(trim($this->prompt))) {
            return;
        }

        $history = session()->get("history");

        $this->content = "<div>$this->prompt</div>\n$this->content";

        $history[] = [
            "role" => "user",
            "content" => $this->prompt
        ];

        session()->put("history", $history);

        $this->prompt =  "";
        $this->generating =  true;

        $this->dispatch("generate-reply")->self();
    }






    #[On('generate-reply')]
    public function generateReply()
    {
        $history = session()->get("history");

        //$tools = $this->checkTools($history);

        //logger(json_encode($tools));
        //if ($tools != null && isset($tools->use_tools) && $tools->use_tools === true && is_array($tools->tools) && count($tools->tools) > 0) {
        //    $history = $this->useTools($tools, $history);
        //}
        logger(json_encode($history, JSON_PRETTY_PRINT));
        
        if ($this->askGuard($history) === false) {

            logger("Unsafe conversation!");
            $response = 'This topic is considered unsafe and I cannot further engage. Ask a differen question or <button wire:click="startOver" class="mr-10 text-red-500 underline">Start over</button>';      
        }
        else {
            $response = self::askModelStreamed($history);

            if ($response === false) {
                logger("Exception!");
                $this->enabled = false;
                $this->generating =  false;
                return;
            }
        }



        

        logger(json_encode($response));
        $history[] = [
            "role" => "assistant",
            "content" => $response
        ];
        session()->put("history", $history);

        $this->generating =  false;
    }

    private function useTools($tools, $history)
    {
        logger("userTools for " . count($tools->tools));

        for ($i = 0; $i < min(count($tools->tools), 3); $i++) {
            $tool = $tools->tools[$i];
            logger('Ruuning tool: ' . $tool->tool_name);

            $toolName = $tool->tool_name;
            $toolParams = $tool->parameters;

            $history[] = [
                "role" => 'tool',
                "content" => $this->$toolName($toolParams)
            ];

            $tools = $this->checkTools($history);
        }


        return $history;
    }


    public function weather($parameters)
    {
        logger("Weather: $parameters");
        $url = "https://api.open-meteo.com/v1/forecast?$parameters";

        $response = Http::asJson()->get($url)->getBody()->getContents();
        logger($response);

        return $response;
    }

    public function current_cinema_movies($parameters)
    {
        logger('Movies: the beekeeper');
        return "the beekeeper at 7pm and 10pm";
    }

    public function stocks($ticker)
    {
        logger('Stocks: 1999');
        return "1999";
    }

    public function find_user_location()
    {
        $apiKey = env('GEOCODING_API_KEY');
        $ip = request()->ip() == '127.0.0.1' ? '73.28.84.93' : request()->ip();
        $url = "https://api.ipgeolocation.io/ipgeo?apiKey="
            . $apiKey
            . "&ip=" . $ip;
        $response = Http::asJson()->get($url)->getBody()->getContents();
        logger($response);

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
                "content" => 'You are a helpful assistant who has access to the following lookup tools: 
                    find_user_location - Determines the location of the user based on their IP address,
                    get_current_weather - get the current weather forcast based on users location
                        
                You will be given a excerpt from a conversation along with some known facts. Determine if a fact is missing and if you need to use any of the tools mentioned to obtain that fact.
                If you already have data, do not use a tool. 

                Your response will be a json object only. Do not use variables. "use_tools" property is required. 
                Answer only in JSON using the following format:
                    
                    { 
                    "use_tools": boolean, 
                    "tools": [
                        {
                            "tool_name": string, 
                            "reason_for_use": string,  
                            "parameters": string
                        }
                    ]
                }

                focus on the last comment fo the conversation only. If a question has already been answered, do not require tools
'
            ],
            [
                "role" => "user",
                "content" => '
                facts: 
                ' . json_encode($this->facts) . '
                
               
                conversation history:
                ' . json_encode(session()->get("history")).'

                 
'
            ],

        ];

        logger(json_encode($history));
        $response = $this->askModel($history);
        logger($response);
        $tools = json_decode($response);

        return $tools;
    }


    public function askModel($history)
    {
        $data = [
            "model" => config('services.ollama.model'),
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
            "messages" => $history,
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
            "model" => config('services.ollama.model'),
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
