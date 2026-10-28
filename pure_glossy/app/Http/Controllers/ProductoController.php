<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    public function buscar(Request $request)
    {
        $termino = $request->input('q'); // Recoge el término de búsqueda del frontend

        // Búsqueda por nombre o código
        $productos = DB::table('productos')
             ->where(function ($query) use ($termino) {
            $query->where('nombre', 'like', '%' . $termino . '%')
            ->orWhere('codigo', 'like', '%' . $termino . '%');
            })
        ->where('estado', 'activo')
            ->get(); // Devuelve los resultados

        return response()->json($productos); // Devuelve los datos en formato JSON
    }

}
