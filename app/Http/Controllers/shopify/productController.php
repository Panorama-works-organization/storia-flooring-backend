<?php

namespace App\Http\Controllers\shopify;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Shopify\Rest\Admin2023_04\Product;
use Shopify\Rest\Admin2023_04\Variant;
use Shopify\Rest\Admin2023_04\Metafield;
use Shopify\Utils;
use Shopify\Clients\Graphql;
use Illuminate\Support\Facades\Http;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Shopify\Rest\Admin2023_04\Image;

class productController extends Controller
{
    public function getProductsByIdsGraph($productsIds)
    {
        try {
            $controller = new Controller;
            $client = $controller->GraphqlClient();

            // Creating an array with al formatted ids
            $formattedIds = array_map(function ($id) {
                return "\"gid://shopify/Product/{$id}\""; // ID global de GraphQL
            }, $productsIds);

            $idsString = implode(', ', $formattedIds);

            // Making the request
            $query = <<<QUERY
            query {
                nodes(ids: [{$idsString}]) {
                    ... on Product {
                        id
                        title
                        handle
                        description
                        status
                        productType
                        vendor
                        tags
                        images(first: 5) {
                            edges {
                                node {
                                    id
                                    originalSrc
                                    altText
                                }
                            }
                        }
                        variants(first: 50) {
                            edges {
                                node {
                                    id
                                    title
                                    price
                                    sku
                                }
                            }
                        }
                        metafields(first: 50) {
                            edges {
                                node {
                                    id
                                    key
                                    value
                                }
                            }
                        }
                    }
                }
            }
            QUERY;

            $target = $client->query(["query" => $query]);
            $target = $target->getDecodedBody();

            if (isset($target['errors'])) {
                throw new \Exception('Función getProductsByIds, Shopify error: ' . $target['errors'][0]['message']);
            }

            $products = $target['data']['nodes'];

            $response = [
                'status' => true,
                'data' => $products
            ];
        } catch (\Exception $e) {
            $response = [
                'status' => false,
                'message' => 'Función getProductsByIds: ' . $e->getMessage()
            ];
        } finally {
            return $response;
        }
    }

