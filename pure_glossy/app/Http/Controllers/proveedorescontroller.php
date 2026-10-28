<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class proveedorescontroller extends Controller
{
    public function mostrar()
    {
        $proveedores = DB::table('proveedores')->get();
    
        return view('proveedores',['proveedores' => $proveedores]);
    }


    public function agregar(Request $request)
    {
        // Validación de datos
        $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|int',
            'direccion' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'nom_preventista' => 'required|string|max:255',
            'num_preventista' => 'required|int|numeric:15',
        ]);

        DB::table('proveedores')->insert([
            'nombre' => $request->input('nombre'),
            'telefono' => $request->input('telefono'),
            'direccion' => $request->input('direccion'),
            'email' => $request->input('email'),
            'nombre_preventista' => $request->input('nom_preventista'),
            'num_preventista' => $request->input('num_preventista'),
        ]);

          // Redirigir a la vista de promociones (quizás con un mensaje de éxito)
          return redirect()->route('views.proveedores')->with('success', 'Proveedor agregada correctamente.');

    }
    public function modificar(Request $request)
    {     
        // Validación de datos
        $request->validate([
            'modal_id_proveedor' => 'required|exists:proveedores,id_proveedor|int', // Asegúrate de que el id exista
            'modal_nombre' => 'required|string|max:255',
            'modal_telefono' => 'required|int',
            'modal_direccion' => 'required|string|max:255',
            'modal_email' => 'required|string|max:255',
            'modal_nom_preventista' => 'required|string|max:255',
            'modal_num_preventista' => 'required|int|numeric:15',
            'modal_estado' => 'required|string|max:255',
        ]);

        DB::table('proveedores')
        ->where('id_proveedor', $request->input('modal_id_proveedor'))
        ->update([
            'nombre' => $request->input('modal_nombre'),
            'telefono' => $request->input('modal_telefono'),
            'direccion' => $request->input('modal_direccion'),
            'email' => $request->input('modal_email'),
            'nombre_preventista' => $request->input('modal_nom_preventista'),
            'num_preventista' => $request->input('modal_num_preventista'),
            'estado' => $request->input('modal_estado'),
        ]);

        
          return redirect()->route('views.proveedores')->with('success', 'Proveedor modificado correctamente.');

    }
    public function borrar($id_proveedor)
    {
     
        DB::table('proveedores')
        ->where('id_proveedor', $id_proveedor)
        ->update(['estado' => 'desactivado']);
        return redirect()->route('views.proveedores')->with('success', 'Proveedor eliminado correctamente.');

    }
}
