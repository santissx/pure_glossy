<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FallbackController extends Controller
{
    public function notFound()
    {
        return response()->view('error404', [], 404);
    }
}

