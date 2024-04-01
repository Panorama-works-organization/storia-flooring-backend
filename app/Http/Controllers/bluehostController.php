<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;



class bluehostController extends Controller
{
    public function deleteFile()
    {
        $rutaArchivo = 'public/storage/Totems.pdf';

        try {
            Storage::delete($rutaArchivo);
            return response()->json(['mensaje' => 'Archivo borrado exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'Error al intentar borrar el archivo: ' . $e->getMessage()]);
        }
    }
}
