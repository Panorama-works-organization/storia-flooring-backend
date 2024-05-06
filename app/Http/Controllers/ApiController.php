<?php

namespace App\Http\Controllers;

//require_once '../vendor/autoload.php';

use Illuminate\Http\Request;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Slides;
use Carbon\Carbon;
use App\Http\Controllers\shopify\catalogController;
use App\Http\Controllers\shopify\productController;
use App\Http\Controllers\slidesController;
use Exception;
use Google\Service\AdExchangeBuyerII\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\templateCreatedMail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\shopify\customerController;
use App\Jobs\catalogCreatorJob;
use GuzzleHttp\Client as GuzzleClient;

class ApiController extends Controller
{

    public function start(Request $request)
    {

        // Setting a longer time limit because the original is not enough
        set_time_limit(6000);

        try {
            // Validating request data
            $request->validate([
                'customerMail' => 'required|string',
                'catalogName' => 'required|string',
                'catalogDate' => 'required',
                'customerName' => 'required|string',
                'firstSlideImageURL' => 'required|url',
                'productsIds' => 'required|array',
            ]);
            Log::info("----New catalog init----");
            // Saving that information in vars
            $fechaActual = Carbon::now();
            $timeStamp = $fechaActual->format('YmdHis');
            $shopifyDate = $request->input('catalogDate');
            $date = $fechaActual->format('d/m/Y');
            $response = null;
            $code = null;
            $customerName = $request->input('customerName');
            $customerMail = $request->input('customerMail');

            $catalogName = $request->input('catalogName');
            $catalogNameChanged = str_replace(' ', '_', $catalogName);

            $productos = $request->input('productsIds');

            $firstSlideImageURL = $request->input('firstSlideImageURL');
            Log::info("assing values");
            $customerController = new customerController();
            $customerGID = $customerController->getCustomerData($customerMail);


            $statusCode = $customerGID->getStatusCode();

            if ($statusCode == 200) {
                $responseData = $customerGID->getData(true);
                $customerGID = $responseData['id'];
            } else {
                throw new Exception('Cannot get customerGID');
            }
            $parts = explode('/', $customerGID);
            $customerId = end($parts);
            $internalName = $customerId . '_catalog_' . $timeStamp;
            Log::info("get customer id" . $customerId);

            $productController = new productController;
            $products = $productController->getProductsByIdsREST($productos);
            Log::info("get product info");

            // HERE I GET ALL THE PRODUCT METAFIELDS
            //$productMetafields = $productController->getProductsMetafields($productos);
            //dd($productMetafields);
            // $productMetafields = $test['data'][0]['metafields']['edges'];
            // foreach ($productMetafields as $metafield) {
            //     if($metafield["node"]["key"] == "product_specs") {
            //         $product_specs = $metafield["node"]["value"];

            //     }
            //     else if ($metafield["node"]["key"] == "ficha_tecnica"){
            //         $ficha_tecnica = $metafield["node"]["value"];
            //     }
            // }

            // $arrayMetaobjects = json_decode($product_specs);
            // $firstMetaobject = $productController->getMetaobjectData("gid://shopify/Metaobject/43660181561");
            // $testttt = json_decode($firstMetaobject["spec_description"]);
            // //dd($arrayMetaobjects);
            // dd($testttt->children[0]->children[0]->value);

            // array:5 [ // app/Http/Controllers/ApiController.php:95
            //     0 => "gid://shopify/Metaobject/43644649529"
            //     1 => "gid://shopify/Metaobject/43659788345"
            //     2 => "gid://shopify/Metaobject/43659460665"
            //     3 => "gid://shopify/Metaobject/43644485689"
            //     4 => "gid://shopify/Metaobject/43660181561"
            //   ]



            // Creating slide
            $slidesController = new slidesController;
            Log::info("Starting template creation");

            $templateData = [
                'products' => $products,
                'date' => $date,
                'customerName' => $customerName,
                'catalogName' => $catalogName,
                'firstSlideImageURL' => $firstSlideImageURL,
                'catalogNameChanged' => $catalogNameChanged,
                'timeStamp' => $timeStamp,
                'customerId' => $customerId,
                'shopifyDate' => $shopifyDate,
                'customerMail' => $customerMail
            ];

            //$this->createTemplate($templateData);
            // aca dispachear el job

            ///


            //
            //
            //

            ////

            $presentationId = $slidesController->createTemplate($templateData);
            if ($presentationId == null) {
                throw new Exception('Cannot create presentation');
            }
            Log::info("created presentation");

            // Saving PDF into /public folder
            $savePDF = $this->savePDF($catalogNameChanged);
            Log::info("SAVED PDF");
            $statusCode = $savePDF->getStatusCode();

            if ($statusCode !== 200) {
                throw new Exception('Cannot save PDF');
            }

            Log::info("saved pdf on sever");

            // // Deleting the template in Drive
            $deleteMsg = $this->deleteFile($presentationId);
            Log::info($deleteMsg);

            // Saving the PDF to Shopify
            $fileURL = 'https://daniel-orozco-api.panorama.works/public/storage/' . $catalogNameChanged . '.pdf';
            Log::info("got the file url from server");
            $type = 'FILE';
            $pdfGID = $productController->uploadPDFToShopify($fileURL, $type);
            $statusCode = $pdfGID->getStatusCode();
            if ($statusCode == 200) {
                $pdfGID = $pdfGID->getData(true);
            } else {
                throw new Exception('Cannot get pdfGID');
            }
            Log::info("uploaded pdf to shopify and get GID");
            //echo 'PDF GID: ' . $pdfGID;
            $type = 'IMAGE';
            $imageGID = $productController->uploadPDFToShopify($firstSlideImageURL, $type);
            $statusCode = $imageGID->getStatusCode();

            if ($statusCode == 200) {
                $imageGID = $imageGID->getData(true);
            } else {
                throw new Exception('Cannot get imageGID');
            }
            Log::info("uploaded image to shopify and get GID");

            $catalogMetaobject = $productController->createCatalog($pdfGID, $imageGID, $timeStamp, $customerId, $catalogName, $shopifyDate, $customerMail, $customerName, $catalogNameChanged);
            // $statusCode = $catalogMetaobject->getStatusCode();

            if (!$catalogMetaobject['status']) {
                throw new Exception($catalogMetaobject['message']);
            }
            //echo 'Catalog ID: ' . $catalogMetaobject;
            Log::info("created catalog metaobject and get GID " . $catalogMetaobject['metaobject_id']);
            Log::info("get Client metafields");
            $catalogIds = $productController->getCustomerMetafields($customerGID);
            if (!$catalogIds['status']) {
                throw new Exception($catalogIds['message']);
            }
            Log::info("push metaobject id to client catalogs ids");
            $catalogIds['catalog_ids'][] = $catalogMetaobject['metaobject_id'];
            Log::info(json_encode($catalogIds, JSON_UNESCAPED_SLASHES));
            $updateMetafield = $productController->AddCustomerMetafield($customerGID, $catalogIds['catalog_ids']);
            // Sending Email to the Client
            if (!$updateMetafield['status']) {
                throw new Exception($updateMetafield['message']);
            }
            $this->sendCreatedEmail($customerMail, $catalogName, $date);
            Log::info("Email send to client " . $customerMail);
            $response = [
                'status' => true,
                'message' => 'Template sucesfully created'
            ];
            $code = 200;
        } catch (\Exception $e) {
            Log::error("catalog cant created: " . $e->getMessage());
            $response = [
                'status' => false,
                'message' => $e->getMessage()
            ];
            $code = 400;
        } finally {
            Log::info("----End of catalog creation----");
            return response()->json($response, $code);
        }
    }

