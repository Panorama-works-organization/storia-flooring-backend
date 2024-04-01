<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;
use App\Mail\EmailNotification;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\bluehostController;
//use PDF;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    $pdf = PDF::loadView('catalog');
    return $pdf->stream('prueba.pdf');
    //return view('catalog');
});

Route::get('/start', [ApiController::class, 'start'])->name('start');
