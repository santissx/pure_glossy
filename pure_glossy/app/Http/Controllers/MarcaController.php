<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarcaController extends Controller
{
    public function index()
    {
        $marcas = DB::table('marcas')->orderBy('nombre', 'asc')->get();
        return view('marcas', compact('marcas'));
    }

    public function agregar(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255|unique:marcas,nombre',
            'descripcion' => 'nullable|string|max:255',
        ], [
            'nombre.unique' => 'Esta marca ya se encuentra registrada.',
        ]);

        DB::table('marcas')->insert([
            'nombre'      => $request->input('nombre'),
            'descripcion' => $request->input('descripcion'),
            'estado'      => 'activo',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return redirect()->back()->with('success', 'Marca agregada correctamente.');
    }

    public function modificar(Request $request)
    {
        $id_marca = $request->input('id_marca');

        $request->validate([
            'id_marca'    => 'required|exists:marcas,id_marca',
            'nombre'      => 'required|string|max:255|unique:marcas,nombre,' . $id_marca . ',id_marca',
            'descripcion' => 'nullable|string|max:255',
            'estado'      => 'required|in:activo,desactivado',
        ]);

        DB::table('marcas')
            ->where('id_marca', $id_marca)
            ->update([
                'nombre'      => $request->input('nombre'),
                'descripcion' => $request->input('descripcion'),
                'estado'      => $request->input('estado'),
                'updated_at'  => now(),
            ]);

        return redirect()->back()->with('success', 'Marca modificada correctamente.');
    }

    public function borrar($id_marca)
    {
        DB::table('marcas')
            ->where('id_marca', $id_marca)
            ->update([
                'estado'     => 'desactivado',
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Marca desactivada correctamente.');
    }
}