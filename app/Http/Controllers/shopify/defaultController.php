<?php

namespace App\Http\Controllers\shopify;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class defaultController extends Controller
{
    public function getProducts()
    {
        $client = $this->GraphqlClient();
        $queryString = <<<QUERY
        {
            products (first: 3) {
            edges {
                node {
                id
                title
                }
            }
            }
        }
        QUERY;
        $response = $client->query($queryString);
        $products = $response->getDecodedBody();
        return response()->json($products);
    }
}
