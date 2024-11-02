<?php

namespace App\Console\Commands;

use App\Jobs\SyncProducts;
use Illuminate\Console\Command;
use OpenAI\Laravel\Facades\OpenAI;

class ShopifyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:shopify {action}';

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
            case 'download':
                
                (new SyncProducts())->handle();
                
                break;
            
            default:
                # code...
                break;
        }
    }
}
