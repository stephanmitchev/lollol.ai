<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \Probots\Pinecone\Client as Pinecone;

class PineconeCommand extends Command
{
    private $host = 'https://coastalbleu-embeddings-4cc597e.svc.aped-4627-b74a.pinecone.io';
    private $apiKey = '1f1defc9-4e69-4a9f-bfef-95df2e1f85c9';
    private $index = 'coastalbleu-embeddings';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:pinecone {action}';

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
        // Initialize Pinecone
        $pinecone = new Pinecone($this->apiKey, $this->host);
        
        switch ($this->argument('action')) {
            case 'deleteIndex':
                $response = $pinecone->control()->index($this->index)->delete();
                $this->info($response);
                break;
            
            case 'createIndex':
                $response = $pinecone->control()->index($this->index)->createServerless(
                    dimension: 1536,
                    cloud: 'aws',
                    region: 'us-east-1',
                    metric: 'cosine',
                    // ... more options    
                );
                $this->info($response);
                break;
            
            default:
                # code...
                break;
        }
        
        
       

    }
}
