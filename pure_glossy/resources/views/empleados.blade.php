@auth
@if(Auth::user()->rol === 'administrador')
@extends('layouts.nav')

@section('title', 'Usuarios')

@section('ladoizq')
<div class="row h-100">
    <div class="col-lg-8 d-flex flex-column">
        <div class="card mb-3 flex-grow-1 left-table border-secondary">
            <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white border-bottom border-secondary py-3">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="fas fa-users me-2"></i>Usuarios
                </h5>
                <button type="button" class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#veragregarusuario">
                    <i class="fas fa-user-plus me-1"></i> Agregar Usuario
                </button>
            </div>
            <div class="card-body d-flex flex-column p-0">
                <div class="table-responsive flex-grow-1 table-scrollgr">
                    <table class="table table-dark table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $usuario)
                            <tr>
                                <td>{{ $usuario->id }}</td>
                                <td>{{ $usuario->name }}</td>
                                <td>{{ $usuario->email }}</td>
                                <td><span class="badge bg-secondary">{{ $usuario->rol }}</span></td>
                                <td>{{ $usuario->estado }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#vermodificarusuario" 
                                        data-id="{{ $usuario->id }}"
                                        data-name="{{ $usuario->name }}"
                                        data-email="{{ $usuario->email }}"
                                        data-rol="{{ $usuario->rol }}"
                                        data-estado="{{ $usuario->estado }}"
                                        onclick="document.getElementById('modal_id_usuario').value = {{ $usuario->id }}">Modificar</button>

                                        <a href="{{ route('empleados.sesiones', ['id' => $usuario->id]) }}" 
                                            class="btn btn-info btn-sm">
                                            Sesiones
                                        </a>

                                        <form class="m-0 d-inline" action="{{route('empleados.borrar' , ['id' => $usuario->id])}}" method="POST" onsubmit="return confirm('¿Eliminar usuario?')">
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
@endsection

<!-- Modal agregar usuario -->
<div class="modal fade" id="veragregarusuario" tabindex="-1" aria-labelledby="veragregarusuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark">
            <form action="{{ route('empleados.agregar') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="veragregarLabel">Agregar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                    <div class="mb-3">
                        <label for="rol" class="form-label">Rol</label>
                        <select id="rol" class="form-control" name="rol" required>
                            <option value="administrador">Administrador</option>
                            <option value="empleado">Empleado</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Agregar Usuario</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal modificar usuario -->
<div class="modal fade" id="vermodificarusuario" tabindex="-1" aria-labelledby="vermodificarusuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark">
            <form action="{{ route('empleados.modificar') }}" method="GET">
                @csrf
                <input type="hidden" name="id" id="modal_id_usuario">
                <div class="modal-header">
                    <h5 class="modal-title" id="vermodificarLabel">Modificar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="name" name="name"  required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"  required>
                    </div>
                    <div class="mb-3">
                        <label for="rol" class="form-label">Rol</label>
                        <select id="rol" class="form-control" name="rol"   required>
                            <option value="administrador">Administrador</option>
                            <option value="empleado">Empleado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select id="estado" class="form-control" name="estado"   required>
                            <option value="activo">activo</option>
                            <option value="desactivado">desactivado</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Modificar Usuario</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modificarusuarioModal = document.getElementById('vermodificarusuario');
        modificarusuarioModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const email = button.getAttribute('data-email');
            const rol = button.getAttribute('data-rol');
            const estado = button.getAttribute('data-estado');
            modificarusuarioModal.querySelector('#modal_id_usuario').value = id;
            modificarusuarioModal.querySelector('#name').value = name;
            modificarusuarioModal.querySelector('#email').value = email;
            modificarusuarioModal.querySelector('#rol').value = rol;
            modificarusuarioModal.querySelector('#estado').value = estado;
        });
    });
    </script>
@else
    <script>window.location = "{{ route('views.ventas') }}";</script>
@endif
@else
    <script>window.location = "{{ route('login') }}";</script>
@endauth