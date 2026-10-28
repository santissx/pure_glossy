<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BalanceController extends Controller
{
    public function mostrar(Request $request)
    {
        // CONDICIONAL INTELIGENTE CORREGIDO Y SEGURO
        if ($request->filled('fechainicio') || $request->filled('fechafin')) {
            
            // CASO A: Filtrado estricto por rango manual de fechas particulares
            $inicio = $request->input('fechainicio') ? $request->input('fechainicio') . ' 00:00:00' : '2024-01-01 00:00:00';
            $fin = $request->input('fechafin') ? $request->input('fechafin') . ' 23:59:59' : now()->format('Y-m-d H:i:s');
            
            // Deducimos el año base de la fecha inicial para no romper la gráfica anual
            $anioSeleccionado = date('Y', strtotime($inicio));
            
        } else {
            
            // CASO B: No hay fechas cargadas, filtramos el año completo seleccionado
            $anioSeleccionado = $request->input('anio') ? $request->input('anio') : now()->year;
            $inicio = $anioSeleccionado . '-01-01 00:00:00';
            $fin = $anioSeleccionado . '-12-31 23:59:59';
        }

        // REQUERIMIENTO A: Vendedor que más vendió
        $dataVendedores = DB::table('ventas')
            ->leftJoin('ventas_anuladas', 'ventas.id_venta', '=', 'ventas_anuladas.id_venta')
            ->join('users', 'ventas.id_usuario', '=', 'users.id')
            ->select('users.name as nombre', DB::raw('SUM(ventas.monto_total) as total'))
            ->whereNull('ventas_anuladas.id_venta')
            ->whereBetween('ventas.fecha_venta', [$inicio, $fin])
            ->groupBy('users.id', 'users.name')
            ->get();

        // REQUERIMIENTO B: Balances de Formas de Pago
        $dataFormasPago = DB::table('ventas')
            ->leftJoin('ventas_anuladas', 'ventas.id_venta', '=', 'ventas_anuladas.id_venta')
            ->join('metodos_pago', 'ventas.id_metodo_pago', '=', 'metodos_pago.id_metodo_pago')
            ->select('metodos_pago.nombre as metodo', DB::raw('SUM(ventas.monto_total) as total'))
            ->whereNull('ventas_anuladas.id_venta')
            ->whereBetween('ventas.fecha_venta', [$inicio, $fin])
            ->groupBy('metodos_pago.id_metodo_pago', 'metodos_pago.nombre')
            ->get();

        // REQUERIMIENTO C: Gastos / Inversión por Proveedor (sumado según productos de cada compra)
        $dataGastosProveedor = DB::table('productosxcompras')
            ->join('compras', 'productosxcompras.id_compra', '=', 'compras.id_compra')
            ->join('productos', 'productosxcompras.id_producto', '=', 'productos.id_producto')
            ->join('proveedores', 'productos.id_proveedor', '=', 'proveedores.id_proveedor')
            ->select(
                'proveedores.nombre as proveedor', 
                DB::raw('SUM(productosxcompras.cantidad_agregada * productosxcompras.precio_unitario) as total')
            )
            ->whereBetween('compras.fecha', [$inicio, $fin])
            ->groupBy('proveedores.id_proveedor', 'proveedores.nombre')
            ->get();

        // REQUERIMIENTO D: Ventas a lo largo del mes
        $dataVentasMes = DB::table('ventas')
            ->leftJoin('ventas_anuladas', 'ventas.id_venta', '=', 'ventas_anuladas.id_venta')
            ->select(DB::raw('DAY(ventas.fecha_venta) as dia'), DB::raw('SUM(ventas.monto_total) as total'))
            ->whereNull('ventas_anuladas.id_venta')
            ->whereBetween('ventas.fecha_venta', [$inicio, $fin])
            ->groupBy(DB::raw('DAY(ventas.fecha_venta)'))
            ->orderBy('dia', 'asc')
            ->get();

        // REQUERIMIENTO E: Ventas por mes en el año
        $dataVentasAnio = DB::table('ventas')
            ->leftJoin('ventas_anuladas', 'ventas.id_venta', '=', 'ventas_anuladas.id_venta')
            ->select(DB::raw('MONTH(ventas.fecha_venta) as mes'), DB::raw('SUM(ventas.monto_total) as total'))
            ->whereNull('ventas_anuladas.id_venta')
            ->whereYear('ventas.fecha_venta', $anioSeleccionado)
            ->groupBy(DB::raw('MONTH(ventas.fecha_venta)'))
            ->orderBy('mes', 'asc')
            ->get();

        // REQUERIMIENTO F: Balance Positivo Global (Formato Tabla)
        $totalVentas = DB::table('ventas')
            ->leftJoin('ventas_anuladas', 'ventas.id_venta', '=', 'ventas_anuladas.id_venta')
            ->whereNull('ventas_anuladas.id_venta')
            ->whereBetween('ventas.fecha_venta', [$inicio, $fin])
            ->sum('monto_total');

        $totalCompras = DB::table('compras')
            ->whereBetween('compras.fecha', [$inicio, $fin])
            ->sum('monto_compra');

        $totalGastos = DB::table('gastos')
            ->whereBetween('gastos.fecha_gasto', [substr($inicio, 0, 10), substr($fin, 0, 10)])
            ->sum('monto');

        // NUEVO CÁLCULO: Balance de Ganancias Limpio (Ganancia Neta histórica de productos)
       $totalCostos = DB::table('ventas_productos')
            ->join('ventas', 'ventas_productos.id_venta', '=', 'ventas.id_venta')
            ->leftJoin('ventas_anuladas', 'ventas.id_venta', '=', 'ventas_anuladas.id_venta')
            ->whereNull('ventas_anuladas.id_venta_anulada')
            ->whereBetween('ventas.fecha_venta', [$inicio, $fin])
            ->sum(DB::raw('COALESCE(ventas_productos.precio_lista, 0) * ventas_productos.cantidad'));

        // 2. La ganancia real es la plata neta que ingresó menos lo que costó la mercadería
        $gananciaLimpia = $totalVentas - $totalCostos;

        $dataBalance = [
            'totalVentas'    => $totalVentas,
            'totalCompras'   => $totalCompras,
            'totalGastos'    => $totalGastos,
            'balanceNeto'    => ($totalVentas - $totalCompras - $totalGastos),
            'gananciaLimpia' => $gananciaLimpia 
        ];

        return view('balances', compact(
            'dataVendedores', 
            'dataFormasPago', 
            'dataGastosProveedor', 
            'dataVentasMes', 
            'dataVentasAnio',
            'dataBalance',
            'anioSeleccionado'
        ));
    }
}