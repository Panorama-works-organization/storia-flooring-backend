<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Service\Slides\Request as SlidesRequest;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Slides;
use Google\Service\Drive\DriveFile;
use Google\Service\DriveActivity\Create;
use Google\Service\Slides\BatchUpdatePresentationRequest;
use Google\Service\Slides\Presentation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\shopify\catalogController;
use App\Http\Controllers\shopify\productController;

use function PHPUnit\Framework\isEmpty;
use App\Mail\templateCreatedMail;
use App\Mail\editorRequestMail;
use Exception;
use Google\Service\Slides\TextContent;
use Google\Service\Slides\TextElement;
use Illuminate\Validation\Rules\Exists;

class slidesController extends Controller
{

    public function createTemplate($templateData)
    {
        try {
            // Setting google credentials
            $credentialsPath = __DIR__ . '/no-borrar-catalog-creator-a5c2ee4022cf.json';
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);
            Log::info("Setted credentials");


            // Configuring google client
            $client = new Client();
            $client->useApplicationDefaultCredentials();
            $client->SetScopes(['https://www.googleapis.com/auth/drive.file']);
            $client->addScope(Slides::PRESENTATIONS);



            // Duplicating original template
            $presentationId = $this->duplicateTemplate($templateData['catalogNameChanged']);
            if ($presentationId == null) {
                return null;
            }
            Log::info("Template has been duplicated");

            // Creating each slide
            $this->fillFirstSlide($presentationId, $templateData);
            Log::info("Filled first slide");
            sleep(5);
            Log::info("Sleeped 5 seconds");
            Log::info("Starting Second Slide");
            $this->fillNewSecondSlideVariantsOnePerVariant($presentationId, $templateData['products']);
            Log::info("Filled second slide");


            $pageNumber = 1;
            $slideId = $this->getPageId($presentationId, $pageNumber);
            $this->deleteSlide($presentationId, $slideId);
            Log::info("Deleted slide");
            //$slideId = $this->getPageId($presentationId, $pageNumber);
            //$this->deleteSlide($presentationId, $slideId);

            return $presentationId;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function fillFirstSlide($presentationId, $templateData)
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->SetScopes(['https://www.googleapis.com/auth/drive.file']);
        $driveService = new Drive($client);
        $client->addScope(Slides::PRESENTATIONS);

        $slideNumber = 0;
        $pageNumber = 0;
        $objectInfo = $this->getObjectInfo($presentationId, $slideNumber);


        $data = [
            'Image' => $templateData['firstSlideImageURL'],
            'Client' => $templateData['customerName'],
            'Date' => $templateData['date'],
            'Title' => $templateData['catalogName'],
        ];

        $y = 0;
        foreach ($data as $label => $value) {
            Log::info("Starting foreach");
            $element = $objectInfo[$y];
            $elementId = $element['objectId'];
            if ($label == 'Image') {
                Log::info("Lavel Image");
                $arregloImagenes = [
                    'objectId' => $elementId,
                    'image' => $value
                ];
                $this->replacePlaceholderForImage($presentationId, $arregloImagenes);
            } else {
                Log::info("Lavel Texto");
                $arregloTextos = [
                    'objectId' => $elementId,
                    'text' => $value
                ];
                $this->insertTextInEmptyTextField($presentationId, $arregloTextos, true);
            }
            $y++;
            Log::info("Finishing foreach");
        }
    }

    public function fillNewSecondSlideVariants($presentationId, $products)
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->SetScopes(['https://www.googleapis.com/auth/drive.file']);
        $client->addScope(Slides::PRESENTATIONS);

        $duplicates = count($products);
        $numeroSlide = 2;

        for ($i = 0; $i < $duplicates; $i++) {
            if ($i < $duplicates) {
                $secondSlidePosition = 1;
                $secondSlideId = $this->getPageId($presentationId, $secondSlidePosition);

                $numeroSlide = 2;

                $cantidadVariantes = count($products[$i]->variants);

                for ($x = 1; $x <= $cantidadVariantes; $x++) {
                    $this->duplicateSlide($presentationId, $secondSlideId);
                    if ($i == 0) {
                        $arrayTotal = $this->fillSlide($presentationId, $products, $i, $numeroSlide, $x - 1);
                        $arrayTotalTextos = $arrayTotal['arrayTextos'];
                        $arrayTotalImagenes = $arrayTotal['arrayImagenes'];
                    } else {
                        $arrayTotal = $this->fillSlide($presentationId, $products, $i, $numeroSlide, $x - 1);
                        foreach ($arrayTotal['arrayTextos'] as $texto) {
                            $arrayTotalTextos[] = $texto;
                        }
                        foreach ($arrayTotal['arrayImagenes'] as $imagen) {
                            $arrayTotalImagenes[] = $imagen;
                        }
                    }
                }
            }
        }

