<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VentaController extends Controller
{
    public function mostrar()
    {
        $metodosDePago = DB::table('metodos_pago')->select('id_metodo_pago', 'nombre')->get();

        // Obtener promociones activas con sus productos desglosados
        $promociones = DB::table('promociones')
            ->where('estado', 'activo')
            ->get();

        foreach ($promociones as $promo) {
            $promo->productos = DB::table('promocion_productos as pp')
                ->join('productos as p', 'pp.id_producto', '=', 'p.id_producto')
                ->where('pp.id_promocion', $promo->id_promocion)
                ->select('p.id_producto', 'p.nombre', 'p.codigo_barra', 'p.precio_venta', 'p.stock', 'pp.cantidad')
                ->get();
        }

        return view('welcome', ['metodosDePago' => $metodosDePago, 'promociones' => $promociones]);
    }

    public function obtenerClientesCorrientes()
    {
        $clientesCorrientes = DB::table('clientes_corrientes')
            ->select('id_cliente', 'nombre_y_apellido', 'dni')
            ->where('estado', 'activo')
            ->get();
        
        return response()->json($clientesCorrientes);
    }

    public function buscarProductos(Request $request)
    {
        $queryStr = $request->get('q');
    
        $consulta = DB::table('productos')->where('estado', 'activo');

        if (!empty($queryStr)) {
            
            $palabras = array_filter(explode(' ', $queryStr));

            $consulta->where(function ($q) use ($palabras) {
                foreach ($palabras as $palabra) {
                    $q->where(function ($subQ) use ($palabra) {
                        $subQ->where('nombre', 'LIKE', '%' . $palabra . '%')
                            ->orWhere('codigo_barra', 'LIKE', '%' . $palabra . '%');
                    });
                }
            });
        }

        $productos = $consulta->take(30)->get();

        return response()->json($productos);
    }

public function guardar(Request $request)
    {
        $request->validate([
            'metodo_pago' => 'required|exists:metodos_pago,id_metodo_pago',
            'descuento' => 'nullable|numeric|min:0',
            'productos' => 'nullable|array',
            'productos.*.id_producto' => 'required|exists:productos,id_producto',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
            'promociones' => 'nullable|array',
            'promociones.*.id_promocion' => 'required|exists:promociones,id_promocion',
            'promociones.*.cantidad' => 'required|integer|min:1',
            'promociones.*.precio' => 'required|numeric|min:0',
            'id_cliente' => 'required_if:metodo_pago,3|exists:clientes_corrientes,id_cliente',
            'cliente_nombre' => 'nullable|string|max:255',
            'cliente_telefono' => 'nullable|string|max:50',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        // Validar que se haya enviado al menos un producto o una promoción
        if (empty($request->productos) && empty($request->promociones)) {
            return redirect()->back()->with('error', 'Debes agregar al menos un producto o una promoción a la venta.');
        }

        DB::beginTransaction();

        try {
            $cantidadesTotales = []; // Acumulador de stock requerido por id_producto

            // 1. Acumular stock de productos individuales
            if (!empty($request->productos)) {
                foreach ($request->productos as $p) {
                    $id = $p['id_producto'];
                    $cantidadesTotales[$id] = ($cantidadesTotales[$id] ?? 0) + $p['cantidad'];
                }
            }

            // 2. Consultar productos de promos y acumular stock requerido
            $promosDesglosadas = [];
            if (!empty($request->promociones)) {
                foreach ($request->promociones as $indexPromo => $promoItem) {
                    $idPromo = $promoItem['id_promocion'];
                    $cantCombos = $promoItem['cantidad'];
                    $precioComboIngresado = $promoItem['precio'];

                    // Traer los productos componentes de la promo
                    $itemsPromoDB = DB::table('promocion_productos as pp')
                        ->join('productos as p', 'pp.id_producto', '=', 'p.id_producto')
                        ->where('pp.id_promocion', $idPromo)
                        ->select('p.id_producto', 'p.nombre', 'p.precio_venta', 'pp.cantidad as cant_unitario')
                        ->get();

                    // Calcular precio proporcional por ítem
                    $sumaPreciosLista = 0;
                    foreach ($itemsPromoDB as $ip) {
                        $sumaPreciosLista += ($ip->precio_venta * $ip->cant_unitario);
                    }
                    $factorDescuento = ($sumaPreciosLista > 0) ? ($precioComboIngresado / $sumaPreciosLista) : 1;

                    foreach ($itemsPromoDB as $ip) {
                        $cantTotalItem = $ip->cant_unitario * $cantCombos;
                        $cantidadesTotales[$ip->id_producto] = ($cantidadesTotales[$ip->id_producto] ?? 0) + $cantTotalItem;

                        $promosDesglosadas[] = [
                            'id_producto' => $ip->id_producto,
                            'cantidad'    => $cantTotalItem,
                            'precio'      => $ip->precio_venta * $factorDescuento,
                        ];
                    }
                }
            }

            // 3. Calcular el subtotal / monto total de la venta
            $montoTotal = 0;
            if (!empty($request->productos)) {
                foreach ($request->productos as $p) {
                    $montoTotal += ($p['precio'] * $p['cantidad']);
                }
            }
            if (!empty($request->promociones)) {
                foreach ($request->promociones as $pr) {
                    $montoTotal += ($pr['precio'] * $pr['cantidad']);
                }
            }

            $descuento = $request->descuento ?? 0;
            if ($descuento > $montoTotal) {
                DB::rollBack();
                return redirect()->back()->with('error', 'El descuento no puede superar el monto total de la venta.');
            }

            $montoTotal -= $descuento;
            $esClienteCorriente = ($request->metodo_pago == 3);

            // 4. Crear la venta en la tabla `ventas`
            $idVenta = DB::table('ventas')->insertGetId([
                'id_usuario'       => Auth::id(),
                'fecha_venta'      => now()->format('Y-m-d H:i:s'), 
                'monto_total'      => $montoTotal,
                'id_metodo_pago'   => $request->metodo_pago,
                'descuento'        => $descuento,
                'id_cliente'       => $esClienteCorriente ? $request->id_cliente : null,
                'cliente_nombre'   => !$esClienteCorriente ? $request->cliente_nombre : null,
                'cliente_telefono' => !$esClienteCorriente ? $request->cliente_telefono : null,
                'observaciones'    => $request->observaciones,
            ]);

            // 5. Insertar productos individuales en `ventas_productos`
            if (!empty($request->productos)) {
                foreach ($request->productos as $p) {
                    $precioListaDB = DB::table('productos')->where('id_producto', $p['id_producto'])->value('precio_lista');

                    DB::table('ventas_productos')->insert([
                        'id_venta'     => $idVenta,
                        'id_producto'  => $p['id_producto'],
                        'cantidad'     => $p['cantidad'],
                        'precio'       => $p['precio'],
                        'precio_lista' => $precioListaDB ?? 0.00,
                    ]);

                    DB::table('productos')->where('id_producto', $p['id_producto'])->decrement('stock', $p['cantidad']);
                }
            }

            // 6. Insertar productos desglosados de las promociones en `ventas_productos`
            foreach ($promosDesglosadas as $itemDesglosado) {
                $precioListaDB = DB::table('productos')->where('id_producto', $itemDesglosado['id_producto'])->value('precio_lista');

                DB::table('ventas_productos')->insert([
                    'id_venta'     => $idVenta,
                    'id_producto'  => $itemDesglosado['id_producto'],
                    'cantidad'     => $itemDesglosado['cantidad'],
                    'precio'       => $itemDesglosado['precio'],
                    'precio_lista' => $precioListaDB ?? 0.00,
                ]);

                DB::table('productos')->where('id_producto', $itemDesglosado['id_producto'])->decrement('stock', $itemDesglosado['cantidad']);
            }

            DB::commit();

            return redirect()->route('views.ventas')
                ->with('success', 'Venta guardada con éxito')
                ->with('nueva_venta_id', $idVenta);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'No se pudo guardar la venta: ' . $e->getMessage());
        }
    }
}