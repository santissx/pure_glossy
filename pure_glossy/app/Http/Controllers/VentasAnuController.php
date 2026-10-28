<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentasAnuController extends Controller
{
    // Muestra el historial de ventas anuladas
    public function index(Request $request)
    {
        // 1. Iniciamos la consulta con tus JOIN y SELECT originales
        $query = DB::table('ventas_anuladas')
            ->join('ventas', 'ventas_anuladas.id_venta', '=', 'ventas.id_venta')
            ->join('users', 'ventas_anuladas.id_usuario_anulador', '=', 'users.id')
            ->select(
                'ventas_anuladas.id_venta_anulada', 
                'ventas_anuladas.id_venta', 
                'users.name as vendedor_name', 
                'ventas_anuladas.descripcion', 
                'ventas_anuladas.fecha_anu'
            );

        // 2. APLICAMOS LA LÓGICA DE FILTROS PARA "DATETIME"
        if ($request->filled('fechainicio') && $request->filled('fechafin')) {
            // Si mandó las dos fechas, completamos el inicio del día y el final del día
            $fechaInicio = $request->input('fechainicio') . ' 00:00:00';
            $fechaFin = $request->input('fechafin') . ' 23:59:59';
            $query->whereBetween('ventas_anuladas.fecha_anu', [$fechaInicio, $fechaFin]);
            
        } elseif ($request->filled('fechainicio')) {
            // Si solo mandó "Desde", traemos todo desde ese día a las 00:00 en adelante
            $fechaInicio = $request->input('fechainicio') . ' 00:00:00';
            $query->where('ventas_anuladas.fecha_anu', '>=', $fechaInicio);
            
        } elseif ($request->filled('fechafin')) {
            // Si solo mandó "Hasta", traemos todo hasta ese día a las 23:59:59
            $fechaFin = $request->input('fechafin') . ' 23:59:59';
            $query->where('ventas_anuladas.fecha_anu', '<=', $fechaFin);
        }

        // 3. Ejecutamos la consulta ordenando por las más recientes primero
        $ventas = $query->orderBy('ventas_anuladas.fecha_anu', 'desc')->get();

        // 4. Retornamos la vista que especificaste
        return view('ventas_anu', compact('ventas'));  
    }
}