        try {
            sleep(5);
            $this->insertTextInEmptyTextField($presentationId, $arrayTotalTextos, false);
            sleep(5);
            $this->replacePlaceholderForImage($presentationId, $arrayTotalImagenes);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function fillNewSecondSlideVariantsOnePerVariant($presentationId, $products)
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->SetScopes(['https://www.googleapis.com/auth/drive.file']);
        $client->addScope(Slides::PRESENTATIONS);


        Log::info("Starting slide 2 function");

        $duplicates = count($products);
        $numeroSlide = 2;

        for ($i = 0; $i < $duplicates; $i++) {

            if ($i < $duplicates) {
                $secondSlidePosition = 1;
                $secondSlideId = $this->getPageId($presentationId, $secondSlidePosition);

                $numeroSlide = 2;

                $cantidadVariantes = count($products[$i]->variants);

                for ($x = 1; $x <= $cantidadVariantes; $x++) {
                    sleep(5);
                    Log::info("Duplicating slide afte 5seconds of sleep");
                    $this->duplicateSlide($presentationId, $secondSlideId);
                    Log::info("Slide duplicated, sleeping 5sec");
                    sleep(5);
                    $this->fillSlideOnePerVariant($presentationId, $products, $i, $numeroSlide, $x - 1);
                    Log::info("Slide filled, sleeping 5 seconds");
                }
            }
        }
    }

    public function fillNewSecondSlide($presentationId, $products)
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->SetScopes(['https://www.googleapis.com/auth/drive.file']);
        $client->addScope(Slides::PRESENTATIONS);



        // Ordenar el array por la clave 'name' en orden descendente
        usort($products, function ($a, $b) {
            return strcmp($b['nombreProducto'], $a['nombreProducto']);
        });

        $duplicates = count($products);
        $numeroSlide = 2;

