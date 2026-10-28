<?php
namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VentasAnuladasExport implements FromCollection, WithHeadings
{
    protected $filtros;

    public function __construct($filtros = [])
    {
        $this->filtros = $filtros;
    }

    public function collection()
    {
        // Unimos con `ventas` y `users`
        $query = DB::table('ventas_anuladas')
            ->join('ventas', 'ventas_anuladas.id_venta', '=', 'ventas.id_venta')
            ->join('users', 'ventas_anuladas.id_usuario_anulador', '=', 'users.id');

        // APLICAMOS LA LÓGICA COMPLETA DE FILTROS (Acá estaba el error)
        if (!empty($this->filtros['fechainicio']) && !empty($this->filtros['fechafin'])) {
            $query->whereBetween('ventas_anuladas.fecha_anu', [
                $this->filtros['fechainicio'] . ' 00:00:00', 
                $this->filtros['fechafin'] . ' 23:59:59'
            ]);
            
        } elseif (!empty($this->filtros['fechainicio'])) {
            // Si solo hay fecha de inicio
            $query->where('ventas_anuladas.fecha_anu', '>=', $this->filtros['fechainicio'] . ' 00:00:00');
            
        } elseif (!empty($this->filtros['fechafin'])) {
            // Si solo hay fecha de fin
            $query->where('ventas_anuladas.fecha_anu', '<=', $this->filtros['fechafin'] . ' 23:59:59');
        }

        // Seleccionamos las columnas
        $anuladas = $query->select(
            'ventas_anuladas.id_venta_anulada',
            'ventas_anuladas.id_venta',
            'ventas_anuladas.fecha_anu',
            'ventas_anuladas.descripcion as motivo',
            'ventas.monto_total',
            'users.name as usuario_anulador'
        )->orderBy('ventas_anuladas.fecha_anu', 'desc')->get();

        // Sumamos el total de dinero devuelto
        $sumaTotal = $anuladas->sum('monto_total');

        // Fila vacía
        $anuladas->push((object)[
            'id_venta_anulada' => '',
            'id_venta'         => '',
            'fecha_anu'        => '',
            'motivo'           => '',
            'monto_total'      => '',
            'usuario_anulador' => ''
        ]);

        // Fila de TOTAL
        $anuladas->push((object)[
            'id_venta_anulada' => '',
            'id_venta'         => '',
            'fecha_anu'        => '',
            'motivo'           => 'TOTAL ANULADO:',
            'monto_total'      => $sumaTotal,
            'usuario_anulador' => ''
        ]);

        return $anuladas;
    }

    public function headings(): array
    {
        return [
            'ID Anulación',
            'ID Venta Original',
            'Fecha Anulación',
            'Motivo',
            'Monto Devuelto ($)',
            'Usuario que Anuló'
        ];
    }
}