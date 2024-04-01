<?php

namespace App\Http\Controllers\shopify;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\editorRequestMail;
use App\Mail\EmailNotification;
use Carbon\Carbon;
use Exception;
use Shopify\Clients\Graphql;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class customerController extends Controller
{
    function getCatalogAccessStatus($customerId)
    {
        try {
            $client = $this->GraphqlClient();
            $queryString = <<<QUERY
            {
                customer(id: "$customerId") {
                    id
                    metafield(key: "catalog_access", namespace: "custom"){
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
                throw new \Exception('function getCatalogAccessStatus Shopify error: ' . $target['errors'][0]['message']);
            }
            $response = [
                'status' => true,
                'data' => $target['data']['customer']
            ];
        } catch (\Exception $e) {
            $response = [
                'status' => false,
                'message' => 'getCatalogAccessStatus ' . $e->getMessage()
            ];
        } finally {
            return $response;
        }
    }

    public function updateCustomerStatus(Request $request)
    {
        try {
            $request->validate([
                'customer_id' => 'required',
                'customer_name' => 'required|string',
                'customer_email' => 'required|string',
                'status' => 'required|string',
            ]);
            $response = $code = null;
            $response = $this->updateStatus($request->customer_id, $request->status);
            if (!$response['status']) {
                throw new \Exception($response['message']);
            }
            $data = [
                'customerName' => $request->customer_name,
                'date' => now()->format('d/m/Y'),
            ];
            Mail::to($request->customer_email)->send(new EmailNotification($data));
            $code = 200;
        } catch (\Exception $e) {
            $response = [
                'status' => false,
                'message' => $e->getMessage()
            ];
            $code = 500;
        } finally {
            return response()->json($response, $code);
        }
    }
    public function updateStatus($customerId, $status)
    {
        try {
            $shopifyId = 'gid://shopify/Customer/' . $customerId;
            $customer = $this->getCatalogAccessStatus($shopifyId);
            if (!$customer['status']) {
                throw new \Exception($customer['message']);
            }
            $currentMetafield = $customer['data']['metafield'];
            $metafields = [];
            if (isset($currentMetafield)) {
                $metafields[] = [
                    "id" => $currentMetafield['id'],
                    "value" => $status,
                ];
            } else {
                $metafields[] = [
                    "key" => "catalog_access",
                    "value" => $status,
                    "namespace" => "custom"
                ];
            }
            $updateCustomerMetafield = $this->updateCustomerMetaobject($shopifyId, $metafields);
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
                'message' => $e->getMessage()
            ];
        } finally {
            return $response;
        }
    }
    public function getStoreEmail()
    {
        $client = new Graphql(env('SHOPIFY_APP_HOST_NAME'), env('SHOPIFY_ACCESS_TOKEN'));
        $query = <<<QUERY
        query {
            shop {
              email
            }
          }
        QUERY;
        $response = $client->query(["query" => $query]);
        $getDataResponse = $response->getDecodedBody();
        $zepikaMail = $getDataResponse['data']['shop']['email'];

        return $zepikaMail;
    }
    //Sending request email to be a editor
    function sendEditorRequestEmail(Request $request)
    {
        $response = $code = null;
        try {
            $request->validate([
                'customer_name' => 'required|string',
                'customer_email' => 'required|string',
                'customer_id' => 'required',
                'customer_rol' => 'required',
                'update_status_url' => 'required'
            ]);
            //$adminEmail = 'amador@panorama.works';
            $adminEmail = $this->getStoreEmail();
            $approveRequestUrl = $request->update_status_url . '?customer_id=' . $request->customer_id . '&customer_name=' . $request->customer_name . '&customer_email=' . $request->customer_email . '&customer_rol=' . $request->customer_rol . '&status=Active Designer';
            $adminName = "Daniel Orozco";
            $fechaActual = Carbon::now();
            $date = $fechaActual->format('d/m/Y');
            $data = [
                'customerMail' => $request->customer_email,
                'customerName' => $request->customer_name,
                'customerID' => $request->customer_id,
                'customerRol' => $request->customer_rol,
                'approveRequestUrl' => $approveRequestUrl,
                'adminName' => $adminName,
                'date' => $date
            ];

            $updateStatus = $this->updateStatus($request->customer_id, 'Pending Approval');
            if (!$updateStatus['status']) {
                throw new \Exception($updateStatus['message']);
            }
            Mail::to($adminEmail)->send(new editorRequestMail($data));

            $response = [
                'status' => true,
                'message' => 'La solicitud has sido enviada.'
            ];
            $code = 200;
        } catch (\Exception $e) {
            $response = [
                'status' => false,
                'error' => $e->getMessage()
            ];
            $code = 500;
        } finally {
            return response()->json($response, $code);
        }
    }

    public function getCustomerData($email)
    {
        $customerMail = $email;
        $response = $code = null;
        try {
            $client = new Graphql(env('SHOPIFY_APP_HOST_NAME'), env('SHOPIFY_ACCESS_TOKEN'));
            $query = <<<QUERY
            query {
                customers(first: 1, query: "email:$customerMail") {
                    edges {
                        node {
                        id
                        legacyResourceId
                        displayName
                        tags
                        metafields(first:5){
                            edges{
                                node{
                                        key
                                        value
                                    }
                                }
                            }
                        }
                    }
                }
            }
            QUERY;
            $target = $client->query(["query" => $query]);
            $response = $target->getDecodedBody();
            if (isset($response['errors'])) {
                throw new Exception($target->getDecodedBody()['errors'][0]['message']);
            } else {
                $response = $response['data']['customers']['edges'][0]['node'];
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

    public function savePdfToMetafields($customerId, $pdfUrl)
    {
        $response = $code = null;

        try {
            // Construye la URL de la tienda Shopify y el token de acceso
            $shopUrl = env('SHOPIFY_APP_HOST_NAME');
            $accessToken = env('SHOPIFY_ACCESS_TOKEN');

            // Construye el cliente GuzzleHTTP
            $client = new Client([
                'base_uri' => "https://$shopUrl",
                'headers' => [
                    'X-Shopify-Access-Token' => $accessToken,
                    'Content-Type' => 'application/json',
                ],
            ]);

            // Descarga el contenido del archivo PDF desde la URL
            $pdfContent = file_get_contents($pdfUrl);

            // Codifica el contenido del archivo PDF en base64
            $pdfBase64 = base64_encode($pdfContent);

            // Construye la consulta GraphQL para actualizar los metafields
            $query = <<<QUERY
            mutation {
                customerUpdate(input: {
                    id: "$customerId",
                    metafields: [
                        {
                            key: "pdf_file",
                            value: "$pdfBase64",
                            type: base64
                        }
                    ]
                }) {
                    customer {
                        id
                    }
                }
            }
            QUERY;

            // Realiza la solicitud GraphQL usando GuzzleHTTP
            $response = $client->post('/admin/api/2022-01/graphql.json', [
                'body' => json_encode(['query' => $query]),
            ]);

            $body = json_decode($response->getBody(), true);

            if (isset($body['errors'])) {
                $code = 500;
                $response = [
                    "status" => false,
                    "error" => $body['errors'][0]['message']
                ];
            } else {
                $code = 200;
                $response = [
                    "status" => true,
                    "message" => "Archivo PDF guardado exitosamente en los metafields del usuario."
                ];
            }
        } catch (\Exception $e) {
            $code = 500;
            $response = [
                "status" => false,
                "error" => $e->getMessage()
            ];
        } finally {
            return response()->json($response, $code);
        }
    }
    public function getCustomerMetafields($id = "gid://shopify/Customer/6146723381305")
    {
        $response = null;
        try {
            Log::info("attempting to get customer metafields");
            $client = new Graphql(env('SHOPIFY_APP_HOST_NAME'), env('SHOPIFY_ACCESS_TOKEN'));
            $query = <<<QUERY
            query {
                customer(id: "$id") {
                    metafield(key: "catalogs", namespace: "custom") {
                        id
                        value
                    }
                }
            }
            QUERY;
            $target = $client->query(["query" => $query]);
            $targetDecode = $target->getDecodedBody();

            if (isset($targetDecode['data']['customer']['metafield'])) {
                $catalogsIds = json_decode($targetDecode['data']['customer']['metafield']['value']);
                $metafieldID = $targetDecode['data']['customer']['metafield']['id'];
            } else {
                $metafieldID = null;
                $catalogsIds = [];
            }
            return [
                "status" => true,
                "metafield_id" => $metafieldID,
                "catalog_ids" => $catalogsIds
            ];
        } catch (Exception $e) {
            return [
                "status" => false,
                "error" => $e->getMessage()
            ];
        }
    }
}
