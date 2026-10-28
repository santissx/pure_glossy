<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;


class gastoscontroller extends Controller
{
    public function mostrar(Request $request)
    {
        // 1. Iniciamos la consulta base con el JOIN
        $query = DB::table('gastos')
            ->join('users', 'gastos.id_usuario', '=', 'users.id') 
            ->select('gastos.*', 'users.name as nombre_usuario');

        // 2. Aplicamos los filtros de fecha (Si el usuario los mandó desde la vista)
        if ($request->filled('fechainicio') && $request->filled('fechafin')) {
            $query->whereBetween('gastos.fecha_gasto', [
                $request->input('fechainicio') . ' 00:00:00', 
                $request->input('fechafin') . ' 23:59:59'
            ]);
        } elseif ($request->filled('fechainicio')) {
            $query->where('gastos.fecha_gasto', '>=', $request->input('fechainicio') . ' 00:00:00');
        } elseif ($request->filled('fechafin')) {
            $query->where('gastos.fecha_gasto', '<=', $request->input('fechafin') . ' 23:59:59');
        }

        // 3. Ejecutamos la consulta ordenando por fecha (los más nuevos arriba)
        $gastos = $query->orderBy('gastos.fecha_gasto', 'desc')->get();

        // 4. Calculamos el total de los gastos FILTRADOS. 
        // Usamos ->sum() directo sobre la colección para ahorrarle trabajo a la base de datos
        $totalgastos = $gastos->sum('monto');

        // 5. Mandamos los datos a la vista
        return view('gastos', [
            'gastos'      => $gastos,
            'totalgastos' => $totalgastos,
        ]);
    }


    public function agregar(Request $request)
    {
        // Validación de datos
        $request->validate([
            'motivo' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'monto' => 'required|numeric||regex:/^\d{1,10}(\.\d{0,2})?$/',
            'categoria' => 'required|in:administrativo,logistico,cotidiano,deudas',
            'id_usuario' => 'required|int|min:0',
        ]);

        DB::table('gastos')->insert([
            'motivo' => $request->input('motivo'),
            'descripcion' => $request->input('descripcion'),
            'monto' => $request->input('monto'),
            'categoria' => $request->input('categoria'),
            'id_usuario' => $request->input('id_usuario'),
        ]);

          // Redirigir a la vista de promociones (quizás con un mensaje de éxito)
          return redirect()->route('views.gastos')->with('success', 'Gasto agregado correctamente.');

    }
    public function modificar(Request $request)
    {

       
        // Validación de datos
        $request->validate([
            'id_gasto' => 'required|exists:gastos,id_gasto|int', // Asegúrate de que el id exista
            'motivo' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'monto' => 'required|string|max:255',
            'categoria' => 'required|in:administrativo,logistico,cotidiano,deudas',
        ]);

        DB::table('gastos')
        ->where('id_gasto', $request->input('id_gasto'))
        ->update([
            'motivo' => $request->input('motivo'),
            'descripcion' => $request->input('descripcion'),
            'monto' => $request->input('monto'),
            'categoria' => $request->input('categoria'), 

        ]);
          return redirect()->route('views.gastos')->with('success', 'Promoción modificada correctamente.');

    }
    public function borrar($id_gasto)
    {
     
        DB::table('gastos')
        ->where('id_gasto', $id_gasto)
    ->delete();
        return redirect()->route('views.gastos')->with('success', 'Gasto eliminado correctamente.');

    }

}