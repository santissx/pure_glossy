<?php
namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ComprasExport implements FromCollection, WithHeadings
{
    protected $filtros;

    // 1. Agregamos el constructor para recibir el array de filtros
    public function __construct($filtros)
    {
        $this->filtros = $filtros;
    }

    public function collection()
    {
        // 2. Inicializamos la consulta base haciendo el JOIN
        $query = DB::table('compras')
            ->join('proveedores', 'compras.id_proveedor', '=', 'proveedores.id_proveedor');

        // 3. Filtrar por Proveedor
        if (!empty($this->filtros['proveedor'])) {
            $query->where('compras.id_proveedor', $this->filtros['proveedor']);
        }

        // 4. Filtrar por Rango de Fechas (Usamos compras.fecha y contemplamos el fin del día)
        if (!empty($this->filtros['fechainicio']) && !empty($this->filtros['fechafin'])) {
            $fechaInicioCompleta = $this->filtros['fechainicio'] . ' 00:00:00';
            $fechaFinCompleta = $this->filtros['fechafin'] . ' 23:59:59';
            
            $query->whereBetween('compras.fecha', [$fechaInicioCompleta, $fechaFinCompleta]);
            
        } elseif (!empty($this->filtros['fechainicio'])) {
            $query->whereDate('compras.fecha', '>=', $this->filtros['fechainicio']);
            
        } elseif (!empty($this->filtros['fechafin'])) {
            $fechaFinCompleta = $this->filtros['fechafin'] . ' 23:59:59';
            $query->where('compras.fecha', '<=', $fechaFinCompleta);
        }

        // 5. Retornamos la consulta seleccionando las columnas y ejecutando get()
       // 1. Ejecutamos la consulta y la guardamos en la variable ($compras)
        $compras = $query->select(
            'compras.id_compra',
            'compras.fecha',
            'compras.monto_compra',
            'proveedores.nombre as nombre_proveedor'
        )
        ->orderBy('compras.fecha', 'desc')
        ->get();

        // 2. Sumamos todos los montos de compras
        $sumaTotal = $compras->sum('monto_compra');

        // 3. Espacio en blanco
        $compras->push((object)[
            'id_compra'        => '',
            'fecha'            => '',
            'monto_compra'     => '',
            'nombre_proveedor' => ''
        ]);

        // 4. Agregamos la fila del TOTAL
        $compras->push((object)[
            'id_compra'        => '',
            'fecha'            => 'TOTAL GASTADO:',
            'monto_compra'     => $sumaTotal,
            'nombre_proveedor' => ''
        ]);

        // 5. Retornamos para armar el Excel
        return $compras;
    }

    public function headings(): array
    {
        return [
            'N° de Compra',
            'Fecha y Hora',
            'Monto Total ($)',
            'Nombre del Proveedor'
        ];
    }
}