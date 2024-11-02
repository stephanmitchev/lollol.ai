<?php

namespace App\Jobs;

use App\Classes\ShopifyUtils;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use stdClass;

class SyncProducts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $s = new ShopifyUtils(env('SHOPIFY_ADMIN_URL')."/api/2024-04/graphql.json", env('SHOPIFY_API_KEY'));

        $filename = storage_path().'/products_storeid_coastalbleu_com.jsonl';
        $filenameOut = storage_path().'/products_out.jsonl';
        
        if (1==0 || !file_exists($filename)) {
            $bulkStatus = $s->requestBulkProducts();

            $s->downloadBulkProducts($filename);
        }
        
        $products = [];

        $fp = @fopen($filename, "r");
        $fpOut = @fopen($filenameOut, "w");
        if ($fp) {
            $product = null;
            while (($buffer = fgets($fp, 8192)) !== false) {
                $data = json_decode($buffer, true);
                if ($product == null) {

                    // Wrong line - multiple variants
                    if(array_key_exists('sku', $data)) {
                        continue;
                    }
                    
                    $product = new stdClass();
                    

                    $product->shopify_id = $data['id'];
                    $product->title = $data['title'];
                    $product->handle = $data['handle'];
                    $product->description = $data['description'];
                    $product->inventory = $data['totalInventory'];
                    $product->track_inventory = $data['tracksInventory'];
                    $product->image = @$data['featuredImage']['url'] ?? null;
                    $product->vendor_sku = @$data['original_sku']['value'] ?? null;
                    $product->vendor_name = @$data['vendor_name']['value'] ?? null;
                
                }
                else {
                    $product->sku = $data['sku'];
                    
                    fputs($fpOut, json_encode($product)."\n");
                    
                    $product = null;
                }



            }
            if (!feof($fp)) {
                echo "Error: unexpected fgets() fail\n";
            }
            fclose($fp);
            fclose($fpOut);


            
        }
            
    }
}
