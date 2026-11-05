<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromocionController extends Controller
{
    public function index()
    {
        // 1. Obtener promociones activas
        $promociones = DB::table('promociones')
            ->where('estado', 'activo')
            ->get();

        // 2. Adjuntar los productos con su marca y categoría
        foreach ($promociones as $promo) {
            $promo->productos = DB::table('promocion_productos as pp')
                ->join('productos as p', 'pp.id_producto', '=', 'p.id_producto')
                ->leftJoin('marcas as m', 'p.id_marca', '=', 'm.id_marca')
                ->leftJoin('categorias as c', 'p.id_categoria', '=', 'c.id_categoria')
                ->where('pp.id_promocion', $promo->id_promocion)
                ->select(
                    'p.id_producto', 
                    'p.nombre', 
                    'p.precio_venta', 
                    'pp.cantidad', 
                    'pp.descuento_porcentaje',
                    'm.nombre as marca',
                    'c.categoria as categoria'
                )
                ->get();
        }

        // 3. Obtener productos activos con marca y categoría para los buscadores de los modales
        $productos = DB::table('productos as p')
            ->leftJoin('marcas as m', 'p.id_marca', '=', 'm.id_marca')
            ->leftJoin('categorias as c', 'p.id_categoria', '=', 'c.id_categoria')
            ->where('p.estado', 'activo')
            ->select(
                'p.id_producto',
                'p.nombre',
                'p.codigo',
                'p.codigo_barra',
                'p.precio_venta',
                'm.nombre as marca',
                'c.categoria as categoria'      
            )
            ->orderBy('p.nombre', 'asc')
            ->get();

        return view('promociones', compact('promociones', 'productos'));
    }

    public function actualizarPrecios()
    {
        DB::beginTransaction();

        try {
            $promociones = DB::table('promociones')
                ->where('estado', 'activo')
                ->get();

            foreach ($promociones as $promo) {
                $productos = DB::table('promocion_productos as pp')
                    ->join('productos as p', 'pp.id_producto', '=', 'p.id_producto')
                    ->where('pp.id_promocion', $promo->id_promocion)
                    ->select(
                        'p.precio_venta',
                        'pp.cantidad',
                        'pp.descuento_porcentaje'
                    )
                    ->get();

                $nuevoPrecio = 0;

                foreach ($productos as $producto) {
                    $precioProducto = $producto->precio_venta * $producto->cantidad;

                    if ($promo->tipo_descuento === 'producto') {
                        $descuentoProducto = (float) $producto->descuento_porcentaje;
                        $precioProducto = $precioProducto * (1 - $descuentoProducto / 100);
                    }

                    $nuevoPrecio += $precioProducto;
                }

                if ($promo->tipo_descuento === 'promocion') {
                    $descuentoPromocion = (float) $promo->descuento_porcentaje;
                    $nuevoPrecio = $nuevoPrecio * (1 - $descuentoPromocion / 100);
                }

                DB::table('promociones')
                    ->where('id_promocion', $promo->id_promocion)
                    ->update([
                        'precio' => $nuevoPrecio
                    ]);
            }

            DB::commit();

            return redirect()->back()->with(
                'success',
                'Precios de las promociones actualizados correctamente.'
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with(
                'error',
                'Error al actualizar los precios: ' . $e->getMessage()
            );
        }
    }

    public function store(Request $request)
    {
        $request->merge([
            'tipo_descuento' => $request->tipo_descuento ?? 'promocion'
        ]);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'tipo_descuento' => 'required|in:promocion,producto',
            'descuento_porcentaje' => 'required_if:tipo_descuento,promocion|nullable|numeric|min:0|max:100',
            'productos' => 'required|array|min:1',
            'productos.*.id_producto' => 'required|integer',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.descuento_porcentaje' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();

        try {
            $descuentoPromocion = $request->tipo_descuento === 'promocion'
                ? $request->descuento_porcentaje
                : 0;

            $id_promocion = DB::table('promociones')->insertGetId([
                'nombre' => $request->nombre,
                'precio' => $request->precio,
                'descuento_porcentaje' => $descuentoPromocion,
                'tipo_descuento' => $request->tipo_descuento,
                'estado' => 'activo'
            ]);

            foreach ($request->productos as $item) {
                $descuentoProducto = $request->tipo_descuento === 'producto'
                    ? ($item['descuento_porcentaje'] ?? 0)
                    : 0;

                DB::table('promocion_productos')->insert([
                    'id_promocion' => $id_promocion,
                    'id_producto' => $item['id_producto'],
                    'cantidad' => $item['cantidad'],
                    'descuento_porcentaje' => $descuentoProducto
                ]);
            }

            DB::commit();

            return redirect()->back()->with(
                'success',
                'Promoción creada con éxito.'
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with(
                'error',
                'Error al crear la promoción: ' . $e->getMessage()
            );
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'tipo_descuento' => 'required|in:promocion,producto',
            'descuento_porcentaje' => 'required_if:tipo_descuento,promocion|nullable|numeric|min:0|max:100',
            'productos' => 'required|array|min:1',
            'productos.*.id_producto' => 'required|integer',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.descuento_porcentaje' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();

        try {
            $descuentoPromocion = $request->tipo_descuento === 'promocion'
                ? $request->descuento_porcentaje
                : 0;

            DB::table('promociones')
                ->where('id_promocion', $id)
                ->update([
                    'nombre' => $request->nombre,
                    'precio' => $request->precio,
                    'descuento_porcentaje' => $descuentoPromocion,
                    'tipo_descuento' => $request->tipo_descuento,
                ]);

            DB::table('promocion_productos')
                ->where('id_promocion', $id)
                ->delete();

            foreach ($request->productos as $item) {
                $descuentoProducto = $request->tipo_descuento === 'producto'
                    ? ($item['descuento_porcentaje'] ?? 0)
                    : 0;

                DB::table('promocion_productos')->insert([
                    'id_promocion' => $id,
                    'id_producto' => $item['id_producto'],
                    'cantidad' => $item['cantidad'],
                    'descuento_porcentaje' => $descuentoProducto
                ]);
            }

            DB::commit();

            return redirect()->back()->with(
                'success',
                'Promoción actualizada con éxito.'
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with(
                'error',
                'Error al actualizar la promoción: ' . $e->getMessage()
            );
        }
    }

    public function destroy($id)
    {
        DB::table('promociones')
            ->where('id_promocion', $id)
            ->update(['estado' => 'inactivo']);

        return redirect()->back()->with('success', 'Promoción desactivada.');
    }
}