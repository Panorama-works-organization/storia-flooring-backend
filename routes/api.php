<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\catalog\createController;


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

Route::prefix('v1')->group(function () {
    Route::POST('/catalog/generate', [createController::class, 'createCatalog']);
    Route::POST('/catalog/generate2', [createController::class, 'createCatalog2']);
    //Route::POST('/catalog/test', [createController::class, 'test']);
    Route::POST('/catalog/create-test', [createController::class, 'test']);
    //Route::GET('/products', [ApiController::class, 'getProducts']);
});
