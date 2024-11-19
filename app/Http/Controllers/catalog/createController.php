<?php

namespace App\Http\Controllers\catalog;

use App\Http\Controllers\Controller;
use App\Http\Controllers\shopify\customerController;
use App\Http\Controllers\shopify\productController;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PDF;
use Dompdf;
use Dompdf\Dompdf as DompdfDompdf;

class createController extends Controller
{
    public function createCatalog(Request $request)
    {
        $response = $code = null;
        try {
            Log::info('---Init new catalog---');
            $request->validate([
                'customerMail' => 'required|string',
                'customerGID' => 'required',
                'catalogName' => 'required|string',
                'catalogSubtitle' => 'required|string',
                'catalogDate' => 'required',
                'customerName' => 'required|string',
                'firstSlideImageURL' => 'required|url',
                'productsIds' => 'required|array',
            ]);

            Log::info('Pass validation request data');
            $catalogDataCompiled = $this->compileCatalogData($request);

            Log::info('Data compiled');
            $pdf = PDF::loadView('catalog', [
                "data" => $catalogDataCompiled
            ]);

            Log::info('Data pass to catalog view');
            $pdfFilename = $catalogDataCompiled['catalogNameChanged'] . '-' . now()->timestamp . '.pdf';
            Storage::disk('public')->put($pdfFilename, $pdf->output());
            $pdf_url = Storage::disk('public')->url($pdfFilename);
            Log::info('The pdf_url is: ' . $pdf_url);

            Log::info('catalog stored in server');
            $catalogDate = new \DateTime($catalogDataCompiled['date']);
            $productController = new productController;

            $pdfShopifyId = $productController->uploadPDFToShopify($pdf_url, "FILE");
            Log::info('catalog updated to server');
            $firstImageName = $this->getImageNameFromUrl($catalogDataCompiled['firstSlideImageURL']);
            $firstImageId = $productController->queryImageByName($firstImageName);
            $type = 'IMAGE';
            $imageGID = $productController->uploadPDFToShopify($catalogDataCompiled['firstSlideImageURL'], $type);


            if ($firstImageId != null) {
                $catalogsFields = [
                    [
                        "key" => "internal_name",
                        "value" => $catalogDataCompiled['catalog_internal_name'],
                    ],
                    [
                        "key" => "name",
                        "value" => $catalogDataCompiled['catalogName'],
                    ],
                    // [
                    //     "key" => "image",
                    //     "value" => $firstImageId,
                    // ],
                    [
                        "key" => "image",
                        "value" => $imageGID,
                    ],
                    [
                        "key" => "date",
                        "value" => date_format($catalogDate, DateTime::ATOM),
                    ],
                    [
                        "key" => "client_email",
                        "value" => $catalogDataCompiled['customerMail'],
                    ],
                    [
                        "key" => "client_name",
                        "value" => $catalogDataCompiled['customerName'],
                    ],
                    [
                        "key" => "file",
                        "value" => $pdfShopifyId
                    ]
                ];
            } else {
                $catalogsFields = [
                    [
                        "key" => "internal_name",
                        "value" => $catalogDataCompiled['catalog_internal_name'],
                    ],
                    [
                        "key" => "name",
                        "value" => $catalogDataCompiled['catalogName'],
                    ],
                    [
                        "key" => "image",
                        "value" => $imageGID,
                    ],
                    [
                        "key" => "date",
                        "value" => date_format($catalogDate, DateTime::ATOM),
                    ],
                    [
                        "key" => "client_email",
                        "value" => $catalogDataCompiled['customerMail'],
                    ],
                    [
                        "key" => "client_name",
                        "value" => $catalogDataCompiled['customerName'],
                    ],
                    [
                        "key" => "file",
                        "value" => $pdfShopifyId
                    ]
                ];
            }

            $catalogMetaobject = $productController->createCatalogMetaobject($catalogsFields);
            if (!$catalogMetaobject) {
                throw new Exception($catalogMetaobject['message']);
            }
            Log::info('catalog metaobject crated');
            $updateCustomerResponse = $this->updateCustomerCatalogMetafield($catalogDataCompiled['customerId'], $catalogMetaobject['metaobject_id']);
            if (!$updateCustomerResponse) {
                throw new Exception($updateCustomerResponse['message']);
            }
            Log::info('catalog metafield updated');
            $this->sendCreatedEmail($catalogDataCompiled['customerMail'], $catalogDataCompiled['catalogName'], now()->format('d/m/Y'));
            Log::info('Mail sent to: ' . $catalogDataCompiled['customerMail']);
            $response = [
                "status" => true,
                "message" => "Catalog has been created an added it to its customer, mail has sent",
                "pdf_url" => $pdf_url
            ];
            $code = 200;
        } catch (Exception $e) {
            log::error($e->getMessage());
            $response = [
                "status" => false,
                "message" => $e->getMessage()
            ];
            $code = 500;
        } finally {
            log::info("---End new catalog---");
            return response()->json($response, $code);
        }
    }

