
@extends('layouts.nav')

@section('title', 'Gastos')

@section('ladoizq')
<div class="row h-100">
    <div class="col-lg-8 d-flex flex-column">
        <!-- Cuadro de historial -->
        <div class="card mb-3 flex-grow-1 left-table border-secondary">
            <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white border-bottom border-secondary py-3">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="fas fa-wallet me-2"></i>Historial de Gastos
                </h5>
                <button type="button" class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#veragregargastos">
                    <i class="fas fa-plus me-1"></i> Agregar Gasto
                </button>
            </div>
            <div class="card-body d-flex flex-column p-0">
                <div class="table-responsive flex-grow-1 table-scrollgr">
                    <table class="table table-dark table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Motivo</th>
                                <th>Descripción</th>
                                <th>Monto</th>
                                <th>Fecha</th>
                                <th>Categoría</th>
                                <th>Usuario</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($gastos as $gasto)
                            <tr>
                                <td>{{ $gasto->id_gasto }}</td>
                                <td>{{ $gasto->motivo }}</td>
                                <td>{{ $gasto->descripcion }}</td>
                                <td class="fw-bold text-warning">${{ number_format($gasto->monto, 2) }}</td>
                                <td>{{ $gasto->fecha_gasto }}</td>
                                <td>{{ $gasto->categoria }}</td>
                                <td>{{ $gasto->nombre_usuario }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button class="btn btn-primary btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#vermodificargastos"
                                        data-id="{{ $gasto->id_gasto }}"
                                        data-motivo="{{ $gasto->motivo }}"
                                        data-descripcion="{{ $gasto->descripcion }}"
                                        data-monto="{{ $gasto->monto }}"
                                        data-fecha_gasto="{{ $gasto->fecha_gasto }}"
                                        data-categoria="{{ $gasto->categoria }}">
                                        Modificar
                                        </button>
                                        <form class="m-0 d-inline" action="{{route('gastos.borrar' , ['id_gasto' => $gasto->id_gasto])}}" method="POST" onsubmit="return confirm('¿Eliminar gasto?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="filtros mb-3 p-3 bg-dark rounded border border-secondary">
            <form method="GET" action="{{ route('views.gastos') }}"> <label for="rango" class="form-label text-white">Filtrar por rango de fechas</label>
                <div class="input-group">
                    <input type="date" class="form-control" name="fechainicio" value="{{ request('fechainicio') }}">
                    <span class="input-group-text">a</span>
                    <input type="date" class="form-control" name="fechafin" value="{{ request('fechafin') }}">
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Aplicar filtros</button>
                        
                        <button type="submit" formaction="{{ route('exportar.gastos') }}" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Exportar Gastos a Excel
                        </button>
                    </div>
                    
                    <h5 class="text-white m-0">
                        <strong>Gastos totales: <span class="text-danger">${{ number_format($totalgastos, 2) }}</span></strong>
                    </h5>
                </div>
            </form>
        </div>
        </div>

    <!-- Columna derecha superior -->
    <div class="col-lg-4 right-column">
        @include('parciales.columna_derecha')
    </div>

    
</div>




<!-- Modal agregar de gastos -->
<div class="modal fade" id="veragregargastos" tabindex="-1" aria-labelledby="veragregargastosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark">
            <form action="{{ route('gastos.agregar') }}" method="POST">
                @csrf 
                <div class="modal-header">
                    <h5 class="modal-title" id="veragregarLabel">Agregar Gasto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Campos para agregar productos -->
                   
                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo</label>
                        <input type="text" class="form-control" id="motivo" name="motivo" >
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion" required>
                    </div>
                    <div class="mb-3">
                        <label for="monto" class="form-label">Monto</label>
                        <input type="number" class="form-control" id="monto" name="monto"  step="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="categoria" class="form-label">Categoria</label>
                        <select class="form-control" id="categoria" name="categoria" required>
                            <option value="administrativo">Administrativo</option>
                            <option value="logistico">Logístico</option>
                            <option value="cotidiano">Cotidiano</option>
                            <option value="deudas">Deudas</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="id_usuario" class="form-label">Usuario</label>
                        <input type="number" class="form-control" id="id_usuario" name="id_usuario" value="{{ auth()->id() }}" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Agregar Gasto</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- Modal modificar gastos -->
<div class="modal fade" id="vermodificargastos" tabindex="-1" aria-labelledby="vermodificargastosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark">
            <form action="{{ route('gastos.modificar') }}" method="GET">
                @csrf
               
                <input type="hidden" name="id_gasto" id="modal_id_gasto">
                <div class="modal-header">
                    <h5 class="modal-title" id="vermodifgastosLabel">modificar Gasto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo</label>
                        <input type="text" class="form-control" id="motivo" name="motivo" >
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion" required>
                    </div>
                    <div class="mb-3">
                        <label for="monto" class="form-label">Monto</label>
                        <input type="number" class="form-control" id="monto" name="monto"  step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="categoria" class="form-label">Categoria</label>
                        <select class="form-control" id="categoria" name="categoria" required>
                            <option value="administrativo">Administrativo</option>
                            <option value="logistico">Logístico</option>
                            <option value="cotidiano">Cotidiano</option>
                            <option value="deudas">Deudas</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Modificar Gasto</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modificargastosModal = document.getElementById('vermodificargastos');
        modificargastosModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
    
            const id = button.getAttribute('data-id');
            const descripcion = button.getAttribute('data-descripcion');
            const monto = button.getAttribute('data-monto');
            const categoria = button.getAttribute('data-categoria');
            const motivo = button.getAttribute('data-motivo');
            modificargastosModal.querySelector('#modal_id_gasto').value = id;
            modificargastosModal.querySelector('#descripcion').value = descripcion;
            modificargastosModal.querySelector('#monto').value = monto;
            modificargastosModal.querySelector('#categoria').value = categoria;
            modificargastosModal.querySelector('#motivo').value = motivo;
        });
    });
    </script>
@endsection 


