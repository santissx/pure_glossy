@auth

@if(Auth::user()->rol === 'administrador')

@extends('layouts.nav')

@section('title', 'Historial de sesiones')

@section('ladoizq')

<div class="row h-100">

    <div class="col-lg-8 d-flex flex-column">

        <div class="card mb-3 flex-grow-1 left-table border-secondary">

            <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white border-bottom border-secondary py-3">

                <h5 class="card-title mb-0 fw-bold">
                    <i class="fas fa-history me-2"></i>
                    Historial de sesiones
                </h5>

                <a href="{{ route('views.empleados') }}" class="btn btn-secondary fw-bold">
                    <i class="fas fa-arrow-left me-1"></i>
                    Volver
                </a>

            </div>

            <div class="card-body d-flex flex-column p-0">

                <div class="p-3 text-white">

                    <h6 class="mb-1">
                        Usuario: <strong>{{ $usuario->name }}</strong>
                    </h6>

                    <small class="text-secondary">
                        Email: {{ $usuario->email }}
                    </small>

                </div>

                <div class="table-responsive flex-grow-1 table-scrollgr">

                    <table class="table table-dark table-striped align-middle mb-0">

                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Inicio de sesión</th>
                                <th>Cierre de sesión</th>
                                <th>Estado</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse ($sesiones as $sesion)

                            <tr>

                                <td>
                                    {{ $sesion->id_session }}
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($sesion->session_start)->format('d/m/Y H:i:s') }}
                                </td>

                                <td>
                                    @if($sesion->session_end)
                                        {{ \Carbon\Carbon::parse($sesion->session_end)->format('d/m/Y H:i:s') }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>

                                    @if($sesion->session_end)
                                        <span class="badge bg-secondary">
                                            Cerrada
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            Activa
                                        </span>
                                    @endif

                                </td>

                            </tr>

                            @empty

                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    No hay sesiones registradas para este usuario.
                                </td>
                            </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-4 right-column">
        @include('parciales.columna_derecha')
    </div>

</div>

@endsection

@else

<script>
    window.location = "{{ route('views.ventas') }}";
</script>

@endif

@else

<script>
    window.location = "{{ route('login') }}";
</script>

@endauth