    public function updateCustomerCatalogMetafield($customerId, $metaobjectID)
    {
        $customerController = new customerController;
        $customerGID = 'gid://shopify/Customer/' . $customerId;
        $customerCatalogMetafield = $customerController->getCustomerMetafields($customerGID);

        if (!$customerCatalogMetafield['status']) {
            throw new Exception($customerCatalogMetafield['message']);
        }
        array_push($customerCatalogMetafield['catalog_ids'], $metaobjectID);
        if ($customerCatalogMetafield['metafield_id'] !== null) {
            $metafields = [
                [
                    "id" => $customerCatalogMetafield['metafield_id'],
                    "value" => json_encode($customerCatalogMetafield['catalog_ids'])
                ]
            ];
        } else {
            $metafields = [
                [
                    "namespace" => 'custom',
                    "key" => 'catalogs',
                    "value" => json_encode($customerCatalogMetafield['catalog_ids'])
                ]
            ];
        }

        $updateMetafieldResponse = $this->updateCustomerMetaobject($customerGID, $metafields);
        if (!$updateMetafieldResponse['status']) {
            throw new Exception($updateMetafieldResponse['message']);
        }
        return $updateMetafieldResponse;
    }

    public function compileCatalogData($request)
    {
        $customerController = new customerController();
        $customerGID = $request->customerId;

        $timeStamp = now()->format('YmdHis');
        $customerId = explode('/', $customerGID)[4];
        $internalName = $customerId . '_catalog_' .  $timeStamp;
        //$productController = new productController;
        //$products = $productController->getAllProductByIds($request->productsIds);
        $polishedProducts = $this->removeComaFromMetafields($request->products);
        $upperlinedProductKeys = $this->formatProductStrings($polishedProducts);
        $catalogNameChanged = str_replace(' ', '_', $request->catalogName);
        $portraitImageUrl = $request->firstSlideImageURL;
        $portraitImageUrl = preg_replace('/_300x300/', '', $portraitImageUrl);
        $portraitImageUrl .= '&width=1920';
        $formatDate = date('d.m.Y', strtotime($request->catalogDate));
        $templateData = [
            'products' => $upperlinedProductKeys,
            'date' => $formatDate,
            'customerName' => $request->customerName,
            'catalogName' => $request->catalogName,
            'catalogSubtitle' => $request->catalogSubtitle,
            'firstSlideImageURL' => $portraitImageUrl,
            'catalogNameChanged' => $catalogNameChanged,
            'timeStamp' => $timeStamp,
            'customerId' => $customerId,
            'customerMail' => $request->customerMail,
            'catalog_internal_name' => $internalName
        ];
        return $templateData;
    }

    function removeComaFromMetafields($products) {
        foreach ($products as &$product) {
            foreach($product["metafields"] as &$metafield) {
                $ultimoCaracter = substr($metafield["value"], -1);
                if ($ultimoCaracter == ",") {
                    $ultimoCaracter = "";
                    $metafield["value"] = substr($metafield["value"], 0, -1) . $ultimoCaracter;
                }
            }
        }
        return $products;
    }


