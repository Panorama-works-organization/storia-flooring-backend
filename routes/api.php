<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\bluehostController;
use App\Http\Controllers\catalog\createController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\shopify\catalogController;
use App\Http\Controllers\shopify\customerController;
use App\Http\Controllers\shopify\defaultController;
use App\Http\Controllers\shopify\productController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Route::GET('/test', [productController::class, 'getCustomerMetafields']);

Route::prefix('v1')->group(function () {
    Route::POST('/catalogs/create', [catalogController::class, 'testFunction']);
    Route::POST('/customer/update-status', [customerController::class, 'updateCustomerStatus']);
    Route::POST('/customer/requestEditor', [customerController::class, 'sendEditorRequestEmail']);
    Route::POST('/slides/create', [ApiController::class, 'startNew']);
    Route::GET('/scan', [ApiController::class, 'scan']);
    Route::GET('/testShopify', [ApiController::class, 'testShopify']);
    Route::GET('getCustomerData', [customerController::class, 'getCustomerData']);
    Route::GET('uploadPDFToShopify', [productController::class, 'uploadPDFToShopify']);
    Route::GET('getCustomerMetafields', [productController::class, 'getCustomerMetafields']);
    Route::POST('createCatalog', [productController::class, 'createCatalog']);
    Route::POST('AddCustomerMetafield', [productController::class, 'AddCustomerMetafield']);
    Route::delete('/deleteFile', [bluehostController::class, 'deleteFile']);



    Route::GET('/getCustomerData', [customerController::class, 'getCustomerData']);
    Route::GET('/customer-metafields', [productController::class, 'getCustomerMetafields']);
});
Route::prefix('v1-1')->group(function () {
    Route::POST('/catalog/generate', [createController::class, 'createCatalog']);
});