    public function getMetaobjectById($metaobjectId)
    {
        try {
            $controller = new Controller;
            $client = $controller->GraphqlClient();

            // Formatear el ID del metaobjeto para GraphQL
            $formattedMetaobjectId = "\"{$metaobjectId}\"";

            // Realizar la solicitud GraphQL
            $query = <<<QUERY
        query {
            node(id: {$formattedMetaobjectId}) {
                ... on Metafield {
                    id
                    key
                    value
                    namespace
                }
            }
        }
        QUERY;

            $response = $client->query(["query" => $query]);
            $decodedResponse = $response->getDecodedBody();

            if (isset($decodedResponse['errors'])) {
                throw new \Exception('Error de Shopify: ' . $decodedResponse['errors'][0]['message']);
            }

            $metaobject = $decodedResponse;

            $result = $metaobject;
        } catch (\Exception $e) {
            $result = [
                'status' => false,
                'message' => 'Error al obtener la información del metaobjeto: ' . $e->getMessage()
            ];
        } finally {
            return $result;
        }
    }
    public function queryImageByName($filename = "Frame_1133")
    {
        $client = new Graphql(env('SHOPIFY_APP_HOST_NAME'), env('SHOPIFY_ACCESS_TOKEN'));
        $query = <<<QUERY
        query {
            files(first:1,query: "filename:$filename") {
                edges {
                    node {
                        ... on MediaImage {
                                id
                        }
                    }
                }
            }
        }
        QUERY;
        $response = $client->query(["query" => $query]);
        $response = $response->getDecodedBody();
        $imageID = $response['data']['files']['edges'][0]['node']['id'];
        return $imageID;
    }
    public function uploadPDFToShopify($fileURL, $type)
    {
        $response = $code = null;
        try {
            $client = new Graphql(env('SHOPIFY_APP_HOST_NAME'), env('SHOPIFY_ACCESS_TOKEN'));
            $query = <<<QUERY
                mutation fileCreate(\$files: [FileCreateInput!]!) {
                    fileCreate(files: \$files) {
                    files {
                        alt
                        createdAt
                        ... on GenericFile {
                            id
                        }
                        ... on MediaImage {
                            id
                        }
                        ... on Video {
                            id
                        }
                        preview{
                        image{
                            url
                            id
                        }
                        status
                        }
                    }
                    userErrors {
                        field
                        message
                    }
                    }
                }
                QUERY;

            $variables = [
                "files" => [
                    [
                        "contentType" => $type,
                        "originalSource" => $fileURL
                    ]
                ]
            ];

            $target = $client->query(["query" => $query, "variables" => $variables]);
            $response = $target->getDecodedBody();

            if (isset($response['data']['fileCreate']['userErrors'][0])) {
                throw new Exception($response['data']['fileCreate']['userErrors'][0]['message']);
            }
            if (isset($response['errors'])) {
                throw new Exception($response['errors'][0]['message']);
            }
            $response = $response['data']['fileCreate']['files'][0]['id'];
            Log::info($type . " uploaded to Shopify: " . $response);
        } catch (Exception $e) {
            $response = [
                "status" => false,
                "error" => $e->getMessage()
            ];
            Log::error("Error uploading file to Shopify: " . $response['data']['fileCreate']['userErrors'][0]['message']);
        } finally {
            return $response;
        }
    }
    function getMetaobjectData($metaobject_id)
    {
        $response = $code = null;
        try {
            $client = new Graphql(env('SHOPIFY_APP_HOST_NAME'), env('SHOPIFY_ACCESS_TOKEN'));
            $query = <<<QUERY
            {
                metaobject(id: "$metaobject_id") {
                    type
                    id
                    fields{
                        key
                        value
                    }
                }
            }
            QUERY;
            $target = $client->query(["query" => $query]);
            $target = $target->getDecodedBody();
            foreach ($target['data']['metaobject']['fields'] as $field) {
                $response[$field['key']] = $field['value'];
            }
            if (isset($response['errors'])) {
                throw new Exception($response['errors'][0]['message']);
            } else {
                $code = 200;
            }
        } catch (\Exception $e) {
            $response = [
                "status" => false,
                "error" => $e->getMessage()
            ];
        } finally {
            return $response;
        }
    }

    public function getProductsMetafields($productsIds)
    {
        try {
            $controller = new Controller;
            $client = $controller->GraphqlClient();

            // Creating an array with al formatted ids
            $formattedIds = array_map(function ($id) {
                return "\"gid://shopify/Product/{$id}\""; // ID global de GraphQL
            }, $productsIds);

            $idsString = implode(', ', $formattedIds);

            // Making the request
            $query = <<<QUERY
            query {
                nodes(ids: [{$idsString}]) {
                    ... on Product {
                        id
                        title
                        metafields(first: 50) {
                            edges {
                                node {
                                    id
                                    key
                                    value
                                }
                            }
                        }
                    }
                }
            }
            QUERY;

            $target = $client->query(["query" => $query]);
            $target = $target->getDecodedBody();

            if (isset($target['errors'])) {
                throw new \Exception('Función getProductsByIds, Shopify error: ' . $target['errors'][0]['message']);
            }

            $products = $target['data']['nodes'];

            $response = [
                'status' => true,
                'data' => $products
            ];
        } catch (\Exception $e) {
            $response = [
                'status' => false,
                'message' => 'Función getProductsByIds: ' . $e->getMessage()
            ];
        } finally {
            return $response;
        }
    }