    public function createCatalog2(Request $request)
    {
        $response = $code = null;
        try {
            Log::info('---Init new catalog---');
            $request->validate([
                'customerMail' => 'required|string',
                'catalogName' => 'required|string',
                'customerName' => 'required|string',
                'customerId' => 'required',
                'catalogDate' => 'required',
                'firstSlideImageURL' => 'required|url',
                'products' => 'required|array',
            ]);
            Log::info('Pass validation request data');

            $catalogDataCompiled = $this->compileCatalogData($request);

            Log::info('Data compiled');
            $pdf = PDF::loadView('catalog2', [
                "data" => $catalogDataCompiled
            ]);

            Log::info('Data pass to catalog view');
            $pdfFilename = $catalogDataCompiled['catalogNameChanged'] . '-' . now()->timestamp . '.pdf';
            Storage::disk('public')->put($pdfFilename, $pdf->output());
            $pdf_url = Storage::disk('public')->url($pdfFilename);
            Log::info('PDF URL: ' . $pdf_url);
            $correct_pdf_url = 'https://api-storia.panorama.works/storage/app/public/' . $pdfFilename;
            Log::info('CORRECT PDF URL: ' . $correct_pdf_url);
            Log::info('catalog stored in server');
            $catalogDate = new \DateTime($catalogDataCompiled['date']);
            $productController = new productController;

            $pdfShopifyId = $productController->uploadPDFToShopify($correct_pdf_url, "FILE");
            Log::info('catalog updated to server');
            $firstImageName = $this->getImageNameFromUrl($catalogDataCompiled['firstSlideImageURL']);
            $firstImageId = $productController->queryImageByName($firstImageName);
            $type = 'IMAGE';
            $imageGID = $productController->uploadPDFToShopify($catalogDataCompiled['firstSlideImageURL'], $type);


            if ($firstImageId != null) {
                $catalogsFields = [
                    [
                        "key" => "internal_name",
                        "value" => $catalogDataCompiled['catalog_internal_name'],
                    ],
                    [
                        "key" => "name",
                        "value" => $catalogDataCompiled['catalogName'],
                    ],
                    // [
                    //     "key" => "image",
                    //     "value" => $firstImageId,
                    // ],
                    [
                        "key" => "image",
                        "value" => $imageGID,
                    ],
                    [
                        "key" => "date",
                        "value" => date_format($catalogDate, DateTime::ATOM),
                    ],
                    [
                        "key" => "client_email",
                        "value" => $catalogDataCompiled['customerMail'],
                    ],
                    [
                        "key" => "client_name",
                        "value" => $catalogDataCompiled['customerName'],
                    ],
                    [
                        "key" => "file",
                        "value" => $pdfShopifyId
                    ]
                ];
            } else {
                $catalogsFields = [
                    [
                        "key" => "internal_name",
                        "value" => $catalogDataCompiled['catalog_internal_name'],
                    ],
                    [
                        "key" => "name",
                        "value" => $catalogDataCompiled['catalogName'],
                    ],
                    [
                        "key" => "image",
                        "value" => $imageGID,
                    ],
                    [
                        "key" => "date",
                        "value" => date_format($catalogDate, DateTime::ATOM),
                    ],
                    [
                        "key" => "client_email",
                        "value" => $catalogDataCompiled['customerMail'],
                    ],
                    [
                        "key" => "client_name",
                        "value" => $catalogDataCompiled['customerName'],
                    ],
                    [
                        "key" => "file",
                        "value" => $pdfShopifyId
                    ]
                ];
            }

            $catalogMetaobject = $productController->createCatalogMetaobject($catalogsFields);
            if (!$catalogMetaobject) {
                throw new Exception($catalogMetaobject['message']);
            }
            Log::info('catalog metaobject crated');
            $updateCustomerResponse = $this->updateCustomerCatalogMetafield($catalogDataCompiled['customerId'], $catalogMetaobject['metaobject_id']);
            if (!$updateCustomerResponse) {
                throw new Exception($updateCustomerResponse['message']);
            }
            Log::info('catalog metafield updated');
            $this->sendCreatedEmail($catalogDataCompiled['customerMail'], $catalogDataCompiled['catalogName'], now()->format('d/m/Y'));
            Log::info('Mail sent to: ' . $catalogDataCompiled['customerMail']);
            $response = [
                "status" => true,
                "message" => "Catalog has been created an added it to its customer, mail has sent"
            ];
            $code = 200;
        } catch (Exception $e) {
            log::error($e->getMessage());
            $response = [
                "status" => false,
                "message" => $e->getMessage()
            ];
            $code = 500;
        } finally {
            log::info("---End new catalog---");
            return response()->json($response, $code);
        }
    }

