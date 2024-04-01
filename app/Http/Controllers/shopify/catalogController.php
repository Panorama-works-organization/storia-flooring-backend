<?php

namespace App\Http\Controllers\shopify;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class catalogController extends Controller
{
    public function initialize($customerId, $fileUrl, $internalName, $catalogName, $catalogDescription, $date, $email, $clientName, $arrayProductos)
    {
        $customerId = "gid://shopify/Customer/6146723381305";
        $fileUrl = "https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf";
        $fields = [
            [
                "key" => "internal_name",
                "value" => $internalName
            ],
            [
                "key" => "catalog_name",
                "value" => $catalogName
            ],
            [
                "key" => "catalog_description",
                "value" => $catalogDescription
            ],
            [
                "key" => "catalog_date",
                "value" => $date
            ],
            [
                "key" => "email",
                "value" => $email
            ],
            [
                "key" => "client_name",
                "value" => $clientName
            ],
            [
                "key" => "products",
                "value" => json_encode($arrayProductos, JSON_UNESCAPED_SLASHES)
            ]
        ];
        $featureImageName = "CasaTropicalTulum_portada";
        $newCatalog = $this->createCatalog($customerId, $fileUrl, $fields, $featureImageName);
        dd($newCatalog);
    }
    
    public function createCatalog($customerId = "gid://shopify/Customer/6146723381305", $fileUrl = "https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf", $fields, $imageName = "CasaTropicalTulum_portada")
    {
        try {
            $type = "catalogos";
            $uploadCatalogResponse = $this->fileCreate($fileUrl);
            if (!$uploadCatalogResponse['status']) {
                throw new \Exception($uploadCatalogResponse['message']);
            }
            $catalogData = $this->getShopifyFileByID($uploadCatalogResponse['data'][0]['id']);
            if (!$catalogData['status']) {
                throw new \Exception($catalogData['message']);
            }
            $imageID = $this->getImageIdByName($imageName);
            if (!$imageID['status']) {
                throw new \Exception($imageID['message']);
            }
            $fileId = $catalogData['data']['id'];
            array_push($fields, [
                "key" => "catalog_image",
                "value" => $imageID['data']['id']
            ]);
            array_push($fields, [
                "key" => "catalog_file",
                "value" => $fileId
            ]);

            $catalogMetaobject = $this->createMetaobject($type, $fields);
            if (!$catalogMetaobject['status']) {
                throw new \Exception($catalogMetaobject['message']);
            }
            $catalogMetaobjectId = $catalogMetaobject['data']['id'];
            $customerData = $this->getCustomerCatalogs($customerId);
            if (!$customerData['status']) {
                throw new \Exception($customerData['message']);
            }
            $customerMetafield = $customerData['data']['metafield'];
            $customerFields = [];
            if (isset($customerMetafield)) {
                $customerCatalosIds = json_decode($customerMetafield['value'], true);
                array_push($customerCatalosIds, $catalogMetaobjectId);
                $customerFields = [
                    [
                        "id" => $customerMetafield['id'],
                        "value" => json_encode($customerCatalosIds, JSON_UNESCAPED_SLASHES)
                    ]
                ];
            } else {
                $customerCatalosIds = [$catalogMetaobjectId];
                $customerFields = [
                    [
                        "key" => "catalogs",
                        "namespace" => "catalogos",
                        "value" => json_encode($customerCatalosIds, JSON_UNESCAPED_SLASHES)
                    ]
                ];
            }
            $updateCustomerMetafield = $this->updateCustomerMetaobject($customerId, $customerFields);
            if (!$updateCustomerMetafield['status']) {
                throw new \Exception($updateCustomerMetafield['message']);
            }
            $response = [
                'status' => true,
                'data' => $updateCustomerMetafield['data']
            ];
        } catch (\Exception $e) {
            $response = [
                'status' => false,
                'data' => $e->getMessage()
            ];
        } finally {
            return $response;
        }
    }
    function getImageIdByName($fileName)
    {
        try {
            $client = $this->GraphqlClient();
            $queryString = <<<QUERY
            query {
                files(first: 1, query: "filename:$fileName") {
                    edges {
                        node {
                            ... on MediaImage {
                                id
                                image {
                                    originalSrc: url
                                }
                            }
                        }
                    }
                }
            }
            QUERY;
            $target = $client->query(["query" => $queryString]);
            $target = $target->getDecodedBody();
            if (isset($target['errors'])) {
                throw new \Exception('function getImageIdByName Shopify error: ' . $target['errors'][0]['message']);
            }
            $response = [
                'status' => true,
                'data' => $target['data']['files']['edges'][0]['node']
            ];
        } catch (\Exception $e) {
            $response = [
                'status' => false,
                'message' => 'getImageIdByName ' . $e->getMessage()
            ];
        } finally {
            return $response;
        }
    }
    function getCustomerCatalogs($customerId)
    {
        try {
            $client = $this->GraphqlClient();
            $queryString = <<<QUERY
            {
                customer(id: "$customerId") {
                    id
                    metafield(key: "catalogs", namespace: "custom"){
                        id
                        key
                        value
                    }
                }
            }
            QUERY;
            $target = $client->query($queryString);
            $target = $target->getDecodedBody();

            if (isset($target['errors'])) {
                throw new \Exception('function getCustomerCatalogs Shopify error: ' . $target['errors'][0]['message']);
            }
            $response = [
                'status' => true,
                'data' => $target['data']['customer']
            ];
        } catch (\Exception $e) {
            $response = [
                'status' => false,
                'message' => 'getCustomerCatalogs ' . $e->getMessage()
            ];
        } finally {
            return $response;
        }
    }
}
