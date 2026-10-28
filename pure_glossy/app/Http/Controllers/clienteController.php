<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class clienteController extends Controller
{
    public function mostrar()
    {
        $clientes = DB::table('clientes_corrientes')->get();
    
        return view('/clientes',['clientes' => $clientes]);
    }


    public function agregar(Request $request)
{
    // Validación de los datos recibidos
    $request->validate([
        'nombre_y_apellido' => 'required|string|max:255',
        'dni' => 'required|digits_between:5,12',
        'telefono' => 'required|digits_between:8,15',
    ]);

    \DB::table('clientes_corrientes')->insert([
        'nombre_y_apellido' => $request->input('nombre_y_apellido'),
        'DNI' => $request->input('dni'),
        'telefono' => $request->input('telefono'),
    ]);

    // Redirigir con un mensaje de éxito
    return redirect()->route('views.clientes')->with('success', 'Cliente agregado correctamente.');
}

    public function modificar(Request $request)
    {

       
        // Validación de datos
        $request->validate([
            'modal_id_cliente' => 'required|exists:clientes_corrientes,id_cliente|int',
            'nombre_y_apellido' => 'required|string|max:255',
            'dni' => 'required|digits_between:5,12',
            'telefono' => 'required|digits_between:8,15',
            'estado' => 'required|string|max:255',
        ]);

        DB::table('clientes_corrientes')
        ->where('id_cliente', $request->input('modal_id_cliente'))
        ->update([
          
            'nombre_y_apellido' => $request->input('nombre_y_apellido'),
            'DNI' => $request->input('dni'),
            'telefono' => $request->input('telefono'),
            'estado' => $request->input('estado'),

        ]);
          return redirect()->route('views.clientes')->with('success', 'cliente modificado correctamente.');

    }
    public function borrar($id_cliente)
    {
     
        DB::table('clientes_corrientes')
        ->where('id_cliente', $id_cliente)
        ->update(['estado' => 'desactivado']);
        return redirect()->route('views.clientes')->with('success', 'cliente eliminado correctamente.');

    }
}
