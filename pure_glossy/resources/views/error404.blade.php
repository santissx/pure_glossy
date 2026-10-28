@extends('layouts.nav')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Error 404 - Página no encontrada</div>

                <div class="card-body">
                    <p>Lo sentimos, la página que estás buscando no existe.</p>
                    <a href="{{ route('views.ventas') }}" class="btn btn-primary">Volver al inicio</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
