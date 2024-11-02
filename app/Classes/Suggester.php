<?php

namespace App\Classes;

use App\Jobs\GenerateSuggestion;
use App\Models\Event;
use App\Models\Suggestion;
use Illuminate\Support\Facades\Http;
use OpenAI\Laravel\Facades\OpenAI;
use \Probots\Pinecone\Client as Pinecone;

class Suggester
{

    private $host = 'https://coastalbleu-embeddings-4cc597e.svc.aped-4627-b74a.pinecone.io';
    private $apiKey = '1f1defc9-4e69-4a9f-bfef-95df2e1f85c9';


    public static function shouldSuggest(Event $event) {
        // Check past suggestions
        self::shouldSuggestBySessionID($event->cssguid);
    }

    public static function shouldSuggestBySessionID($cssguid) {
        if ($cssguid == null || empty($cssguid)) {
            logger('cssguig not provided: '.$cssguid);
            return;
        }

        $s = Suggestion::where('cssguid', $cssguid)->orderBy('created_at', 'desc')->first();
        logger($s);

        if ($s == null || $s->created_at < now()->subMinutes(2)) {   
            $events = Event::where('cssguid', $cssguid)->count();
            logger($events);
            if ($events > 5) {
                logger('Generating suggestion for session '.$cssguid);
               
                $s = new Suggestion();
                $s->cssguid = $cssguid;
                $s->save();

                //logger(json_encode($s));
                GenerateSuggestion::dispatch($s);
            }
            
        }
    }

    public static function doSuggest(Suggestion $suggestion) {
        
        if ($suggestion == null) {
            logger("Null suggestion??");
            return;
        }
        
        $suggeter = new Suggester();
        $handles = $suggeter->handlesByCookie($suggestion->cssguid);
        $result = json_decode($suggeter->generateSuggestion($handles, 2), true);

        logger($result);

        $suggestion->suggestion = $result['text'];
        $suggestion->suggested_products = json_encode($result['products']);
        $suggestion->save();
    }



    public function handlesByCookie($uid) {
        $events = Event::where("cssguid", $uid)
            ->where("value", "LIKE", "%/products/%")
            ->orWhere("value", "LIKE", "%/collections/%")
            ->orderByDesc('created_at')
            ->take(5)
            ->get();
        
        $result = [];
        foreach ($events as $e) {
            //$s .= $e->value;
            preg_match('/(collections\/(?P<collection>.*?))?\/products\/(?P<product>.*?)(\?.*?)?$/',$e->value,$matches);
            if (array_key_exists('collection', $matches) && $matches['collection'] != '') {
                //$s .= $matches['collection'] . ' collection, ';
            }
            if (array_key_exists("product", $matches) && $matches['product'] != '') {
                $s = $matches['product'];
                $result[$s] = $s;
            }
        }

        return array_values($result);
    }

    
    public function generateSuggestion($handles, $numProducts) {

        // Ask for the question
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o',
            
            'messages' => [
                ['role' => 'system', 'content' => 
                    'You are a coastal home decorator and a data scientist who analyses 
                    users browsing history in the form of a comma separated input. Determine 
                    what type of room is the user decorating and What items would the user 
                    need to complete a beatiful space in addition to the ones they have already browsed? 
                    You respond with 2-3 comma-separated phrases. Respond only with the search phrases. 
                    Here is the browsing history:'],
                ['role' => 'user', 'content' => implode(',', $handles)],
            ],
        ]);
      
        $search = $response->choices[0]->message->content;
        logger("History: ".implode(',', $handles));
        logger("Vector search for: $search");

        // Embed
        $response = OpenAI::embeddings()->create([
            'model' => 'text-embedding-3-small',
            'input' => $search,
        ]);

        // Query
        
        $pinecone = new Pinecone($this->apiKey, $this->host);
        
        $response = $pinecone->data()->vectors()->query(
            vector: $response->embeddings[0]->embedding,
            topK: $numProducts,
        );

        //logger(json_encode($response->json()['matches']));
        $searchResults = [];
        foreach ($response->json()['matches'] as $match) {
            $searchResults[] = [
                'id' => str_replace('gid://shopify/Product/', '', $match['id']),
                'title' => $match['metadata']['title'],
                'image' => @$match['metadata']['image'] ?? '',
                'url' => "https://coastalbleu.com/products/".$match['metadata']['url']
            ];
        }


        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o',
            'temperature' => 0,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful coastal design store assistant who receives 
                input from a vector database search result and creates a short paragraph for a user to consider 
                looking at certain complementary products. Determine 
                    what type of room is the user decorating and how will the products you receive as input fit into that space. The products will be hyperlinked to their urls. Your input is in JSON format. You will respond in 
                JSON without markdown where "text" will contain partial html without html and body tags with 1-2 paragraphs, 
                of why the selected prodcuts are a good fit. Do not add a heading.
                "products" will contain the aforementioned products id(number only), title, image, recommendation(full paragraph), and url. 
                the recommendation property will contain a brief design recommendation'],
                ['role' => 'user', 'content' => json_encode($searchResults)],
            ],
        ]);

        logger($response->choices[0]->message->content);
      
        //logger(json_encode(json_decode($response->choices[0]->message->content), JSON_PRETTY_PRINT));
        return $response->choices[0]->message->content;


    }




}
