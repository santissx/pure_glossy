<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Verifica si el usuario está autenticado y si su rol es 'administrador'
        if (Auth::check() && Auth::user()->rol === 'administrador') {
            return $next($request); // Permite el acceso a la siguiente solicitud
        }

        // Redirige a la página de inicio o a otra página si no es administrador
        return redirect()->route('views.ventas')->with('error', 'No tienes acceso a esta sección.');
    }
}