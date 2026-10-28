<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistorialPresupuestoController extends Controller
{
    public function mostrar(Request $request)
    {
        $query = DB::table('presupuestos')
            ->leftJoin('users', 'presupuestos.id_usuario', '=', 'users.id')
            ->select(
                'presupuestos.id_presupuesto',
                'users.name as vendedor_name',
                'presupuestos.fecha',
                'presupuestos.monto_total',
                'presupuestos.descuento',
                'presupuestos.nombre_cliente',
                'presupuestos.telefono_cliente',
                'presupuestos.estado'
            );

        if ($request->filled('fechainicio') && $request->filled('fechafin')) {
            $query->whereBetween('presupuestos.fecha', [
                $request->input('fechainicio') . ' 00:00:00', 
                $request->input('fechafin') . ' 23:59:59'
            ]);
        }

        $presupuestos = $query->orderBy('presupuestos.fecha', 'desc')->get();
        $totalPresupuestos = $presupuestos->sum('monto_total');

        return view('historial_presupuestos', compact('presupuestos', 'totalPresupuestos'));
    }

    public function obtenerDetalle($id)
    {
        $productos = DB::table('presupuestos_productos')
            ->join('productos', 'presupuestos_productos.id_producto', '=', 'productos.id_producto')
            ->select(
                'productos.nombre as producto',
                'presupuestos_productos.cantidad',
                'presupuestos_productos.precio'
            )
            ->where('presupuestos_productos.id_presupuesto', $id)
            ->get();

        return response()->json($productos);
    }

    public function eliminar(Request $request)
    {
        $request->validate(['id_presupuesto' => 'required|exists:presupuestos,id_presupuesto']);
        
        DB::table('presupuestos')->where('id_presupuesto', $request->id_presupuesto)->delete();
        
        return redirect()->back()->with('success', 'Presupuesto eliminado correctamente.');
    }

    public function convertir($id)
    {
        $presupuesto = DB::table('presupuestos')->where('id_presupuesto', $id)->first();

        if (!$presupuesto) {
            return redirect()->back()->with('error', 'El presupuesto no existe.');
        }

        // 1. Productos individuales (donde id_promocion es NULL)
        $productosIndividuales = DB::table('presupuestos_productos as pp')
            ->join('productos as p', 'pp.id_producto', '=', 'p.id_producto')
            ->where('pp.id_presupuesto', $id)
            ->whereNull('pp.id_promocion')
            ->select('p.id_producto', 'p.nombre', 'p.codigo_barra', 'p.stock', 'pp.precio', 'pp.cantidad')
            ->get();

        // 2. Promociones agrupadas (donde id_promocion NO es NULL)
        $promocionesAgrupadasDB = DB::table('presupuestos_productos as pp')
            ->join('promociones as promo', 'pp.id_promocion', '=', 'promo.id_promocion')
            ->where('pp.id_presupuesto', $id)
            ->whereNotNull('pp.id_promocion')
            ->select('promo.id_promocion', 'promo.nombre', 'promo.precio', 'pp.id_producto', 'pp.cantidad as cant_desglosada')
            ->get();

        $promocionesFormateadas = [];
        if ($promocionesAgrupadasDB->isNotEmpty()) {
            $grupos = $promocionesAgrupadasDB->groupBy('id_promocion');

            foreach ($grupos as $idPromo => $items) {
                $primero = $items->first();
                
                // Calculamos la cantidad de combos comprados dividiendo lo guardado por lo requerido en 1 combo
                $cantUnitaria = DB::table('promocion_productos')
                    ->where('id_promocion', $idPromo)
                    ->where('id_producto', $primero->id_producto)
                    ->value('cantidad') ?? 1;

                $cantCombos = (int) floor($primero->cant_desglosada / $cantUnitaria);

                $promocionesFormateadas[] = [
                    'id_promocion' => $primero->id_promocion,
                    'nombre'       => $primero->nombre,
                    'precio'       => (float) $primero->precio, // Toma el precio EXACTO original de la promo
                    'cantidad'     => $cantCombos > 0 ? $cantCombos : 1,
                ];
            }
        }

        DB::table('presupuestos')->where('id_presupuesto', $id)->update(['estado' => 'convertido']);

        return redirect()->route('views.ventas')
            ->with('cargar_presupuesto', $productosIndividuales)
            ->with('cargar_promociones_presupuesto', $promocionesFormateadas)
            ->with('descuento_presupuesto', $presupuesto->descuento)
            ->with('nombre_cliente', $presupuesto->nombre_cliente)
            ->with('telefono_cliente', $presupuesto->telefono_cliente)
            ->with('observaciones_presupuesto', $presupuesto->observaciones)
            ->with('id_presupuesto_convertido', $id);
    }

    public function actualizarPrecios($id)
        {
            DB::beginTransaction();
            try {
                $presupuesto = DB::table('presupuestos')->where('id_presupuesto', $id)->first();
                if (!$presupuesto) {
                    return redirect()->back()->with('error', 'Presupuesto no encontrado.');
                }

                $productosPresupuesto = DB::table('presupuestos_productos')->where('id_presupuesto', $id)->get();
                $subtotal = 0;

                foreach ($productosPresupuesto as $prod) {
                    // Buscamos el precio actual VIVO en la tabla de productos
                    $precioActual = DB::table('productos')->where('id_producto', $prod->id_producto)->value('precio_venta');
                    
                    // Actualizamos el registro intermedio
                    DB::table('presupuestos_productos')
                        ->where('id_presupuesto_producto', $prod->id_presupuesto_producto)
                        ->update(['precio' => $precioActual]);
                    
                    $subtotal += ($precioActual * $prod->cantidad);
                }

                // Recalculamos el total y ponemos la fecha de HOY
                $totalFinal = max(0, $subtotal - $presupuesto->descuento);
                
                DB::table('presupuestos')->where('id_presupuesto', $id)->update([
                    'fecha' => now()->format('Y-m-d H:i:s'),
                    'monto_total' => $totalFinal
                ]);

                DB::commit();
                return redirect()->back()->with('success', 'Los precios del presupuesto han sido actualizados al día de hoy.');

            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Error al actualizar precios.');
            }
        }
}