    public function startNew(Request $request)
    {

        // Setting a longer time limit because the original is not enough
        set_time_limit(6000);

        try {
            // Validating request data
            $request->validate([
                'customerMail' => 'required|string',
                'catalogName' => 'required|string',
                'catalogDate' => 'required',
                'customerName' => 'required|string',
                'firstSlideImageURL' => 'required|url',
                'productsIds' => 'required|array',
            ]);
            Log::info("----New catalog init----");
            // Saving that information in vars
            $fechaActual = Carbon::now();
            $timeStamp = $fechaActual->format('YmdHis');
            $shopifyDate = $request->input('catalogDate');
            $date = $fechaActual->format('d/m/Y');
            $response = null;
            $code = null;
            $customerName = $request->input('customerName');
            $customerMail = $request->input('customerMail');

            $catalogName = $request->input('catalogName');
            $catalogNameChanged = str_replace(' ', '_', $catalogName);

            $productos = $request->input('productsIds');

            $firstSlideImageURL = $request->input('firstSlideImageURL');
            Log::info("assing values");
            $customerController = new customerController();
            $customerGID = $customerController->getCustomerData($customerMail);


            $statusCode = $customerGID->getStatusCode();

            if ($statusCode == 200) {
                $responseData = $customerGID->getData(true);
                $customerGID = $responseData['id'];
            } else {
                throw new Exception('Cannot get customerGID');
            }
            $parts = explode('/', $customerGID);
            $customerId = end($parts);
            $internalName = $customerId . '_catalog_' . $timeStamp;
            Log::info("get customer id" . $customerId);

            $productController = new productController;
            $products = $productController->getProductsByIdsREST($productos);
            Log::info("get product info");

            // Creating slide
            $slidesController = new slidesController;
            Log::info("Starting template creation");

            $templateData = [
                'products' => $products,
                'date' => $date,
                'customerName' => $customerName,
                'catalogName' => $catalogName,
                'firstSlideImageURL' => $firstSlideImageURL,
                'catalogNameChanged' => $catalogNameChanged,
                'timeStamp' => $timeStamp,
                'customerId' => $customerId,
                'shopifyDate' => $shopifyDate,
                'customerMail' => $customerMail,
                'customerGID' => $customerGID
            ];

            catalogCreatorJob::dispatch($templateData);

            //$this->createTemplate($templateData);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    function createTemplate($templateData)
    {
        $slidesController = new slidesController();
        $productController = new productController();

        $presentationId = $slidesController->createTemplate($templateData);
        if ($presentationId == null) {
            throw new Exception('Cannot create presentation');
        }
        Log::info("created presentation");

        // Saving PDF into /public folder
        $savePDF = $this->savePDF($templateData['catalogNameChanged']);
        Log::info("SAVED PDF");
        $statusCode = $savePDF->getStatusCode();

        if ($statusCode !== 200) {
            throw new Exception('Cannot save PDF');
        }

        Log::info("saved pdf on sever");

        // // Deleting the template in Drive
        $deleteMsg = $this->deleteFile($presentationId);
        Log::info($deleteMsg);

        // Saving the PDF to Shopify
        $fileURL = 'https://daniel-orozco-api.panorama.works/public/storage/' . $templateData['catalogNameChanged'] . '.pdf';
        Log::info("got the file url from server");
        $type = 'FILE';
        $pdfGID = $productController->uploadPDFToShopify($fileURL, $type);
        $statusCode = $pdfGID->getStatusCode();
        if ($statusCode == 200) {
            $pdfGID = $pdfGID->getData(true);
        } else {
            throw new Exception('Cannot get pdfGID');
        }
        Log::info("uploaded pdf to shopify and get GID");
        //echo 'PDF GID: ' . $pdfGID;
        $type = 'IMAGE';
        $imageGID = $productController->uploadPDFToShopify($templateData['firstSlideImageURL'], $type);
        $statusCode = $imageGID->getStatusCode();

        if ($statusCode == 200) {
            $imageGID = $imageGID->getData(true);
        } else {
            throw new Exception('Cannot get imageGID');
        }
        Log::info("uploaded image to shopify and get GID");

        $catalogMetaobject = $productController->createCatalog($pdfGID, $imageGID, $templateData['timeStamp'], $templateData['customerId'], $templateData['catalogName'], $templateData['shopifyDate'], $templateData['customerMail'], $templateData['customerName'], $templateData['catalogNameChanged']);
        // $statusCode = $catalogMetaobject->getStatusCode();

        if (!$catalogMetaobject['status']) {
            throw new Exception($catalogMetaobject['message']);
        }
        //echo 'Catalog ID: ' . $catalogMetaobject;
        Log::info("created catalog metaobject and get GID " . $catalogMetaobject['metaobject_id']);
        Log::info("get Client metafields");
        $catalogIds = $productController->getCustomerMetafields($templateData['customerGID']);
        if (!$catalogIds['status']) {
            throw new Exception($catalogIds['message']);
        }
        Log::info("push metaobject id to client catalogs ids");
        $catalogIds['catalog_ids'][] = $catalogMetaobject['metaobject_id'];
        Log::info(json_encode($catalogIds, JSON_UNESCAPED_SLASHES));
        $updateMetafield = $productController->AddCustomerMetafield($templateData['customerGID'], $catalogIds['catalog_ids']);
        // Sending Email to the Client
        if (!$updateMetafield['status']) {
            throw new Exception($updateMetafield['message']);
        }
        $this->sendCreatedEmail($templateData['customerMail'], $templateData['catalogName'], $templateData['date']);
        Log::info("Email send to client " . $templateData['customerMail']);
    }

    function testShopify()
    {
        $customerMail = 'gaston@panorama.works';
        $catalogName = 'PruebaReal';
        $customerController = new customerController();
        $customerId = $customerController->getCustomerData($customerMail);
        $fileUrl = 'https://daniel-orozco-api.panorama.works/public/storage/' . $catalogName . '.pdf';

        $customerController->savePdfToMetafields($customerId, $fileUrl);
    }

    function scan()
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->SetScopes(['https://www.googleapis.com/auth/drive.file']);
        $driveService = new Drive($client);
        $client->addScope(Slides::PRESENTATIONS);
        $service = new Slides($client);

        $credentialsPath = __DIR__ . '/no-borrar-catalog-creator-a5c2ee4022cf.json';
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);

        $slidesController = new slidesController();
        $presentationId = $slidesController->duplicateTemplate($client, $driveService);
        if ($presentationId == null) {
            return null;
        }

        $response = $service->presentations->get($presentationId);

        // Verifica si la slide solicitada existe
        $slides = $response->getSlides();
        $slideNumber = count($slides) - 1;

        for ($i = 0; $i <= $slideNumber; $i++) {
            echo 'Slide number: ' . ($i + 1) . '<br>';
            var_dump($slidesController->getObjectInfo($presentationId, $i));
        }
    }

