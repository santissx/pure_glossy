<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = DB::table('categorias')->orderBy('categoria', 'asc')->get();
        return view('categorias', compact('categorias'));
    }

    public function agregar(Request $request)
    {
        $request->validate([
            'categoria'   => 'required|string|max:255|unique:categorias,categoria',
            'descripcion' => 'nullable|string|max:255',
        ], [
            'categoria.unique' => 'Esta categoría ya existe.',
        ]);

        DB::table('categorias')->insert([
            'categoria'   => $request->input('categoria'),
            'descripcion' => $request->input('descripcion') ?? '',
        ]);

        return redirect()->back()->with('success', 'Categoría agregada correctamente.');
    }

    public function modificar(Request $request)
    {
        $id_categoria = $request->input('id_categoria');

        $request->validate([
            'id_categoria' => 'required|exists:categorias,id_categoria',
            'categoria'   => 'required|string|max:255|unique:categorias,categoria,' . $id_categoria . ',id_categoria',
            'descripcion' => 'nullable|string|max:255',
        ]);

        DB::table('categorias')
            ->where('id_categoria', $id_categoria)
            ->update([
                'categoria'   => $request->input('categoria'),
                'descripcion' => $request->input('descripcion') ?? '',
            ]);

        return redirect()->back()->with('success', 'Categoría modificada correctamente.');
    }
}