    public function getProductMetafield($productID)
    {
        try {
            $controller = new Controller;
            $client = $controller->GraphqlClient();
            // Making the request
            $query = <<<QUERY
                query {
                    product(id: "gid://shopify/Product/$productID") {
                        id
                        metafield(key: "imagen_del_plano", namespace: "custom") {
                            reference {
                                ... on MediaImage {
                                    image {
                                        originalSrc
                                    }
                                }
                            }
                        }
                    }
                }
            QUERY;

            $target = $client->query(["query" => $query]);
            $target = $target->getDecodedBody();

            if (isset($target['errors'])) {
                throw new \Exception('Función getVariantsMetafields, Shopify error: ' . $target['errors'][0]['message']);
            }

            if (isset($target['data']['product']['metafield']['reference'])) {
                return $target['data']['product']['metafield']['reference']['image']['originalSrc'];
            } else {
                return null;
            }
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Función getVariantsMetafields: ' . $e->getMessage()
            ];
        }
    }
    public function getImageSrcByMediaImageId($imageId)
    {
        try {
            $controller = new Controller;
            $client = $controller->GraphqlClient();

            // Making the request
            $query = <<<QUERY
            query {
            node(id: "{$imageId}") {
                ... on MediaImage {
                    id
                    image {
                        url
                    }
                }
            }
        }
        QUERY;

            $target = $client->query(["query" => $query]);
            $target = $target->getDecodedBody();

            if (isset($target['errors'])) {
                throw new \Exception('Función getImageSrc, Shopify error: ' . $target['errors'][0]['message']);
            }

            $image = $target['data']['node'];

            // Seleccionar la URL de la imagen (originalSrc, transformedSrc, u otro campo relevante)

            $response = [
                'status' => true,
                'src' => $target
            ];
        } catch (\Exception $e) {
            $response = [
                'status' => false,
                'message' => $e->getMessage()
            ];
        } finally {
            return $response;
        }
    }
    public function getVariantByIdREST()
    {
        $variantId = '42112884211769';
        $variant = Variant::find(
            $this->CurrentSession(),
            $variantId,
            [], // Params
            [] // Options
        );

        return $variant;
    }

    public function getAllProductByIds($productsRequest)
    {
        $productsIds = array_column($productsRequest, 'product_id');
        try {
            $productString = implode(', ', $productsIds);
            $productsData = Product::all(
                $this->CurrentSession(), // Session
                [], // Url Ids
                ["ids" => $productString] // Params
            );
            $products = [];
            foreach ($productsData as $key => $product) {

                // preg_match("/<p>(.*?)<\/p>/", $product->body_html, $matches);
                // dd($product->body_html, $matches);
                // if (count($matches) > 1) {
                //     $description = $matches[1];
                // } else {
                //     $description = "no description";
                // }
                $variantPlaneUrl = $this->getProductMetafield($product->id);
                if (isset($variantPlaneUrl)) {
                    $planSizeUrl = $variantPlaneUrl . '&width=280&height=350';
                } else {
                    $planSizeUrl = '';
                }
                $variants = [];
                foreach ($product->variants as $key => $variant) {
                    $currentProduct = array_filter($productsRequest, function ($item) use ($variant) {
                        return $variant->id === intval($item['variant_id']);
                    });
                    if (count($currentProduct) == 0) {
                        continue;
                    }
                    $currentProduct = reset($currentProduct);

                    $imageUrl = $currentProduct['productImageSrc'];
                    if (intval($currentProduct['variant_id']) === $variant->id) {
                        $variants[$key] = [
                            "id" => $variant->id,
                            "title" => $variant->title,
                            "price" => $variant->price,
                            "sku" => $variant->sku,
                            "image_src" => 'https:' . $imageUrl,
                            "variant_plan" => $planSizeUrl
                        ];
                    }
                }
                $products[] = [
                    "id" => $product->id,
                    "title" => $product->title,
                    "description" => $product->body_html,
                    "handle" => $product->handle,
                    "variants" => $variants
                ];
            }

            return $products;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
    }
    function getImageUrlFromShopify($image_id, $product_id)
    {
        try {
            $image = Image::find(
                $this->CurrentSession(), // Session
                intval($image_id), // Id
                ["product_id" => intval($product_id)], // Url Ids
                [], // Params
            );
            return $image->src;
        } catch (Exception $e) {
            return 'Error getting image from ids, in getImageUrlFromShopify: ' . $e->getMessage() . 'at line' . $e->getLine();
        }
    }
    public function getProductsByIdsREST($productsIds)
    {
        set_time_limit(6000);
        $products = [];
        foreach ($productsIds as $productId) {
            $product = Product::find(
                $this->CurrentSession(), // Session
                $productId, // Actual product id
                [], // Url Ids
                [] // Params
            );

            if ($product) {
                $products[] = $product;
            }
        }
        return $products;
    }



    public function createCatalogMetaobject($fields)
    {
        $type = 'catalogos';
        $response  = null;

        try {
            Log::info("attempting to create catalog metaobject");
            $client = new Graphql(env('SHOPIFY_APP_HOST_NAME'), env('SHOPIFY_ACCESS_TOKEN'));
            $query = <<<QUERY
            mutation CreateMetaobject(\$metaobject: MetaobjectCreateInput!) {
                metaobjectCreate(metaobject: \$metaobject) {
                metaobject {
                    handle
                    id
                }
                userErrors {
                    field
                    message
                    code
                }
                }
            }
            QUERY;
            $variables = [
                "metaobject" => [
                    "type" => $type,
                    "capabilities" => ["publishable" => ["status" => "ACTIVE"]],
                    "fields" => $fields
                ]
            ];

            $target = $client->query(["query" => $query, "variables" => $variables]);
            $targetDecode = $target->getDecodedBody();

            if (isset($targetDecode['errors'][0])) {
                throw new Exception($targetDecode['errors'][0]['message']);
            }
            if (isset($targetDecode['data']['metaobjectCreate']['userErrors'][0])) {
                throw new Exception($targetDecode['data']['metaobjectCreate']['userErrors'][0]['message']);
            }
            $response = [
                "status" => true,
                "metaobject_id" => $targetDecode['data']['metaobjectCreate']['metaobject']['id']
            ];
            Log::info("Catalog created metaobject " . $response['metaobject_id']);
        } catch (\Exception $e) {
            Log::error("Error creating catalog metaobject: " . $e->getMessage());
            $response = [
                "status" => false,
                "message" => $e->getMessage()
            ];
        } finally {
            return $response;
        }
    }

    public function AddCustomerMetafield($customer_id, $catalogIds)
    {

        $key = 'catalogs';
        $namespace = 'custom';
        $type = 'list.metaobject_reference';


        // Decodificar el string JSON a un array de PHP
        //$arrayDatos = json_decode($metafields);

        // Agregar el nuevo ID al array
        //$arrayDatos[] = $id;

        // Codificar el array de vuelta a JSON
        //$value = json_encode($arrayDatos);
        $response = $code = null;
        try {
            Log::info("attempting to update customer metafield");
            $client = new Graphql(env('SHOPIFY_APP_HOST_NAME'), env('SHOPIFY_ACCESS_TOKEN'));
            $query = <<<QUERY
            mutation MetafieldsSet(\$metafields: [MetafieldsSetInput!]!) {
            metafieldsSet(metafields: \$metafields) {
                metafields {
                    id
                    key
                    namespace
                    value
                }
                userErrors {
                    field
                    message
                    code
                }
            }
        }
        QUERY;
            $variables = [
                "metafields" => [
                    [
                        "key" => $key,
                        "namespace" => $namespace,
                        "ownerId" => $customer_id,
                        "type" => $type,
                        "value" => json_encode($catalogIds, JSON_UNESCAPED_SLASHES)
                    ]
                ],
            ];

            $target = $client->query(["query" => $query, "variables" => $variables]);
            $targetDecode = $target->getDecodedBody();
            if (isset($targetDecode['errors'][0])) {
                throw new Exception($targetDecode['errors'][0]['message']);
            }
            if (isset($targetDecode['metafieldsSet']["metafields"]["userErrors"][0])) {
                throw new Exception($targetDecode['metafieldsSet']["metafields"]["userErrors"][0]['message'] . " code " . $targetDecode['metafieldsSet']["metafields"]["userErrors"][0]['code']);
            }
            $response = [
                "status" => true,
                "data" => $targetDecode['data']
            ];
            Log::info("Customer metafield updated");
        } catch (\Exception $e) {
            $response = [
                "status" => false,
                "error" => $e->getMessage()
            ];
        } finally {
            return $response;
        }
    }
}
