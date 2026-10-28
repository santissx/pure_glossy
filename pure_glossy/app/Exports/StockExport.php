<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return DB::table('productos')
            ->leftJoin('categorias', 'productos.id_categoria', '=', 'categorias.id_categoria')
            ->leftJoin('proveedores', 'productos.id_proveedor', '=', 'proveedores.id_proveedor')
            ->where('productos.estado', 'activo')
            ->select(
                'productos.codigo',
                'productos.codigo_barra',
                'productos.nombre',
                'categorias.categoria as nombre_categoria', // Traemos la categoría
                'proveedores.nombre as nombre_proveedor',   // Traemos el proveedor
                'productos.stock',
                'productos.precio_lista',
                'productos.precio_venta'
            )
            ->orderBy('productos.nombre', 'asc') // Lo ordenamos alfabéticamente
            ->get();
    }

    public function headings(): array
    {
        return [
            'Código Interno',
            'Código de Barras',
            'Nombre del Producto',
            'Categoría',
            'Proveedor',
            'Stock Actual',
            'Precio de Lista ($)',
            'Precio de Venta ($)'
        ];
    }
}