    public function getProducts(Request $request)
    {
        $request->validate(['productsIds' => 'required|array']);

        $productController = new productController;
        $productos = $request->input('productsIds');
        $products = $productController->getProductsByIdsREST($productos);

        return $products;
    }



    function savePDF($catalogName)
    {
        try {
            $client = new Client();
            $client->useApplicationDefaultCredentials();
            $client->addScope(Drive::DRIVE);
            $driveService = new Drive($client);
            Log::info("Loaded client services");

            $resultado = $driveService->files->listFiles([
                'q' => "name='" . $catalogName . "'",
                'fields' => 'files(id, size)'
            ]);
            Log::info("Catalog Name: " . $catalogName);

            $num = count($resultado);

            Log::info("Resultado: " . $num);

            if ($num == 0) {
                echo 'File not found';
                die;
            }

            $fileId = $resultado[0]->id;

            Log::info("File ID: " . $fileId);

            $response = $driveService->files->export($fileId, 'application/pdf', array(
                'alt' => 'media'
            ));
            $content = $response->getBody()->getContents();

            $path = "storage/" . $catalogName . ".pdf";

            Log::info("Path: " . $path);

            $route = public_path($path);

            Log::info("Route: " . $route);

            $data = file_put_contents($route, $content);
            Log::info("File put contents: " . $data);
            //Storage::disk('public')->put('example.txt', 'archive');
            //$fileUrl = Storage::disk('public')->url('example.txt');
            $rutaReal = realpath($route);

            //echo 'The file with ID: ' . $fileId . '.<br>Has been downloaded successfully in public\archives.';
            //echo 'Ruta real: ' . $rutaReal;
            //echo 'Route: ' . $route;
            $code = 200;
            $response = 'PDF Saved';
        } catch (\Exception $e) {
            $code = 500;
            $response = [
                "status" => false,
                "error" => $e->getMessage()
            ];
        } finally {
            return response()->json($response, $code);
        }
        //Storage::disk('public')->put('example.txt', 'archive');
    }

