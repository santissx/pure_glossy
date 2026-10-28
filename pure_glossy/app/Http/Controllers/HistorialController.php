<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistorialController extends Controller
{
    public function mostrar(Request $request)
    {
        // Construir la consulta base
        $query = DB::table('ventas')
            ->leftJoin('ventas_anuladas', 'ventas.id_venta', '=', 'ventas_anuladas.id_venta') // Unión para identificar anuladas
            ->join('users', 'ventas.id_usuario', '=', 'users.id')
            ->join('metodos_pago', 'ventas.id_metodo_pago', '=', 'metodos_pago.id_metodo_pago')
            ->leftJoin('clientes_corrientes', 'ventas.id_cliente', '=', 'clientes_corrientes.id_cliente')
            ->select(
                'ventas.id_venta',
                'users.id as vendedor_id',
                'users.name as vendedor_name',
                'ventas.monto_total',
                'ventas.descuento',
                'metodos_pago.nombre as metodo_pago',
                'ventas.fecha_venta',
                'clientes_corrientes.id_cliente as clientec',
                'clientes_corrientes.nombre_y_apellido as nombre_clientec',
                'clientes_corrientes.telefono as telefono_clientec', 
                'ventas.cliente_nombre',
                'ventas.cliente_telefono',
                'ventas.observaciones'
            )
            ->whereNull('ventas_anuladas.id_venta'); // Excluir ventas anuladas
    
        // 1. Filtro por Vendedor
        if ($request->filled('vendedor')) {
            $query->where('users.id', $request->input('vendedor'));
        }

        // 2. Filtro por Cliente (Funcionalidad Tuya)
        if ($request->filled('id_cliente')) {
            $query->where('ventas.id_cliente', $request->input('id_cliente'));
        }
    
        // 3. Filtro de Fechas adaptado para DATETIME (Tu lógica segura anti-errores)
        if ($request->filled('fechainicio') && $request->filled('fechafin')) {
            $query->whereBetween('ventas.fecha_venta', [
                $request->input('fechainicio') . ' 00:00:00', 
                $request->input('fechafin') . ' 23:59:59'
            ]);
        } elseif ($request->filled('fechainicio')) {
            $query->where('ventas.fecha_venta', '>=', $request->input('fechainicio') . ' 00:00:00');
        } elseif ($request->filled('fechafin')) {
            $query->where('ventas.fecha_venta', '<=', $request->input('fechafin') . ' 23:59:59');
        }
    
        // Obtener los resultados ordenados
        $ventas = $query->orderBy('ventas.fecha_venta', 'desc')->get();
    
        // Obtener listas para los menús desplegables (Trayendo a todos los estados activos sin discriminar administrador/empleado)
        $vendedores = DB::table('users')->select('id', 'name')->where('estado', 'activo')->get();
        $clientes = DB::table('clientes_corrientes')->where('estado', 'activo')->get(); 
    
        // Calcular el total de ventas filtradas
        $totalVentas = $ventas->sum('monto_total');

        // Retornar la vista con los datos unificados
        return view('historial', [
            'ventas' => $ventas,
            'vendedores' => $vendedores,
            'clientes' => $clientes, 
            'totalVentas' => $totalVentas, 
        ]);
    }

    public function obtenerDetalleVenta($idVenta)
    {
        $productos = DB::table('ventas_productos')
            ->join('productos', 'ventas_productos.id_producto', '=', 'productos.id_producto')
            ->select(
                'productos.nombre as producto',
                'ventas_productos.cantidad',
                'ventas_productos.precio',
                'ventas_productos.precio_lista' // <--- Seleccionamos el precio de lista original
            )
            ->where('ventas_productos.id_venta', $idVenta)
            ->get();

        // Adjuntamos la leyenda "- Promo" si el precio vendido es menor al precio de lista
        foreach ($productos as $p) {
            if (isset($p->precio_lista) && $p->precio < $p->precio_lista) {
                $p->producto .= ' - Promo';
            }
        }

        return response()->json($productos);
    }

    public function anularVenta(Request $request)
    {
        $validated = $request->validate([
            'id_venta' => 'required|exists:ventas,id_venta',
            'id_usuario_anulador' => 'required|exists:users,id',
            'descripcion' => 'required|string|max:255',
        ]);

        $productos = DB::table('ventas_productos')
            ->join('productos', 'ventas_productos.id_producto', '=', 'productos.id_producto')
            ->select(
                'productos.id_producto',
                'ventas_productos.cantidad'
            )
            ->where('ventas_productos.id_venta', $validated['id_venta'])
            ->get();

        foreach ($productos as $producto) {
            DB::table('productos')
                ->where('id_producto', $producto->id_producto)
                ->increment('stock', $producto->cantidad);
        }
        
        DB::table('ventas_anuladas')->insert([
            'id_venta' => $validated['id_venta'],
            'id_usuario_anulador' => $validated['id_usuario_anulador'],
            'descripcion' => $validated['descripcion'],
        ]);

        return redirect()->back()->with('success', 'Venta anulada correctamente.');
    }

    public function generarTicket($idVenta)
    {
        $venta = DB::table('ventas')
            ->join('users', 'ventas.id_usuario', '=', 'users.id')
            ->join('metodos_pago', 'ventas.id_metodo_pago', '=', 'metodos_pago.id_metodo_pago')
            ->leftJoin('clientes_corrientes', 'ventas.id_cliente', '=', 'clientes_corrientes.id_cliente')
            ->select(
                'ventas.id_venta',
                'users.name as vendedor_name',
                'ventas.monto_total',
                'ventas.descuento',
                'metodos_pago.nombre as metodo_pago',
                'ventas.fecha_venta',
                'clientes_corrientes.id_cliente as clientec',
                'clientes_corrientes.nombre_y_apellido as nombre_clientec',
                'clientes_corrientes.telefono as telefono_clientec', 
                'ventas.cliente_nombre',
                'ventas.cliente_telefono'
            )
            ->where('ventas.id_venta', $idVenta)
            ->first();

        $productos = DB::table('ventas_productos')
            ->join('productos', 'ventas_productos.id_producto', '=', 'productos.id_producto')
            ->leftJoin('categorias', 'productos.id_categoria', '=', 'categorias.id_categoria')
            ->select(
                'productos.nombre as producto',
                'productos.codigo',
                'productos.codigo_barra',
                'productos.id_categoria',
                'categorias.categoria as nombre_categoria',
                'ventas_productos.cantidad',
                'ventas_productos.precio',
                'ventas_productos.precio_lista' // <--- Seleccionamos el precio de lista
            )
            ->where('ventas_productos.id_venta', $idVenta)
            ->get();

        $local = [
            'nombre'    => 'SOLUCIONES ELÉCTRICAS',
            'telefono'  => '3705033180',
            'direccion' => 'AV. Cabral 586 - Formosa Capital',
            'facebook'  => 'Soluciones Eléctricas Fsa',
            'instagram' => '@solucioneselectricasfsa',
        ];

        return view('ticket', compact('venta', 'productos', 'local'));
    }
}