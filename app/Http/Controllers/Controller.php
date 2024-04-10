<?php

namespace App\Http\Controllers;

use App\Mail\templateCreatedMail;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Shopify\Auth\Session;
use Shopify\Clients\Graphql;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function CurrentSession()
    {
        $session = new Session(
            Str::uuid(),
            "storia-flooring.myshopify.com",
            false,
            "9f4310d533023e562408a07f8a2a7010"
        );
        $session->setAccessToken(env('SHOPIFY_ACCESS_TOKEN'));
        $session->setScope(env('SHOPIFY_APP_SCOPES'));

        return $session;
    }
    public function GraphqlClient()
    {
        return new Graphql(env('SHOPIFY_APP_HOST_NAME'), env('SHOPIFY_ACCESS_TOKEN'));
    }
    public function updateCustomerMetaobject($custumerId, $metafields)
    {
        try {
            $client = $this->GraphqlClient();
            $query = <<<QUERY
            mutation updateCustomerMetafields(\$input: CustomerInput!) {
                customerUpdate(input: \$input) {
                    customer {
                        id
                        metafields(first: 2) {
                            edges {
                                node {
                                    id
                                    key
                                    value
                                }
                            }
                        }
                    }
                    userErrors {
                        message
                        field
                    }
                }
            }
            QUERY;
            $input = [
                "id" => $custumerId,
                "metafields" => $metafields
            ];
            $target = $client->query(["query" => $query, 'variables' => ['input' => $input]]);
            $target = $target->getDecodedBody();

            if (isset($target['errors'])) {
                throw new \Exception('function updateCustomerMetaobject, Shopify error: ' . $target['errors'][0]['message']);
            }
            $response = [
                'status' => true,
                'data' => $target['data']['customerUpdate']['customer']
            ];
        } catch (\Exception $e) {
            $response = [
                'status' => false,
                'message' => 'function updateCustomerMetaobject: ' . $e->getMessage()
            ];
        } finally {
            return $response;
        }
    }
    function createMetaobject($type = "custom", $fields)
    {
        try {
            $client = $this->GraphqlClient();
            $queryString = <<<QUERY
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
                    "fields" => $fields
                ]
            ];
            $target = $client->query(["query" => $queryString, "variables" => $variables]);
            $target = $target->getDecodedBody();

            if (isset($target['errors'])) {
                throw new \Exception('function createMetaobject Shopify error: ' . $target['errors'][0]['message']);
            }
            $response = [
                'status' => true,
                'data' => $target['data']['metaobjectCreate']['metaobject']
            ];
        } catch (\Exception $e) {
            $response = [
                'status' => false,
                'message' => 'createMetaobject ' . $e->getMessage()
            ];
        } finally {
            return $response;
        }
    }
    public function fileCreate($url)
    {
        try {
            $client = $this->GraphqlClient();
            $queryString = <<<QUERY
            	mutation fileCreate(\$files: [FileCreateInput!]!) {
                    fileCreate(files: \$files) {
                        files {
                            alt
                            createdAt
                            ... on GenericFile {
                                id
                            }
                        }
                    }
                }
            QUERY;
            $variables = [
                "files" => [
                    "contentType" => "FILE",
                    "originalSource" => $url,
                ]
            ];
            $target = $client->query(["query" => $queryString, "variables" => $variables]);
            $target = $target->getDecodedBody();

            if (isset($target['errors'])) {
                throw new \Exception('function fileCreate Shopify error: ' . $target['errors'][0]['message']);
            }
            $response = [
                'status' => true,
                'data' => $target['data']['fileCreate']['files']
            ];
        } catch (\Exception $e) {
            $response = [
                'status' => false,
                'message' => 'fileCreate ' . $e->getMessage()
            ];
        } finally {
            return $response;
        }
    }
    public function getShopifyFileByID($fileId)
    {
        try {
            $client = $this->GraphqlClient();
            $queryString = <<<QUERY
            	query {
                    node(id: "$fileId") {
                        ... on GenericFile {
                            fileStatus
                            id
                            url
                        }
                    }
                }
            QUERY;
            $target = $client->query(["query" => $queryString]);
            $target = $target->getDecodedBody();
            if (isset($target['errors'])) {
                throw new \Exception('function getShopifyFileByID Shopify error: ' . $target['errors'][0]['message']);
            }
            $response = [
                'status' => true,
                'data' => $target['data']['node']
            ];
        } catch (\Exception $e) {
            $response = [
                'status' => false,
                'message' => 'getShopifyFileByID ' . $e->getMessage()
            ];
        } finally {
            return $response;
        }
    }
    public function sendCreatedEmail($customerMail, $catalogName, $date)
    {
        Mail::to($customerMail)->send(new templateCreatedMail($catalogName, $date));
        return "Email sent";
    }
    public function convertImageToWebP($source, $fileName, $quality = 80)
    {
        $extension = pathinfo($source, PATHINFO_EXTENSION);
        $image = imagecreatefromjpeg($source);
        return imagewebp($image, storage_path('/app/public/' . $fileName . '.webp'), $quality);
    }
    public function getImageNameFromUrl($url)
    {
        $urlToArray = explode('/', $url);
        $lastElement = end($urlToArray);
        $nameToArray = explode('.', $lastElement);
        $imageName = $nameToArray[0];
        return $imageName;
    }
}
