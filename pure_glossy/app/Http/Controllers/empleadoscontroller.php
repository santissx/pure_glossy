<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class empleadoscontroller extends Controller
{
    public function mostrar()
    {


        $users = DB::table('users')->get();
    
        return view('empleados',['users' => $users]);
    }


    public function agregar(Request $request)
{
    // Validación de los datos recibidos
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email',
        'password' => 'required|string|confirmed|min:4', 
        'rol' => 'required|in:administrador,empleado',
    ]);

    \DB::table('users')->insert([
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'password' => bcrypt($request->input('password')),  // Usar bcrypt para encriptar la contraseña
        'rol' => $request->input('rol'),
    ]);

    // Redirigir con un mensaje de éxito
    return redirect()->route('views.empleados')->with('success', 'Usuario agregado correctamente.');
}

    public function modificar(Request $request)
    {

        // Validación de datos
        $request->validate([
            'id' => 'required|exists:users,id|int',
            'name' => 'required|string|max:255', 
            'email' => 'required|string|email|max:255|unique:users,email,' . $request->input('id'), 
            'rol' => 'required|in:administrador,empleado', 
            'estado' => 'required|in:activo,desactivado', 
        ]);

        DB::table('users')
        ->where('id', $request->input('id'))
        ->update([
        
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'rol' => $request->input('rol'),
            'estado' => $request->input('estado'),

        ]);
        return redirect()->route('views.empleados')->with('success', 'Empleado modificado correctamente.');

    }
    public function borrar($id)
    {
    
        DB::table('users')
        ->where('id', $id)
        ->update(['estado' => 'desactivado']);
        return redirect()->route('views.empleados')->with('success', 'Empleado dado de baja correctamente.');

    }

    public function sesiones($id)
    {
        $usuario = User::findOrFail($id);

        $sesiones = $usuario->sessions()
            ->orderByDesc('session_start')
            ->get();

        return view('sesiones', [
            'usuario' => $usuario,
            'sesiones' => $sesiones
        ]);
    }

    public function misSesiones()
    {
        $usuario = Auth::user();

        $sesiones = $usuario->sessions()
            ->orderByDesc('session_start')
            ->get();

        return view('sesiones', [
            'usuario' => $usuario,
            'sesiones' => $sesiones
        ]);
    }
}
