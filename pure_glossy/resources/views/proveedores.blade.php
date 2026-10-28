@extends('layouts.nav')

@section('title', 'Proveedores')

@section('ladoizq')
<div class="row h-100">
    <div class="col-lg-8 d-flex flex-column">
        <div class="card mb-3 flex-grow-1 left-table border-secondary">
            <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white border-bottom border-secondary py-3">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="fas fa-handshake me-2"></i>Proveedores
                </h5>
                <button type="button" class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#veragregarproveedor">
                    <i class="fas fa-plus me-1"></i> Agregar Proveedor
                </button>
            </div>
            <div class="card-body d-flex flex-column p-0">
                <div class="table-responsive flex-grow-1 table-scrollgr">
                    <table class="table table-dark table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Dirección</th>
                                <th>Email</th>
                                <th>Preventista</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($proveedores as $proveedor)
                            <tr>
                                <td>{{ $proveedor->id_proveedor }}</td>
                                <td>{{ $proveedor->nombre }}</td>
                                <td>{{ $proveedor->telefono }}</td>
                                <td>{{ $proveedor->direccion }}</td>
                                <td>{{ $proveedor->email }}</td>
                                <td>{{ $proveedor->nombre_preventista }} - {{ $proveedor->num_preventista }} </td>
                                <td>{{ $proveedor->estado }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button class="btn btn-primary btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#vermodificarproveedor"
                                        data-id="{{ $proveedor->id_proveedor }}"
                                        data-nombre="{{ $proveedor->nombre }}"
                                        data-telefono="{{ $proveedor->telefono }}"
                                        data-direccion="{{ $proveedor->direccion }}"
                                        data-email="{{ $proveedor->email }}"
                                        data-nom_preventista="{{ $proveedor->nombre_preventista }}"
                                        data-num_preventista="{{ $proveedor->num_preventista }}"
                                        data-estado="{{ $proveedor->estado }}">
                                        Modificar
                                        </button>
                                        
                                        <form class="m-0 d-inline" action="{{route('proveedores.borrar' , ['id_proveedor' => $proveedor->id_proveedor])}}" method="POST" onsubmit="return confirm('¿Eliminar proveedor?')">
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
    </div>

    <div class="col-lg-4 right-column">
        @include('parciales.columna_derecha')
    </div>
</div>



<!-- Modal agregar proveedor -->
<div class="modal fade" id="veragregarproveedor" tabindex="-1" aria-labelledby="veragregarproveedorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark">
            <form action="{{ route('proveedores.agregar') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="veragregarLabel">Agregar Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="int" class="form-control" id="telefono" name="telefono" required>
                    </div>
                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="direccion" name="direccion" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="nom_preventista" class="form-label">nombre preventista</label>
                        <input type="text" class="form-control" id="nom_preventista" name="nom_preventista" required>
                    </div>
                    <div class="mb-3">
                        <label for="num_preventista" class="form-label">Numero preventista</label>
                        <input type="int" class="form-control" id="num_preventista" name="num_preventista" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Agregar Proveedor</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal modificar proveedor -->
<div class="modal fade" id="vermodificarproveedor" tabindex="-1" aria-labelledby="vermodificarproveedorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark">
            <form action="{{ route('proveedores.modificar') }}" method="GET">
                @csrf
                <input type="hidden" name="modal_id_proveedor" id="modal_id_proveedor">
                <div class="modal-header">
                    <h5 class="modal-title" id="vermodificarproveedorLabel">Modificar Proveedor</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modal_nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="modal_nombre" name="modal_nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="modal_telefono" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="modal_telefono" name="modal_telefono" required>
                    </div>
                    <div class="mb-3">
                        <label for="modal_direccion" class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="modal_direccion" name="modal_direccion" required>
                    </div>
                    <div class="mb-3">
                        <label for="modal_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="modal_email" name="modal_email" required>
                    </div>
                    <div class="mb-3">
                        <label for="modal_nom_preventista" class="form-label">nombre preventista</label>
                        <input type="text" class="form-control" id="modal_nom_preventista" name="modal_nom_preventista" required>
                    </div>
                    <div class="mb-3">
                        <label for="modal_num_preventista" class="form-label">numero del preventista</label>
                        <input type="text" class="form-control" id="modal_num_preventista" name="modal_num_preventista" required>
                    </div>
                    <div class="mb-3">
                        <label for="modal_estado" class="form-label">Estado</label>
                        <select id="modal_estado" class="form-control" name="modal_estado"  required>
                            <option value="activo">activo</option>
                            <option value="desactivado">desactivado</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Modificar Proveedor</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>




<script>
    document.addEventListener('DOMContentLoaded', function () {
        const vermodificarproveedorModal = document.getElementById('vermodificarproveedor');
        vermodificarproveedorModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
    
            const id = button.getAttribute('data-id');
            const nombre = button.getAttribute('data-nombre');
            const telefono = button.getAttribute('data-telefono');
            const direccion = button.getAttribute('data-direccion');
            const email = button.getAttribute('data-email');
            const nom_preventista = button.getAttribute('data-nom_preventista');
            const num_preventista = button.getAttribute('data-num_preventista');
            const estado = button.getAttribute('data-estado');
    
            vermodificarproveedorModal.querySelector('#modal_id_proveedor').value = id;
            vermodificarproveedorModal.querySelector('#modal_nombre').value = nombre;
            vermodificarproveedorModal.querySelector('#modal_telefono').value = telefono;
            vermodificarproveedorModal.querySelector('#modal_direccion').value = direccion;
            vermodificarproveedorModal.querySelector('#modal_email').value = email;
            vermodificarproveedorModal.querySelector('#modal_nom_preventista').value = nom_preventista;
            vermodificarproveedorModal.querySelector('#modal_num_preventista').value = num_preventista;
            vermodificarproveedorModal.querySelector('#modal_estado').value = estado;
        });
    });
</script>



@endsection


