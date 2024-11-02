<?php

namespace App\Console\Commands;

use App\Classes\Suggester;
use App\Jobs\GenerateSuggestion;
use App\Models\Suggestion;
use Illuminate\Console\Command;
use OpenAI\Laravel\Facades\OpenAI;
use \Probots\Pinecone\Client as Pinecone;

class OpenAICommand extends Command
{

    private $host = 'https://coastalbleu-embeddings-4cc597e.svc.aped-4627-b74a.pinecone.io';
    private $apiKey = '1f1defc9-4e69-4a9f-bfef-95df2e1f85c9';
    private $index = 'coastalbleu-embeddings';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:openai {action} {--data=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        switch ($this->argument('action')) {
            case 'embedding':
                $response = OpenAI::embeddings()->create([
                    'model' => 'text-embedding-3-small',
                    'input' => 'coastal kitchen decor, beach themed tableware',
                ]);

                $this->info(json_encode($response->embeddings[0]->embedding));
                break;
                
            case 'embedShopify':
                $pinecone = new Pinecone($this->apiKey, $this->host);
        
                $file = storage_path().'/products_out.jsonl';
        
                $fp = @fopen($file, "r");
                if ($fp) {
                    while (($buffer = fgets($fp)) !== false) {
                        $product = json_decode($buffer);
                        
                        $response = OpenAI::embeddings()->create([
                            'model' => 'text-embedding-3-small',
                            'input' => $product->title . ' ' . $product->description
                        ]);
                        $response->object;
                        
                        $response = $pinecone->data()->vectors()->upsert(vectors: [
                            'id' => 'product_'.$product->shopify_id,
                            'values' => $response->embeddings[0]->embedding,
                            'metadata' => [
                                'url' => $product->handle,
                                'title' => $product->title,
                                'image' => @$product->image ?? ''
                            ]
                        ]);
                        $this->info($product->handle);




                    }
                }
                    
                fclose($fp);
                


                break;
                
            case 'suggest':
                $s = new Suggester();
                $response = $s->generateSuggestion([
                    '/collections/bowls/products/coastal-blue-dough-bowl',
                    '/collections/bowls'
                ], 2);
                dd($response);

                
                case 'cookieSuggest':

                //$s = new GenerateSuggestion($this->option('data'));
                
//$s->handle();
    
                case 'cookieSuggestJob':

                   //GenerateSuggestion::dispatch($this->option('data'));
                    
            
            default:
                # code...
                break;
        }
    }
}
