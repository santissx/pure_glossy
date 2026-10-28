<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PresupuestoController extends Controller
{

    public function mostrar() 
    {
        $productos = DB::table('productos')
            ->join('proveedores', 'productos.id_proveedor', '=', 'proveedores.id_proveedor')
            ->select('productos.*', 'proveedores.nombre as nombre_proveedor')
            ->where('productos.estado', 'activo')
            ->orderBy('productos.nombre', 'asc')
            ->get();

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

        $local = [
            'nombre'    => 'Soluciones Eléctricas',
            'telefono'  => '3705033180',
            'direccion' => 'AV. Cabral 586 - Formosa Capital',
            'facebook'  => 'Soluciones Eléctricas',
            'instagram' => '@solucioneselectricasfsa'
        ];

        return view('presupuesto', compact('productos', 'promociones', 'local'));
    }


    public function generar(Request $request)
    {
        $request->validate([
            'productos'        => 'nullable|array',
            'promociones'      => 'nullable|array',
            'descuento'        => 'nullable|numeric|min:0',
            'nombre_cliente'   => 'nullable|string|max:255',
            'telefono_cliente' => 'nullable|string|max:50',
            'titulo'           => 'nullable|string|max:255',
            'observaciones'    => 'nullable|string',
        ]);

        if (empty($request->productos) && empty($request->promociones)) {
            return redirect()->back()->with('error', 'Debes agregar al menos un producto o promoción.');
        }

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $descuento = $request->input('descuento') ?? 0;

            // 1. Crear el registro cabecera en presupuestos
            $idPresupuesto = DB::table('presupuestos')->insertGetId([
                'id_usuario'       => Auth::id(),
                'titulo'           => $request->titulo,
                'monto_total'      => 0, // Se actualiza al final del cálculo
                'descuento'        => $descuento,
                'nombre_cliente'   => $request->nombre_cliente,
                'telefono_cliente' => $request->telefono_cliente,
                'observaciones'    => $request->observaciones,
                'fecha'            => now()->format('Y-m-d H:i:s'),
                'estado'           => 'pendiente',
            ]);

            // 2. Insertar productos individuales (id_promocion = NULL)
            if (!empty($request->productos)) {
                foreach ($request->productos as $prodId => $datos) {
                    if (isset($datos['cantidad']) && $datos['cantidad'] > 0) {
                        $precioActual = DB::table('productos')->where('id_producto', $prodId)->value('precio_venta');

                        DB::table('presupuestos_productos')->insert([
                            'id_presupuesto' => $idPresupuesto,
                            'id_producto'    => $prodId,
                            'id_promocion'   => null,
                            'cantidad'       => $datos['cantidad'],
                            'precio'         => $precioActual,
                        ]);

                        $subtotal += ($precioActual * $datos['cantidad']);
                    }
                }
            }

            // 3. Insertar promociones desglosando sus componentes con id_promocion
            if (!empty($request->promociones)) {
                foreach ($request->promociones as $promoItem) {
                    if (isset($promoItem['cantidad']) && $promoItem['cantidad'] > 0) {
                        $idPromo = $promoItem['id_promocion'];
                        $cantCombos = $promoItem['cantidad'];
                        $precioCombo = $promoItem['precio'];

                        $componentes = DB::table('promocion_productos as pp')
                            ->join('productos as p', 'pp.id_producto', '=', 'p.id_producto')
                            ->where('pp.id_promocion', $idPromo)
                            ->select('p.id_producto', 'p.precio_venta', 'pp.cantidad as cant_unitario')
                            ->get();

                        $sumaPreciosLista = 0;
                        foreach ($componentes as $comp) {
                            $sumaPreciosLista += ($comp->precio_venta * $comp->cant_unitario);
                        }
                        $factorDescuento = ($sumaPreciosLista > 0) ? ($precioCombo / $sumaPreciosLista) : 1;

                        foreach ($componentes as $comp) {
                            $cantTotalItem = $comp->cant_unitario * $cantCombos;
                            $precioAjustado = $comp->precio_venta * $factorDescuento;

                            DB::table('presupuestos_productos')->insert([
                                'id_presupuesto' => $idPresupuesto,
                                'id_producto'    => $comp->id_producto,
                                'id_promocion'   => $idPromo,
                                'cantidad'       => $cantTotalItem,
                                'precio'         => $precioAjustado,
                            ]);
                        }

                        $subtotal += ($precioCombo * $cantCombos);
                    }
                }
            }

            // 4. Calcular el total final
            $total = max(0, $subtotal - $descuento);
            if ($descuento > $subtotal) {
                $descuento = $subtotal;
            }

            DB::table('presupuestos')->where('id_presupuesto', $idPresupuesto)->update([
                'monto_total' => $total,
                'descuento'   => $descuento,
            ]);

            DB::commit();
            return redirect()->back()
                ->with('success', 'Presupuesto generado con éxito.')
                ->with('nuevo_presupuesto_id', $idPresupuesto);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Hubo un error al generar el presupuesto: ' . $e->getMessage());
        }
    }


    public function editar($id)
    {
        $presupuesto = DB::table('presupuestos')->where('id_presupuesto', $id)->first();
        if (!$presupuesto || $presupuesto->estado === 'convertido') {
            return redirect()->route('views.historial_presupuestos')->with('error', 'Este presupuesto no se puede editar.');
        }

        $productos = DB::table('productos')->where('estado', 'activo')->get();

        // Obtener promociones activas para el modal
        $promociones = DB::table('promociones')->where('estado', 'activo')->get();
        foreach ($promociones as $promo) {
            $promo->productos = DB::table('promocion_productos as pp')
                ->join('productos as p', 'pp.id_producto', '=', 'p.id_producto')
                ->where('pp.id_promocion', $promo->id_promocion)
                ->select('p.id_producto', 'p.nombre', 'p.codigo_barra', 'p.precio_venta', 'p.stock', 'pp.cantidad')
                ->get();
        }

        // 1. Productos individuales guardados
        $productosSeleccionados = DB::table('presupuestos_productos as pp')
            ->join('productos as p', 'pp.id_producto', '=', 'p.id_producto')
            ->where('pp.id_presupuesto', $id)
            ->whereNull('pp.id_promocion')
            ->select('p.id_producto', 'p.nombre', 'p.codigo', 'p.codigo_barra', 'pp.cantidad', 'pp.precio as precio_guardado')
            ->get();

        // 2. Promociones guardadas
        $promocionesAgrupadasDB = DB::table('presupuestos_productos as pp')
            ->join('promociones as promo', 'pp.id_promocion', '=', 'promo.id_promocion')
            ->where('pp.id_presupuesto', $id)
            ->whereNotNull('pp.id_promocion')
            ->select('promo.id_promocion', 'promo.nombre', 'pp.id_producto', 'pp.cantidad as cant_desglosada', 'pp.precio')
            ->get();

        $promocionesDetectadas = [];
        if ($promocionesAgrupadasDB->isNotEmpty()) {
            $grupos = $promocionesAgrupadasDB->groupBy('id_promocion');

            foreach ($grupos as $idPromo => $items) {
                $primero = $items->first();

                $cantUnitaria = DB::table('promocion_productos')
                    ->where('id_promocion', $idPromo)
                    ->where('id_producto', $primero->id_producto)
                    ->value('cantidad') ?? 1;

                $cantCombos = (int) floor($primero->cant_desglosada / $cantUnitaria);

                // Reconstruir el precio unitario del combo
                $precioComboGuardado = 0;
                foreach ($items as $item) {
                    $precioComboGuardado += ($item->precio * $item->cant_desglosada);
                }
                $precioUnitarioCombo = $cantCombos > 0 ? ($precioComboGuardado / $cantCombos) : 0;

                $promocionesDetectadas[] = (object)[
                    'id_promocion'    => $primero->id_promocion,
                    'nombre'          => $primero->nombre,
                    'cantidad'        => $cantCombos > 0 ? $cantCombos : 1,
                    'precio_guardado' => round($precioUnitarioCombo, 2)
                ];
            }
        }

        return view('presupuesto_editar', compact('presupuesto', 'productos', 'promociones', 'productosSeleccionados', 'promocionesDetectadas'));
    }

    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'productos'        => 'nullable|array',
            'promociones'      => 'nullable|array',
            'descuento'        => 'nullable|numeric|min:0',
            'nombre_cliente'   => 'nullable|string|max:255',
            'telefono_cliente' => 'nullable|string|max:50',
            'titulo'           => 'nullable|string|max:255',
            'observaciones'    => 'nullable|string',
        ]);

        if (empty($request->productos) && empty($request->promociones)) {
            return redirect()->back()->with('error', 'Debes agregar al menos un producto o promoción.');
        }

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $descuento = $request->input('descuento') ?? 0;

            // Borramos los ítems anteriores para reinsertar el nuevo detalle
            DB::table('presupuestos_productos')->where('id_presupuesto', $id)->delete();

            // 1. Insertar productos individuales modificados
            if (!empty($request->productos)) {
                foreach ($request->productos as $prodId => $datos) {
                    if (isset($datos['cantidad']) && $datos['cantidad'] > 0) {
                        $precioActual = DB::table('productos')->where('id_producto', $prodId)->value('precio_venta');

                        DB::table('presupuestos_productos')->insert([
                            'id_presupuesto' => $id,
                            'id_producto'    => $prodId,
                            'id_promocion'   => null,
                            'cantidad'       => $datos['cantidad'],
                            'precio'         => $precioActual,
                        ]);

                        $subtotal += ($precioActual * $datos['cantidad']);
                    }
                }
            }

            // 2. Insertar promociones desglosando sus componentes
            if (!empty($request->promociones)) {
                foreach ($request->promociones as $promoId => $datos) {
                    if (isset($datos['cantidad']) && $datos['cantidad'] > 0) {
                        $idPromo = $datos['id_promocion'];
                        $cantCombos = $datos['cantidad'];
                        $precioCombo = $datos['precio'];

                        $componentes = DB::table('promocion_productos as pp')
                            ->join('productos as p', 'pp.id_producto', '=', 'p.id_producto')
                            ->where('pp.id_promocion', $idPromo)
                            ->select('p.id_producto', 'p.precio_venta', 'pp.cantidad as cant_unitario')
                            ->get();

                        $sumaPreciosLista = 0;
                        foreach ($componentes as $comp) {
                            $sumaPreciosLista += ($comp->precio_venta * $comp->cant_unitario);
                        }
                        $factorDescuento = ($sumaPreciosLista > 0) ? ($precioCombo / $sumaPreciosLista) : 1;

                        foreach ($componentes as $comp) {
                            $cantTotalItem = $comp->cant_unitario * $cantCombos;
                            $precioAjustado = $comp->precio_venta * $factorDescuento;

                            DB::table('presupuestos_productos')->insert([
                                'id_presupuesto' => $id,
                                'id_producto'    => $comp->id_producto,
                                'id_promocion'   => $idPromo,
                                'cantidad'       => $cantTotalItem,
                                'precio'         => $precioAjustado,
                            ]);
                        }

                        $subtotal += ($precioCombo * $cantCombos);
                    }
                }
            }

            $total = max(0, $subtotal - $descuento);
            if ($descuento > $subtotal) {
                $descuento = $subtotal;
            }

            DB::table('presupuestos')->where('id_presupuesto', $id)->update([
                'monto_total'      => $total,
                'descuento'        => $descuento,
                'nombre_cliente'   => $request->nombre_cliente,
                'telefono_cliente' => $request->telefono_cliente,
                'titulo'           => $request->titulo,
                'observaciones'    => $request->observaciones,
                'fecha'            => now()->format('Y-m-d H:i:s'),
            ]);

            DB::commit();
            return redirect()->route('views.historial_presupuestos')->with('success', 'Presupuesto modificado con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Hubo un error al modificar: ' . $e->getMessage());
        }
    }

    public function imprimir($id)
    {
        $presupuesto = DB::table('presupuestos')->where('id_presupuesto', $id)->first();
        if (!$presupuesto) {
            return redirect()->back()->with('error', 'Presupuesto no encontrado.');
        }

        // Obtener productos individuales
        $productos = DB::table('presupuestos_productos as pp')
            ->join('productos as p', 'pp.id_producto', '=', 'p.id_producto')
            ->leftJoin('categorias as c', 'p.id_categoria', '=', 'c.id_categoria')
            ->where('pp.id_presupuesto', $id)
            ->whereNull('pp.id_promocion')
            ->select('p.nombre', 'p.codigo', 'p.codigo_barra', 'p.id_categoria', 'c.categoria as nombre_categoria', 'pp.cantidad', 'pp.precio')
            ->get();

        // Obtener promociones agrupadas
        $promocionesDB = DB::table('presupuestos_productos as pp')
            ->join('promociones as promo', 'pp.id_promocion', '=', 'promo.id_promocion')
            ->where('pp.id_presupuesto', $id)
            ->whereNotNull('pp.id_promocion')
            ->select('promo.id_promocion', 'promo.nombre', 'pp.id_producto', 'pp.cantidad as cant_desglosada', 'pp.precio')
            ->get();

        $promociones = [];
        if ($promocionesDB->isNotEmpty()) {
            $grupos = $promocionesDB->groupBy('id_promocion');
            foreach ($grupos as $idPromo => $items) {
                $primero = $items->first();
                $cantUnitaria = DB::table('promocion_productos')
                    ->where('id_promocion', $idPromo)
                    ->where('id_producto', $primero->id_producto)
                    ->value('cantidad') ?? 1;

                $cantCombos = (int) floor($primero->cant_desglosada / $cantUnitaria);
                $precioTotalCombo = 0;
                foreach ($items as $item) {
                    $precioTotalCombo += ($item->precio * $item->cant_desglosada);
                }
                $precioUnitarioCombo = $cantCombos > 0 ? ($precioTotalCombo / $cantCombos) : 0;

                $promociones[] = (object)[
                    'nombre'   => $primero->nombre,
                    'cantidad' => $cantCombos > 0 ? $cantCombos : 1,
                    'precio'   => round($precioUnitarioCombo, 2),
                ];
            }
        }

        $local = [
            'nombre'    => 'Soluciones Eléctricas',
            'telefono'  => '3705033180',
            'direccion' => 'AV. Cabral 586 - Formosa Capital',
            'facebook'  => 'Soluciones Eléctricas',
            'instagram' => '@solucioneselectricasfsa'
            
        ];

        return view('presupuesto_print', compact('presupuesto', 'productos', 'promociones', 'local'));
    }
}