        for ($i = 0; $i < $duplicates; $i++) {
            if ($i < $duplicates) {
                $secondSlidePosition = 1;
                $thirdSlidePosition = 2;
                $secondSlideId = $this->getPageId($presentationId, $secondSlidePosition);
                $thirdSlideId = $this->getPageId($presentationId, $thirdSlidePosition);

                $numeroSlide = 2;
                $this->duplicateSlide($presentationId, $secondSlideId);
                //$this->fillSlide($presentationId, $products, $i, $numeroSlide);
                $cuenta = count($products[$i]['especificacionesProducto']);
                $paginas = ceil($cuenta / 3);
                //echo 'NECESITAS: ' . $paginas;
                if ($paginas >= 1) {
                    $vuelta = 1;
                    $numeroSlide = $numeroSlide + 2;
                    for ($y = 1; $y <= $paginas; $y++) {
                        $this->duplicateSlide($presentationId, $thirdSlideId);
                        $this->fillOtherSlide($presentationId, $products, $i, $numeroSlide, $vuelta, $paginas, $cuenta);
                        $vuelta++;
                    }
                }
                //$this->updateSlidePosition($presentationId, $secondSlideId, 1);
                $this->updateSlidePosition($presentationId, $thirdSlideId, 2);
            }
        }
    }

    public function fillSlide($presentationId, $products, $i, $numeroSlide, $x)
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->SetScopes(['https://www.googleapis.com/auth/drive.file']);
        $client->addScope(Slides::PRESENTATIONS);
        $productController = new productController;

        $objectInfo = $this->getObjectInfo($presentationId, $numeroSlide);

        // Iterar sobre las claves de $producto
        $y = 0;
        $arrayTextos = [];
        $arrayImagenes = [];
        //foreach (['nombreProducto', 'imagenPrincipal', 'descripcion', 'sku', 'imagenPrincipal', 'precioProducto'] as $key) {
        foreach (['descripcion', 'imagenPrincipal', 'sku', 'nombreProducto', 'imagenPlano', 'precioProducto', 'boton'] as $key) {
            $variantId = $products[$i]->variants[$x]->id;
            $variantArray = [];
            $variantArray[] = $variantId;
            $vari = $productController->getVariantsMetafields($variantArray);
            try {
                $varImgSrc = $vari['data'][0]['metafields']['edges'][0]['node']['value'];
                $imageSrc = $productController->getImageSrcByMediaImageId($varImgSrc);
                $imageSrc = $imageSrc['src']['data']['node']['image']['url'];
                $existeMetafield = true;
            } catch (\Throwable $th) {
                $existeMetafield = false;
            }
            $element = $objectInfo[$y];
            $elementId = $element['objectId'];
            // Utilizar la clave actual para acceder al valor en $producto
            if ($key === 'imagenPrincipal') {
                foreach ($products[$i]->images as $imagen) {
                    if (isset($imagen->variant_ids[0]) && $imagen->variant_ids[0] == $products[$i]->variants[$x]->id) {
                        $image = $imagen->src;
                    } else {
                        $image = $products[$i]->images[$x]->src;
                    }
                    $arrayImagenes[] = [
                        'objectId' => $elementId,
                        'image' => $image
                    ];
                    //$this->replacePlaceholderForImage($presentationId, $elementId, $image);
                }
            } else if ($key === "imagenPlano" && $existeMetafield) {
                $arrayImagenes[] = [
                    'objectId' => $elementId,
                    'image' => $imageSrc
                ];
                //$this->replacePlaceholderForImage($presentationId, $elementId, $imageSrc);
            } else if ($key === "nombreProducto") {
                $arrayTextos[] = [
                    'objectId' => $elementId,
                    'text' => $products[$i]->variants[$x]->title
                ];
                //$this->insertTextInEmptyTextField($presentationId, $elementId, $products[$i]->variants[$x]->title);
            } else if ($key === "descripcion") {
                $this->insertRichTextInEmptyTextField($presentationId, $elementId, $products[$i]->body_html);
            } else if ($key === "precioProducto") {
                $precioFormateado = number_format(floatval($products[$i]->variants[$x]->price), 2, '.', ',');
                $precioProducto = '$' . $precioFormateado;
                $arrayTextos[] = [
                    'objectId' => $elementId,
                    'text' => $precioProducto
                ];
                //$this->insertTextInEmptyTextField($presentationId, $elementId, $precioProducto);
            } else if ($key === "sku") {
                $arrayTextos[] = [
                    'objectId' => $elementId,
                    'text' => $products[$i]->variants[$x]->sku
                ];
                //$this->insertTextInEmptyTextField($presentationId, $elementId, $products[$i]->variants[$x]->sku);
            } else if ($key === "boton") {
                $producHandle = $products[$i]->handle;
                $baseUrl = 'https://daniel-orozco-2023.myshopify.com/products/';
                $completeUrl = $baseUrl . $producHandle;
                $this->insertTextWithLinkInEmptyTextField($presentationId, $elementId, 'VIEW MORE', $completeUrl);
            }
            $y++;
        }
        return [
            'arrayTextos' => $arrayTextos,
            'arrayImagenes' => $arrayImagenes
        ];
    }

    public function fillSlideOnePerVariant($presentationId, $products, $i, $numeroSlide, $x)
    {
        try {
            $client = new Client();
            $client->useApplicationDefaultCredentials();
            $client->SetScopes(['https://www.googleapis.com/auth/drive.file']);
            $client->addScope(Slides::PRESENTATIONS);
            $productController = new productController;

            $objectInfo = $this->getObjectInfo($presentationId, $numeroSlide);

            // Iterar sobre las claves de $producto
            $y = 0;
            //foreach (['nombreProducto', 'imagenPrincipal', 'descripcion', 'sku', 'imagenPrincipal', 'precioProducto'] as $key) {
            foreach (['descripcion', 'imagenPrincipal', 'sku', 'nombreProducto', 'imagenPlano', 'precioProducto', 'boton'] as $key) {
                $variantId = $products[$i]->variants[$x]->id;
                $variantArray = [];
                $variantArray[] = $variantId;
                $vari = $productController->getVariantsMetafields($variantArray);
                try {
                    $varImgSrc = $vari['data'][0]['metafields']['edges'][0]['node']['value'];
                    $imageSrc = $productController->getImageSrcByMediaImageId($varImgSrc);
                    $imageSrc = $imageSrc['src']['data']['node']['image']['url'];
                    $existeMetafield = true;
                } catch (\Throwable $th) {
                    $existeMetafield = false;
                }
                $element = $objectInfo[$y];
                $elementId = $element['objectId'];
                // Utilizar la clave actual para acceder al valor en $producto
                if ($key === 'imagenPrincipal') {
                    foreach ($products[$i]->images as $imagen) {
                        if (isset($imagen->variant_ids[0]) && $imagen->variant_ids[0] == $products[$i]->variants[$x]->id) {
                            $image = $imagen->src;
                        } else {
                            $image = $products[$i]->images[$x]->src;
                        }
                        $arrayImagenes = [
                            'objectId' => $elementId,
                            'image' => $image
                        ];
                        $this->replacePlaceholderForImage($presentationId, $arrayImagenes);
                    }
                } else if ($key === "imagenPlano" && $existeMetafield) {
                    $arrayImagenes = [
                        'objectId' => $elementId,
                        'image' => $imageSrc
                    ];
                    $this->replacePlaceholderForImage($presentationId, $arrayImagenes);
                } else if ($key === "nombreProducto") {
                    $arrayTextos = [
                        'objectId' => $elementId,
                        'text' => $products[$i]->variants[$x]->title
                    ];
                    $this->insertTextInEmptyTextField($presentationId, $arrayTextos, false);
                } else if ($key === "descripcion") {
                    $this->insertRichTextInEmptyTextField($presentationId, $elementId, $products[$i]->body_html);
                } else if ($key === "precioProducto") {
                    $precioFormateado = number_format(floatval($products[$i]->variants[$x]->price), 2, '.', ',');
                    $precioProducto = '$' . $precioFormateado;
                    $arrayTextos = [
                        'objectId' => $elementId,
                        'text' => $precioProducto
                    ];
                    $this->insertTextInEmptyTextField($presentationId, $arrayTextos, false);
                } else if ($key === "sku") {
                    $arrayTextos = [
                        'objectId' => $elementId,
                        'text' => $products[$i]->variants[$x]->sku
                    ];
                    $this->insertTextInEmptyTextField($presentationId, $arrayTextos, false);
                } else if ($key === "boton") {
                    $producHandle = $products[$i]->handle;
                    $baseUrl = 'https://daniel-orozco-2023.myshopify.com/products/';
                    $completeUrl = $baseUrl . $producHandle;
                    $this->insertTextWithLinkInEmptyTextField($presentationId, $elementId, 'VIEW MORE', $completeUrl);
                }
                $y++;
                sleep(5);
            }
        } catch (\Exception $e) {
            Throw new Exception($e->getMessage());
        }
    }

    public function fillOtherSlide($presentationId, $products, $i, $numeroSlide, $vuelta, $paginas, $cantidadItems)
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->SetScopes(['https://www.googleapis.com/auth/drive.file']);
        $client->addScope(Slides::PRESENTATIONS);


        $objectInfo = $this->getObjectInfo($presentationId, $numeroSlide);

        $sobrantes = $cantidadItems % 3;

        if ($vuelta == 1) {
            $inicio = $cantidadItems;
        } else if ($vuelta == 2) {
            if ($sobrantes == 0) {
                $inicio = $cantidadItems - 3;
            } else {
                $inicio = $cantidadItems - $sobrantes;
            }
        } else {
            if ($sobrantes == 0) {
                $inicio = $cantidadItems - 3;
            } else {
                $inicio = $cantidadItems - $sobrantes;
            }
            $multiplicador = $vuelta - 2;
            $inicio = $inicio - ($multiplicador * 3);
        }

        //echo "\n" . 'Inicio = ' . $inicio . "\n";
        //echo 'Numero de vuelta = ' . $vuelta . "\n";
        //echo 'Sobrantes = ' . $sobrantes . "\n";
        //echo 'Cantidad de Items = ' . $cantidadItems . "\n";


        $y = 0;
        foreach (['primero', 'quinto', 'tercero', 'segundo', 'cuarto', 'septimo', 'sexto'] as $key) {
            $element = $objectInfo[$y];
            $elementId = $element['objectId'];
            if ($vuelta == 1) {
                if ($sobrantes == 2) {
                    if ($key === 'primero') {
                        $concatenacion = $products[$i]['nombreProducto'] . ': Product Specs';
                        $this->insertTextInEmptyTextField($presentationId, $elementId, $concatenacion);
                    }
                    if ($key === 'segundo') {
                        $this->insertTextInEmptyTextField($presentationId, $elementId, $products[$i]['especificacionesProducto'][$inicio - 2]['titulo']);
                    } else if ($key === 'tercero') {
                        $this->insertTextInEmptyTextField($presentationId, $elementId, $products[$i]['especificacionesProducto'][$inicio - 2]['descripcion']);
                    }
                    if ($sobrantes == 2) {
                        if ($key === 'cuarto') {
                            $this->insertTextInEmptyTextField($presentationId, $elementId, $products[$i]['especificacionesProducto'][$inicio - 1]['titulo']);
                        } else if ($key === 'quinto') {
                            $this->insertTextInEmptyTextField($presentationId, $elementId, $products[$i]['especificacionesProducto'][$inicio - 1]['descripcion']);
                        }
                    }
                } else if ($sobrantes == 1) {
                    if ($key === 'primero') {
                        $concatenacion = $products[$i]['nombreProducto'] . ': Product Specs';
                        $this->insertTextInEmptyTextField($presentationId, $elementId, $concatenacion);
                    }
                    if ($key === 'segundo') {
                        $this->insertTextInEmptyTextField($presentationId, $elementId, $products[$i]['especificacionesProducto'][$inicio - 1]['titulo']);
                    } else if ($key === 'tercero') {
                        $this->insertTextInEmptyTextField($presentationId, $elementId, $products[$i]['especificacionesProducto'][$inicio - 1]['descripcion']);
                    }
                }
            }

            if (($vuelta == 1 && $sobrantes == 0) || $vuelta > 1) {
                if ($key === 'primero') {
                    $concatenacion = $products[$i]['nombreProducto'] . ': Product Specs';
                    $this->insertTextInEmptyTextField($presentationId, $elementId, $concatenacion);
                } else if ($key === 'segundo') {
                    $this->insertTextInEmptyTextField($presentationId, $elementId, $products[$i]['especificacionesProducto'][$inicio - 3]['titulo']);
                } else if ($key === 'tercero') {
                    $this->insertTextInEmptyTextField($presentationId, $elementId, $products[$i]['especificacionesProducto'][$inicio - 3]['descripcion']);
                } else if ($key === 'cuarto') {
                    if (isset($products[$i]['especificacionesProducto'][$inicio - 2]['titulo'])) {
                        $this->insertTextInEmptyTextField($presentationId, $elementId, $products[$i]['especificacionesProducto'][$inicio - 2]['titulo']);
                    }
                } else if ($key === 'quinto') {
                    $this->insertTextInEmptyTextField($presentationId, $elementId, $products[$i]['especificacionesProducto'][$inicio - 2]['descripcion']);
                } else if ($key === 'sexto') {
                    if (isset($products[$i]['especificacionesProducto'][$inicio - 3]['titulo'])) {
                        $this->insertTextInEmptyTextField($presentationId, $elementId, $products[$i]['especificacionesProducto'][$inicio - 1]['titulo']);
                    }
                } else if ($key === 'septimo') {
                    if (isset($products[$i]['especificacionesProducto'][$inicio - 3]['descripcion'])) {
                        $this->insertTextInEmptyTextField($presentationId, $elementId, $products[$i]['especificacionesProducto'][$inicio - 1]['descripcion']);
                    }
                }
            }
            //$this->insertTextInEmptyTextField($presentationId, $elementId, $key);
            $y++;
        }
    }

    function updateSlidePosition($presentationId, $slideId, $newIndex)
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(Slides::PRESENTATIONS);
        $service = new Slides($client);

        // Construir la solicitud para actualizar la posición de la diapositiva
        $requests = [
            new SlidesRequest([
                'updateSlidesPosition' => [
                    'slideObjectIds' => [$slideId],
                    'insertionIndex' => $newIndex,
                ],
            ]),
        ];

        // Crear la solicitud de actualización en lote
        $batchUpdateRequest = new BatchUpdatePresentationRequest([
            'requests' => $requests,
        ]);

        // Enviar la solicitud al servicio de presentaciones
        $service->presentations->batchUpdate($presentationId, $batchUpdateRequest);
    }

    function deleteSlide($presentationId, $elementId)
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(Slides::PRESENTATIONS);
        $service = new Slides($client);


        // Making the request to delete the element
        $requests = array();
        $requests[] = new SlidesRequest(array(
            'deleteObject' => array(
                'objectId' => $elementId,
            )
        ));

        // Making the request to make the changes
        $batchUpdateRequest = new BatchUpdatePresentationRequest(array(
            'requests' => $requests
        ));

        $response = $service->presentations->batchUpdate($presentationId, $batchUpdateRequest);

        //echo "Element deleted.";
    }

    public function duplicateTemplate($fileName)
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(Drive::DRIVE);
        $driveService = new Drive($client);
        $client->addScope(Slides::PRESENTATIONS);


        // Find the original template by his name
        $originalTemplate = $driveService->files->listFiles([
            'q' => "name='NOELIMINARlllTEMPLATE'",
            'fields' => 'files(id, size)'
        ]);

        $num = count($originalTemplate);

        if ($num == 0) {
            echo 'Original template not found';
            die;
        }

        // Get original template ID
        $originalTemplateId = $originalTemplate[0]->id;

        $driveService = new Drive($client);

        // Get original template info
        $originalTemplateInfo = $driveService->files->get($originalTemplateId);

        // Duplicate the original template
        try {
            $duplicate = new DriveFile();
            $duplicate->setName($fileName);
            $duplicatedTemplate = $driveService->files->copy($originalTemplateId, $duplicate);
            $duplicatedTemplateId = $duplicatedTemplate->getId();

            //echo "Template duplicated. <br>New ID: $duplicatedTemplateId <br><br><br>";
            return $duplicatedTemplateId;
        } catch (\Throwable $th) {
            echo "The duplication of the Template has failed.";
            return null;
        }
    }

    function getObjectInfo($newSlideId, $slideNumber)
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(Slides::PRESENTATIONS);
        $service = new Slides($client);

        $response = $service->presentations->get($newSlideId);

        // Verifica si la slide solicitada existe
        $slides = $response->getSlides();
        if (!isset($slides[$slideNumber])) {
            return "La slide número $slideNumber no existe en esta presentación.";
        }

        $slide = $slides[$slideNumber];
        $objectsInfo = [];
        $objectNumber = 1;

        // Getting the info of the objects on the specified slide
        foreach ($slide->getPageElements() as $element) {
            $objectId = $element->getObjectId();
            $objectType = $this->getObjectType($element);

            $details = json_decode(json_encode($element), true);
            $transform = $details['transform'];
            $positionX = $this->emuToCentimetersFromCenter($transform['translateX']);
            $positionY = $this->emuToCentimetersFromCenter($transform['translateY']);

            $info = [
                'objectId' => $objectId,
                'objectType' => $objectType,
                'positionX' => $positionX,
                'positionY' => $positionY,
                // Puedes agregar más información según sea necesario
            ];

            $objectsInfo[] = $info;

            $objectNumber++;
        }

        return $objectsInfo;
    }

    function getObjectInfoBySlideId($presentationId, $slideId)
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(Slides::PRESENTATIONS);
        $service = new Slides($client);

        $response = $service->presentations->get($presentationId);

        // Verifica si la slide solicitada existe
        $slides = $response->getSlides();
        $foundSlide = null;

        foreach ($slides as $slide) {
            if ($slide->getObjectId() === $slideId) {
                $foundSlide = $slide;
                break;
            }
        }

        if (!$foundSlide) {
            return "La slide con el ID $slideId no existe en esta presentación.";
        }

        $objectsInfo = [];
        $objectNumber = 1;

        // Obtener información de los objetos en la diapositiva encontrada
        foreach ($foundSlide->getPageElements() as $element) {
            $objectId = $element->getObjectId();
            $objectType = $this->getObjectType($element);

            $details = json_decode(json_encode($element), true);
            $transform = $details['transform'];
            $positionX = $this->emuToCentimetersFromCenter($transform['translateX']);
            $positionY = $this->emuToCentimetersFromCenter($transform['translateY']);

            $info = [
                'objectId' => $objectId,
                'objectType' => $objectType,
                'positionX' => $positionX,
                'positionY' => $positionY,
                // Puedes agregar más información según sea necesario
            ];

            $objectsInfo[] = $info;

            $objectNumber++;
        }

        return $objectsInfo;
    }

    function getObjectType($element)
    {
        $details = json_decode(json_encode($element), true);
        // Verifying if the object has any asociated form
        if ($element->getShape()) {
            $shapeType = $element->getShape()->shapeType;

            // Verifying if the type is text
            if ($shapeType === 'TEXT_BOX') {
                return 'Text';
            }
        } else if (isset($details['image']['placeholder'])) {

            // Verifying if the object is a image
            return 'Image';
        }

        // If not, return 'Unknown'
        return 'Unknown';
    }

    function emuToCentimetersFromCenter($emuValue)
    {
        $emusPerCentimeter = 914400;
        $centerAdjustment = 0.5;
        $finalValue = ($emuValue / $emusPerCentimeter) + $centerAdjustment;
        $roundedValue = number_format($finalValue, 2, '.', '');

        return $roundedValue;
    }

    function insertTextInEmptyTextField($presentationId, $arreglo, $booleano)
    {
        try {
            $client = new Client();
            $client->useApplicationDefaultCredentials();
            $client->addScope(Slides::PRESENTATIONS);
            $service = new Slides($client);

            $requests = array();

            $requests[] = new SlidesRequest(array(
                'insertText' => array(
                    'objectId' => $arreglo['objectId'],
                    'text' => $arreglo['text']
                )
            ));


            // Make the request
            $batchUpdateRequest = new BatchUpdatePresentationRequest([
                'requests' => $requests,
            ]);

            $response = $service->presentations->batchUpdate($presentationId, $batchUpdateRequest);
        } catch (\Exception $e) {
            $response = $e;
        } finally {
            return $response;
        }
    }

    function insertTextWithLinkInEmptyTextField($presentationId, $objectId, $text, $linkUrl)
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(Slides::PRESENTATIONS);
        $service = new Slides($client);

        // Primero, insertar el texto directamente en el cuadro de texto
        $insertTextRequest = [
            'insertText' => [
                'objectId' => $objectId,
                'text' => $text,
            ],
        ];

        // Hacer la primera solicitud
        $batchUpdateRequest = new BatchUpdatePresentationRequest([
            'requests' => $insertTextRequest,
        ]);

        $service->presentations->batchUpdate($presentationId, $batchUpdateRequest);

        // Luego, crear un enlace en el texto
        $requests[] = new SlidesRequest(array(
            'updateTextStyle' => array(
                'objectId' => $objectId,
                'textRange' => array(
                    'type' => 'FIXED_RANGE',
                    'startIndex' => 0,
                    'endIndex' => 10
                ),
                'style' => array(
                    'link' => array(
                        'url' => $linkUrl
                    )
                ),
                'fields' => 'link'
            )
        ));

        // Execute the requests.
        $batchUpdateRequest = new BatchUpdatePresentationRequest(array(
            'requests' => $requests
        ));

        $service->presentations->batchUpdate($presentationId, $batchUpdateRequest);

        $updateColorRequest = [
            'updateTextStyle' => [
                'objectId' => $objectId,
                'textRange' => [
                    'type' => 'ALL',
                ],
                'style' => [
                    'foregroundColor' => [
                        'opaqueColor' => [
                            'rgbColor' => [
                                'red' => 1.0,
                                'green' => 1.0,
                                'blue' => 1.0,
                            ],
                        ],
                    ],
                ],
                'fields' => 'foregroundColor',
            ],
        ];

        // Hacer la primera solicitud
        $batchUpdateRequest = new BatchUpdatePresentationRequest([
            'requests' => $updateColorRequest,
        ]);

        $service->presentations->batchUpdate($presentationId, $batchUpdateRequest);

        // Quitar el subrayado del texto
        $updateUnderlineRequest = [
            'updateTextStyle' => [
                'objectId' => $objectId,
                'textRange' => [
                    'type' => 'ALL',
                ],
                'style' => [
                    'underline' => false,
                ],
                'fields' => 'underline',
            ],
        ];

        // Hacer la segunda solicitud
        $batchUpdateRequest = new BatchUpdatePresentationRequest([
            'requests' => $updateUnderlineRequest,
        ]);

        $service->presentations->batchUpdate($presentationId, $batchUpdateRequest);
    }

    function insertRichTextInEmptyTextField($presentationId, $objectId, $htmlText)
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(Slides::PRESENTATIONS);
        $service = new Slides($client);

        $textWithoutTags = preg_replace('/<[^>]+>/', '', $htmlText);

        // Reemplazar <br> con \n
        $textWithNewlines = str_replace('<br>', "\n", $textWithoutTags);

        // Insert the text directly in the textbox
        $requests = [
            'insertText' => [
                'objectId' => $objectId,
                'text' => $textWithNewlines,
            ],
        ];

        // Make the request
        $batchUpdateRequest = new BatchUpdatePresentationRequest([
            'requests' => $requests,
        ]);

        $service->presentations->batchUpdate($presentationId, $batchUpdateRequest);
    }

    function getPageId($presentationId, $pageNumber)
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(Slides::PRESENTATIONS);
        $service = new Slides($client);

        $presentation = $service->presentations->get($presentationId);

        // Verifying if the page number is inside the parameters
        if ($pageNumber < 0 || $pageNumber >= count($presentation->getSlides())) {
            die("Invalid page number");
        }

        // Getting the page id
        $pageId = $presentation->getSlides()[$pageNumber]->getObjectId();

        return $pageId;
    }

    public function getSlideIndex($slideId)
    {
        // Configuración del cliente Google
        $client = new Client();
        $client->setAuthConfig(env('GOOGLE_SLIDES_CREDENTIALS_PATH'));
        $client->addScope(Slides::PRESENTATIONS_READONLY);

        // Crear el servicio Google Slides API
        $slidesService = new Slides($client);

        // ID de la presentación
        $presentationId = env('GOOGLE_SLIDES_PRESENTATION_ID');

        // Obtener información de la presentación
        $presentation = $slidesService->presentations->get($presentationId);

        // Buscar el índice de la diapositiva a partir de su ID
        $slideIndex = null;
        foreach ($presentation->getSlides() as $i => $slide) {
            if ($slide->getObjectId() == $slideId) {
                $slideIndex = $i + 1; // Suma 1 ya que los índices comienzan desde 1 en lugar de 0
                break;
            }
        }

        if ($slideIndex !== null) {
            return $slideIndex;
        } else {
            return null;
        }
    }

    function deleteObject($presentationId, $elementId)
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(Slides::PRESENTATIONS);
        $service = new Slides($client);


        // Making the request to delete the element
        $requests = array();
        $requests[] = new SlidesRequest(array(
            'deleteObject' => array(
                'objectId' => $elementId,
            )
        ));

        // Making the request to make the changes
        $batchUpdateRequest = new BatchUpdatePresentationRequest(array(
            'requests' => $requests
        ));

        $response = $service->presentations->batchUpdate($presentationId, $batchUpdateRequest);

        //echo "Element deleted.";
    }

    function insertImage($presentationId, $slideId, $imageUrl)
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(Slides::PRESENTATIONS);
        $service = new Slides($client);

        $requests = array();
        $uuid = Str::uuid();

        $requests[] = new SlidesRequest(array(
            'createImage' => array(
                'objectId' => $uuid,
                'elementProperties' => array(
                    'pageObjectId' => $slideId,
                    'size' => array(
                        'width' => array('magnitude' => 9144000, 'unit' => 'EMU'),
                        'height' => array('magnitude' => 3366000, 'unit' => 'EMU'),
                    ),
                    'transform' => array(
                        'scaleX' => 1,
                        'scaleY' => 1,
                        'translateX' => 0,
                        'translateY' => 0,
                        'unit' => 'PT'
                    )
                ),
                'url' => $imageUrl,
            ),
        ));

        // Making the request to update this
        $batchUpdateRequest = new BatchUpdatePresentationRequest(array(
            'requests' => $requests
        ));

        $response = $service->presentations->batchUpdate($presentationId, $batchUpdateRequest);

        $createdObjectId = $response->getReplies()[0]->getCreateImage()->getObjectId();

        //echo "<br>New image ID: " . $createdObjectId . '<br>';
        $this->imageReposition($presentationId, $createdObjectId);
        return $uuid;
    }

    function imageReposition($presentationId, $objectId)
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(Slides::PRESENTATIONS);
        $service = new Slides($client);
        $requests = array();

        $translateX = -2379600; // New X position
        $translateY = 0; // New Y position

        $requests[] = new SlidesRequest(array(
            'updatePageElementTransform' => array(
                'objectId' => $objectId,
                'applyMode' => 'RELATIVE',
                'transform' => array(
                    'scaleX' => 1,
                    'scaleY' => 1,
                    'translateX' => $translateX,
                    'translateY' => $translateY,
                    'unit' => 'EMU'
                )
            )
        ));

        // Making the request
        $batchUpdateRequest = new BatchUpdatePresentationRequest(array(
            'requests' => $requests
        ));

        $response = $service->presentations->batchUpdate($presentationId, $batchUpdateRequest);

        //echo "<br>Image successfully moved.<br>";
    }

    function duplicateSlide($presentationId, $slideId)
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(Slides::PRESENTATIONS);
        $service = new Slides($client);



        $presentation = $service->presentations->get($presentationId);
        $slides = $presentation->getSlides();
        $duplicatedSlideId = '';

        foreach ($slides as $slide) {
            if ($slide->getObjectId() === $slideId) {
                $requests = array(
                    new SlidesRequest(array(
                        'duplicateObject' => array(
                            'objectId' => $slideId,
                        ),
                    ))
                );

                $batchUpdateRequest = new BatchUpdatePresentationRequest(array(
                    'requests' => $requests
                ));

                $response = $service->presentations->batchUpdate($presentationId, $batchUpdateRequest);
                $replies = $response->getReplies();
                $duplicatedSlideId = $replies[0]->getDuplicateObject()->getObjectId();
                break;
            }
        }

        //echo "New duplicated Slide ID: " . $duplicatedSlideId;
    }

    function replacePlaceholderForImage($presentationId, $arregloImagenes)
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(Slides::PRESENTATIONS);
        $service = new Slides($client);


        $requests = array();

        $requests[] = new SlidesRequest(array(
            'replaceImage' => array(
                'imageObjectId' => $arregloImagenes['objectId'],
                'imageReplaceMethod' => 'CENTER_INSIDE',
                'url' => $arregloImagenes['image']
            )
        ));

        $batchUpdateRequest = new BatchUpdatePresentationRequest(array(
            'requests' => $requests
        ));

        $service->presentations->batchUpdate($presentationId, $batchUpdateRequest);

        //echo "El marcador de posición de la imagen se ha reemplazado correctamente.";


    }

    function getProductsGid($productos)
    {
        $arrayGid = [];
        foreach ($productos as $producto) {
            $arrayGid[] = $producto['gid'];
        }
        return $arrayGid;
    }

    public function fillSecondSlideDeprecated($presentationId, $title2, $secondImage)
    {

        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->SetScopes(['https://www.googleapis.com/auth/drive.file']);
        $driveService = new Drive($client);
        $client->addScope(Slides::PRESENTATIONS);

        $slideNumber = 1;
        $pageNumber = 1;
        $objectInfo = $this->getObjectInfo($presentationId, $slideNumber);

        $data = [
            'Image' => $secondImage,
            'Title' => $title2,
        ];

        $y = 0;
        foreach ($data as $label => $value) {
            $element = $objectInfo[$y];
            $elementId = $element['objectId'];
            if ($label == 'Image') {
                $pageId = $this->getPageId($presentationId, $pageNumber);
                $this->deleteObject($presentationId, $elementId);
                $this->insertImage($presentationId, $pageId, $value);
            } else {
                $this->insertTextInEmptyTextField($presentationId, $elementId, $value);
            }
            $y++;
        }
    }

    public function fillSecondSlideDeprecated2($presentationId, $products)
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->SetScopes(['https://www.googleapis.com/auth/drive.file']);
        $client->addScope(Slides::PRESENTATIONS);

        // Ordenar el array por la clave 'name' en orden descendente
        usort($products, function ($a, $b) {
            return strcmp($b['primaryTitle'], $a['primaryTitle']);
        });
        $bool = true;
        $duplicates = count($products) - 1;
        for ($i = 0; $i <= $duplicates; $i++) {
            if ($duplicates > 0 && $i < $duplicates) {
                $pageNumber = 2;
                $slideId = $this->getPageId($presentationId, $pageNumber);
                $duplicatedSlideId = $this->duplicateSlide($presentationId, $slideId);
                if ($bool) {
                    $this->fillNewThirdSlide($presentationId, $products);
                }
                $bool = false;
                $pageNumber = 1;
                $slideId = $this->getPageId($presentationId, $pageNumber);
                $duplicatedSlideId = $this->duplicateSlide($presentationId, $slideId);
                $slideNumber = 1 + 1;
                $specsNumber = 0;
            } else {
                $slideNumber = 1;
            }

            $objectInfo = $this->getObjectInfo($presentationId, $slideNumber);

            // Iterar sobre las claves de $producto
            $y = 0;
            foreach (['primaryTitle', 'primaryImageURL', 'primarySubtitle', 'secondaryTitle', 'secondarySubtitle', 'tertiaryTitle', 'secondaryImageURL', 'tertiaryTitle'] as $key) {
                $element = $objectInfo[$y];
                $elementId = $element['objectId'];

                // Utilizar la clave actual para acceder al valor en $producto
                if ($key === 'primaryImageURL' || $key === 'secondaryImageURL') {
                    $this->replacePlaceholderForImage($presentationId, $elementId, $products[$i][$key]);
                } else {
                    $this->insertTextInEmptyTextField($presentationId, $elementId, $products[$i][$key]);
                }
                $y++;
            }
        }
    }

    public function fillThirdSlideDeprecated($presentationId, $products)
    {
        $client = new Client();
        $client->useApplicationDefaultCredentials();
        $client->SetScopes(['https://www.googleapis.com/auth/drive.file']);
        $client->addScope(Slides::PRESENTATIONS);
        $pageNumber = 2;

        // Ordenar el array por la clave 'name' en orden descendente
        usort($products, function ($a, $b) {
            return strcmp($b['name'], $a['name']);
        });

        $duplicates = count($products) - 1;
        for ($i = 0; $i <= $duplicates; $i++) {
            if ($duplicates > 0 && $i < $duplicates) {
                $slideId = $this->getPageId($presentationId, $pageNumber);
                $duplicatedSlideId = $this->duplicateSlide($presentationId, $slideId);
                $slideNumber = 2 + 1;
            } else {
                $slideNumber = 2;
            }

            $objectInfo = $this->getObjectInfo($presentationId, $slideNumber);

            // Iterar sobre las claves de $producto
            $y = 0;
            foreach (['name', 'image_url', 'subsubtitle', 'subtitle', 'category', 'materials', 'environment', 'color', 'print', 'material', 'dimensions', 'weight'] as $key) {
                $element = $objectInfo[$y];
                $elementId = $element['objectId'];

                // Utilizar la clave actual para acceder al valor en $producto
                if ($key === 'image_url') {
                    $this->replacePlaceholderForImage($presentationId, $elementId, $products[$i][$key]);
                } else {
                    $this->insertTextInEmptyTextField($presentationId, $elementId, $products[$i][$key]);
                }
                $y++;
            }
        }
    }
}
