@extends('layouts.nav')

@section('title', 'Historial')

@section('ladoizq')
<div class="row h-100">
    <div class="col-lg-8 d-flex flex-column">
        <!-- Cuadro de historial -->
        <div class="card mb-3 flex-grow-1 left-table position-relative">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title">Historial de anulaciones</h5>
                <div class="table-responsive flex-grow-1 table-scrollgr">
                    <table class="table table-dark table-striped">
                        <thead>
                            <tr>
                                <th>id venta anulada</th>
                                <th>id venta</th>
                                <th>Usuario anulador</th>
                                <th>Descripcion</th>
                                <th>Fecha Anulación (A-M-D)</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ventas as $venta)
                                <tr>
                                    <td>{{ $venta->id_venta_anulada }}</td>
                                    <td>{{ $venta->id_venta }}</td>
                                    <td>{{ $venta->vendedor_name ?? 'N/A' }}</td>
                                    <td>{{ $venta->descripcion }}</td>
                                    <td>{{ $venta->fecha_anu }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="filtros mb-3 p-3 bg-dark rounded border border-secondary">
            <form method="GET" action="{{ route('views.anuladas') }}">

                <label for="rango" class="form-label">Filtrar por rango de fechas</label>
                <div class="input-group">
                    <input type="date" class="form-control" name="fechainicio" value="{{ request('fechainicio') }}" placeholder="Fecha inicio">
                    <span class="input-group-text">a</span>
                    <input type="date" class="form-control" name="fechafin" value="{{ request('fechafin') }}" placeholder="Fecha fin">
                </div>
                
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">
                        Aplicar filtros
                    </button>
                    
                    <button type="submit" formaction="{{ route('exportar.ventas_anuladas') }}" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Exportar Anulaciones a Excel
                    </button>
                </div>
            </form>
        </div>
        
    </div>

    <!-- Columna derecha superior -->
    <div class="col-lg-4 right-column">
        @include('parciales.columna_derecha')
    </div>
</div>
@endsection 

