<?php
namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GastosExport implements FromCollection, WithHeadings
{
    protected $filtros;

    public function __construct($filtros = [])
    {
        $this->filtros = $filtros;
    }

    public function collection()
    {
        $query = DB::table('gastos')
            ->join('users', 'gastos.id_usuario', '=', 'users.id');

        // Opcional: Filtros de fecha si los llegás a necesitar
        if (!empty($this->filtros['fechainicio']) && !empty($this->filtros['fechafin'])) {
            $query->whereBetween('gastos.fecha_gasto', [$this->filtros['fechainicio'] . ' 00:00:00', $this->filtros['fechafin'] . ' 23:59:59']);
        } elseif (!empty($this->filtros['fechainicio'])) {
            $query->where('gastos.fecha_gasto', '>=', $this->filtros['fechainicio'] . ' 00:00:00');
        } elseif (!empty($this->filtros['fechafin'])) {
            $query->where('gastos.fecha_gasto', '<=', $this->filtros['fechafin'] . ' 23:59:59');
        }

        $gastos = $query->select(
            'gastos.id_gasto',
            'gastos.fecha_gasto',
            'gastos.descripcion',
            'gastos.categoria',
            'gastos.monto',
            'users.name as usuario'
        )->orderBy('gastos.fecha_gasto', 'desc')->get();

        // Sumamos el total de los gastos
        $sumaTotal = $gastos->sum('monto');

        // Fila vacía
        $gastos->push((object)[
            'id_gasto'    => '',
            'fecha_gasto' => '',
            'descripcion' => '',
            'categoria'   => '',
            'monto'       => '',
            'usuario'     => ''
        ]);

        // Fila de TOTAL
        $gastos->push((object)[
            'id_gasto'    => '',
            'fecha_gasto' => '',
            'descripcion' => '',
            'categoria'   => 'TOTAL GASTOS:',
            'monto'       => $sumaTotal,
            'usuario'     => ''
        ]);

        return $gastos;
    }

    public function headings(): array
    {
        return [
            'ID Gasto',
            'Fecha',
            'Descripción',
            'Categoría',
            'Monto ($)',
            'Usuario'
        ];
    }
}