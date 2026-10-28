<?php
namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VentasExport implements FromCollection, WithHeadings
{
    protected $filtros;

    public function __construct($filtros)
    {
        $this->filtros = $filtros;
    }

    public function collection()
    {
        // 1. Igualamos la consulta base a la de tu HistorialController
        $query = DB::table('ventas')
            ->leftJoin('ventas_anuladas', 'ventas.id_venta', '=', 'ventas_anuladas.id_venta') // Para identificar anuladas
            ->join('users', 'ventas.id_usuario', '=', 'users.id')
            ->join('metodos_pago', 'ventas.id_metodo_pago', '=', 'metodos_pago.id_metodo_pago')
            ->leftJoin('clientes_corrientes', 'ventas.id_cliente', '=', 'clientes_corrientes.id_cliente')
            ->whereNull('ventas_anuladas.id_venta'); // Excluir ventas anuladas

        // 2. Filtrar por Vendedor
        if (!empty($this->filtros['vendedor'])) {
            $query->where('users.id', $this->filtros['vendedor']);
        }

        // 3. NUEVO: Filtrar por Cliente
        if (!empty($this->filtros['id_cliente'])) {
            $query->where('ventas.id_cliente', $this->filtros['id_cliente']);
        }

        // 4. Filtrar por Rango de Fechas adaptado a DATETIME
        if (!empty($this->filtros['fechainicio']) && !empty($this->filtros['fechafin'])) {
            $query->whereBetween('ventas.fecha_venta', [
                $this->filtros['fechainicio'] . ' 00:00:00', 
                $this->filtros['fechafin'] . ' 23:59:59'
            ]);
        } elseif (!empty($this->filtros['fechainicio'])) {
            $query->where('ventas.fecha_venta', '>=', $this->filtros['fechainicio'] . ' 00:00:00');
        } elseif (!empty($this->filtros['fechafin'])) {
            $query->where('ventas.fecha_venta', '<=', $this->filtros['fechafin'] . ' 23:59:59');
        }

        // 5. Seleccionamos las columnas y obtenemos los resultados
        $ventas = $query->select(
            'ventas.id_venta', 
            'ventas.fecha_venta', 
            'ventas.monto_total', 
            'ventas.descuento', 
            'metodos_pago.nombre as metodo_pago', 
            'users.name as vendedor',
            DB::raw("COALESCE(clientes_corrientes.nombre_y_apellido, 'Consumidor Final') as cliente_nombre")
        )->orderBy('ventas.fecha_venta', 'desc')->get();

        // 6. Calculamos el total sumando la columna 'monto_total'
        $sumaTotal = $ventas->sum('monto_total');

        // 7. Agregamos una fila vacía para dejar un espacio
        $ventas->push((object)[
            'id_venta'       => '',
            'fecha_venta'    => '',
            'monto_total'    => '',
            'descuento'      => '',
            'metodo_pago'    => '',
            'vendedor'       => '',
            'cliente_nombre' => ''
        ]);

        // 8. Inyectamos la fila del TOTAL al final de la colección
        $ventas->push((object)[
            'id_venta'       => '',
            'fecha_venta'    => 'TOTAL RECAUDADO:', 
            'monto_total'    => $sumaTotal,         
            'descuento'      => '',
            'metodo_pago'    => '',
            'vendedor'       => '',
            'cliente_nombre' => ''
        ]);

        return $ventas;
    }

    public function headings(): array
    {
        return [
            'ID Venta',
            'Fecha y Hora',
            'Monto Total ($)',
            'Descuento ($)',
            'Método de Pago',
            'Vendedor',
            'Cliente Corriente' // Nueva columna
        ];
    }
}