    public function formatProductStrings($products)
    {
        foreach ($products as &$product) {
            foreach ($product['metafields'] as &$metafield) {
                $metafield['key'] = strtoupper($metafield['key']);
                $metafield['key'] = str_replace("_", " ", $metafield['key']);
                $metafield['value'] = str_replace("&quot;", "'", $metafield['value']);
            }
            foreach ($product['options'] as &$option) {
                $option['name'] = strtoupper($option['name']);
                $option['values'] = str_replace("_", " ", $option['values']);
                $option['values'] = str_replace("&quot;", "'", $option['values']);
            }
        }

        return $products;
    }
    public function test(Request $request)
    {
        try {
            Log::info('---Init new catalog---');
            $request->validate([
                'customerMail' => 'required|string',
                'catalogName' => 'required|string',
                'customerName' => 'required|string',
                'customerId' => 'required',
                'catalogDate' => 'required',
                'firstSlideImageURL' => 'required|url',
                'products' => 'required|array',
            ]);
            Log::info('Pass validation request data');

            $catalogDataCompiled = $this->compileCatalogData($request);

            Log::info('Data compiled');
            $pdf = PDF::loadView('catalog2', [
                "data" => $catalogDataCompiled
            ]);

            Log::info('Data pass to catalog view');
            $pdfFilename = $catalogDataCompiled['catalogNameChanged'] . '-' . now()->timestamp . '.pdf';
            Storage::disk('public')->put($pdfFilename, $pdf->output());
            $pdf_url = Storage::disk('public')->url($pdfFilename);
            Log::info('PDF URL: ' . $pdf_url);
            return response()->json([
                "status" => true,
                "message" => "Catalog has been created an added it to its customer, mail has sent",
                "pdf_url" => $pdf_url
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => false,
                "message" => $e->getMessage() . " - " . $e->getLine()
            ]);
        }
    }
    // public function test(Request $request)
    // {
    //     $response = $code = null;
    //     try {
    //         Log::info('---Init new catalog---');
    //         $request->validate([
    //             'customerMail' => 'required|string',
    //             'catalogName' => 'required|string',
    //             'customerName' => 'required|string',
    //             'customerId' => 'required',
    //             'catalogDate' => 'required',
    //             'firstSlideImageURL' => 'required|url',
    //             'products' => 'required|array',
    //         ]);
    //         Log::info('Pass validation request data');

    //         $catalogDataCompiled = $this->compileCatalogData($request);
    //         dd($catalogDataCompiled);
    //         Log::info('Data compiled');
    //         $pdf = PDF::loadView('catalog2', [
    //             "data" => $catalogDataCompiled
    //         ]);

    //         Log::info('Data pass to catalog view');
    //         $pdfFilename = $catalogDataCompiled['catalogNameChanged'] . '-' . now()->timestamp . '.pdf';
    //         Storage::disk('public')->put($pdfFilename, $pdf->output());
    //         $pdf_url = Storage::disk('public')->url($pdfFilename);
    //         Log::info('PDF URL: ' . $pdf_url);
    //         return true;
    //     } catch (Exception $e) {
    //         return false;
    //     }
    // }
}