    function deleteFile($fileId)
    {
        // Configurar el cliente de Google
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(Drive::DRIVE);
        $driveService = new Drive($client);

        // Crear el servicio de Google Drive
        $driveService = new Drive($client);

        try {
            $driveService->files->delete($fileId);
            return response()->json(['message' => 'Template deleted from DRIVE']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    public function borrarArchivo()
    {
        // URL del archivo en Bluehost
        $urlArchivo = 'https://daniel-orozco-api.panorama.works/public/storage/Totems.pdf';

        // Inicializar el cliente Guzzle
        $cliente = new GuzzleClient();

        try {
            // Realizar una solicitud DELETE al servidor de Bluehost
            $respuesta = $cliente->request('DELETE', $urlArchivo);

            // Verificar si la solicitud fue exitosa (código 200)
            if ($respuesta->getStatusCode() == 200) {
                return response()->json(['mensaje' => 'Archivo borrado exitosamente']);
            } else {
                return response()->json(['mensaje' => 'Error al intentar borrar el archivo']);
            }
        } catch (\Exception $e) {
            // Capturar excepciones en caso de error
            return response()->json(['mensaje' => 'Error al intentar borrar el archivo: ' . $e->getMessage()]);
        }
    }

    public function prueba() {
        $data = []; // Puedes pasar datos adicionales a tu vista si es necesario
        $dompdf = new DompdfDompdf();
        $options = $dompdf->getOptions();
            $options->setFontCache(storage_path('fonts'));
            $options->set('isRemoteEnabled', true);
            $options->set('pdfBackend', 'CPDF');
            $options->setChroot([
                'resources/views/',
                storage_path('fonts'),
            ]);

        $pdf = PDF::loadView('catalog2', $data);
        return $pdf;

        // Guardar el PDF en el almacenamiento temporal
        $tempFilePath = 'temp/' . uniqid() . '.pdf';
        Storage::put($tempFilePath, $pdf->output());

        return $tempFilePath; // Retorna la ruta del archivo temporal
    }
}
