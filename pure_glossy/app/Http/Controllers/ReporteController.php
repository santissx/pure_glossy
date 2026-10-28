<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VentasExport;
use App\Exports\StockExport;
use App\Exports\ComprasExport;
use App\Exports\GastosExport;
use App\Exports\VentasAnuladasExport;

class ReporteController extends Controller
{
    /**
     * Exporta las ventas filtradas por periodo (dia, semana, mes)
     */
    public function exportarVentas(Request $request)
    {
    // Armamos un array con los filtros que llegaron por la URL
    $filtros = [
        'vendedor'    => $request->input('vendedor'),
        'id_cliente'  => $request->input('id_cliente'),
        'fechainicio' => $request->input('fechainicio'),
        'fechafin'    => $request->input('fechafin'),
    ];

    $nombreArchivo = 'reporte_ventas_' . date('d-m-Y_H-i') . '.xlsx';
    
    // Le pasamos el array de filtros al Export
    return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\VentasExport($filtros), $nombreArchivo);
    }

    /**
     * Exporta el historial de compras
     */
    public function exportarCompras(Request $request)
    {
        // 1. Armamos un array con los filtros que llegaron por la URL
        $filtros = [
            'proveedor'   => $request->input('proveedor'),
            'fechainicio' => $request->input('fechainicio'),
            'fechafin'    => $request->input('fechafin'),
        ];

        // 2. Generamos un nombre dinámico para el archivo Excel
        $nombreArchivo = 'reporte_compras_' . date('d-m-Y_H-i') . '.xlsx';
        
        // 3. Le pasamos el array de filtros al Export y descargamos
        return Excel::download(new ComprasExport($filtros), $nombreArchivo);
    }
    public function exportarStock()
    {
        $nombreArchivo = 'inventario_stock_' . date('d-m-Y') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\StockExport, $nombreArchivo);
    }
    public function backupDatabase()
    {
        // 1. Obtenemos las credenciales de tu archivo .env
        $dbName = env('DB_DATABASE');
        $userName = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST', '127.0.0.1');

        // 2. Generamos el nombre del archivo con la fecha y hora exacta
        $fileName = 'backup_' . $dbName . '_' . date('d-m-Y_H-i') . '.sql';
        
        // 3. Definimos dónde se va a guardar temporalmente (en la carpeta storage de Laravel)
        $storagePath = storage_path('app/backups');
        
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true); // Crea la carpeta si no existe
        }

        $filePath = $storagePath . '/' . $fileName;

        // 4. Armamos el comando de consola
        // NOTA: Si usás XAMPP en Windows y tira error, cambiá "mysqldump" por "C:\xampp\mysql\bin\mysqldump"
        if (empty($password)) {
            $command = "C:\\xampp\\mysql\\bin\\mysqldump --user={$userName} --host={$host} {$dbName} > \"{$filePath}\"";
        } else {
            $command = "C:\\xampp\\mysql\\bin\\mysqldump --user={$userName} --password={$password} --host={$host} {$dbName} > \"{$filePath}\"";
        }

        // 5. Ejecutamos el comando
        exec($command, $output, $returnVar);

        // Si $returnVar es distinto de 0, significa que hubo un error
        if ($returnVar !== 0) {
            return redirect()->back()->with('error', 'Error al generar el backup. Asegurate de que mysqldump esté configurado.');
        }

        // 6. Descargamos el archivo y lo borramos del servidor temporal para no ocupar espacio
        return response()->download($filePath)->deleteFileAfterSend(true);
    }
    /**
     * Exporta el historial de gastos aplicando filtros de fechas
     */
    public function exportarGastos(Request $request)
    {
        $filtros = [
            'fechainicio' => $request->input('fechainicio'),
            'fechafin'    => $request->input('fechafin'),
        ];

        $nombreArchivo = 'reporte_gastos_' . date('d-m-Y_H-i') . '.xlsx';
        
        return Excel::download(new GastosExport($filtros), $nombreArchivo);
    }

    /**
     * Exporta el historial de ventas anuladas aplicando filtros de fechas
     */
    public function exportarVentasAnuladas(Request $request)
    {
        $filtros = [
            'fechainicio' => $request->input('fechainicio'),
            'fechafin'    => $request->input('fechafin'),
        ];

        $nombreArchivo = 'reporte_ventas_anuladas_' . date('d-m-Y_H-i') . '.xlsx';
        
        return Excel::download(new VentasAnuladasExport($filtros), $nombreArchivo);
    }
    }
