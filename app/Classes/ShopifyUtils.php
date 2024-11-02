<?php

namespace App\Classes;

use Illuminate\Support\Facades\Http;




class ShopifyUtils
{

    private $token;

    private $apiUrl;

    public function __construct(string $apiUrl, string $token)
    {
        $this->token = $token;
        $this->apiUrl = $apiUrl;
    }


    private function graphql($query)
    {
        $q = ['query' => $query];

        $response = Http::withHeader('X-Shopify-Access-Token', $this->token)
            ->post($this->apiUrl, $q);

        logger($response);
        return $response->json();
    }

    public function requestBulkProducts()
    {

        $result = $this->graphql(<<<QUERY
        mutation {
            bulkOperationRunQuery(
                query: """
                {
                    products (first:20) { 
                        edges { 
                            node { 
                                id 
                                title 
                                description 
                                handle 
                                totalInventory
                                tracksInventory
                                featuredImage {
                                    url( transform: {preferredContentType: JPG, maxWidth:200})
                                }
                                variants (first:1) {
                                    edges {
                                        node {
                                            sku
                                        }
                                    }
                                }
                                original_sku: metafield (key: "custom.original_sku") { 
                                    value
                                }
                                vendor_name: metafield (key: "custom.vendor_name") { 
                                    value
                                } 
                            } 
                        } 
                    }
                }
              """
            ) {
              bulkOperation {
                id
                status
              }
              userErrors {
                field
                message
              }
            }
          }
        QUERY);

        return $result;
    }


    public function downloadBulkProducts($path)
    {
        $status = '';

        do {
            $result = $this->graphql(<<<QUERY
            query {
                currentBulkOperation {
                    id
                    status
                    url
                }
            }
            QUERY);

            $status = $result['data']['currentBulkOperation']['status'];
            sleep(2);
        }
        while ($status != 'COMPLETED');

        file_put_contents($path, fopen($result['data']['currentBulkOperation']['url'], 'r'));
    }

    public function testSettings()
    {
        $result = $this->graphql(<<<QUERY
        query {
            products(first: 1) {
                edges {
                    node {
                    id
            
                    }
                }
            }
        }
        QUERY);

        $id = @$result['data']['products']['edges'][0]['node']['id'];

        return $id != null;
    }

    public function getStoreName()
    {
        $result = $this->graphql(<<<QUERY
        query {
            shop {
              name
            }
        }
        QUERY);

        $name = @$result['data']['shop']['name'];
        //dd($result);
        return $name;
    }

}
