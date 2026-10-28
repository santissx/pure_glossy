@extends('layouts.nav')

@section('title', 'Historial de Presupuestos')

@section('ladoizq')
<div class="row h-100">
    <div class="col-lg-8 d-flex flex-column">
        
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card mb-3 flex-grow-1 left-table position-relative">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title">Historial de Presupuestos</h5>
                
                <div class="table-responsive flex-grow-1 table-scrollhist">
                    <table class="table table-dark table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Vendedor</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($presupuestos as $presupuesto)
                            <tr>
                                <td>{{ $presupuesto->id_presupuesto }}</td>
                                <td>{{ $presupuesto->vendedor_name ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($presupuesto->fecha)->format('d/m/Y H:i') }}</td>
                                <td>{{ $presupuesto->nombre_cliente ?: 'Consumidor final' }} <br> <small>{{ $presupuesto->telefono_cliente }}</small></td>
                                <td>${{ number_format($presupuesto->monto_total, 2) }}</td>
                                <td>
                                    @if($presupuesto->estado === 'convertido')
                                        <span class="badge bg-success">Convertido</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pendiente</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        <button type="button" class="btn btn-sm btn-primary ver-detalle-presupuesto" 
                                            data-id="{{ $presupuesto->id_presupuesto }}" data-bs-toggle="modal" data-bs-target="#modalDetallePresupuesto">
                                            Ver Detalle
                                        </button>
                                        <a href="{{ route('presupuesto.imprimir', $presupuesto->id_presupuesto) }}" class="btn btn-sm btn-secondary">
                                            Imprimir
                                        </a>
                                        @if($presupuesto->estado !== 'convertido')
                                            <button type="button" class="btn btn-sm btn-info btn-actualizar-precio" 
                                                data-id="{{ $presupuesto->id_presupuesto }}" data-bs-toggle="modal" data-bs-target="#modalActualizarPrecios">
                                                <i class="fas fa-sync-alt"></i> Actualizar
                                            </button>
                                            <a href="{{ route('presupuesto.editar', $presupuesto->id_presupuesto) }}" class="btn btn-sm btn-warning">
                                                Editar
                                            </a>
                                            <a href="{{ route('historial.presupuestos.convertir', $presupuesto->id_presupuesto) }}" class="btn btn-sm btn-success">
                                                A Caja
                                            </a>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-danger btn-eliminar-presupuesto" 
                                            data-id="{{ $presupuesto->id_presupuesto }}" data-bs-toggle="modal" data-bs-target="#modalEliminarPresupuesto">
                                            ✕
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="filtros mb-3 p-3 bg-dark rounded border border-secondary">
                    <form method="GET" action="{{ route('views.historial_presupuestos') }}">
                        <label class="form-label text-white">Filtrar por rango de fechas</label>
                        <div class="input-group mb-3">
                            <input type="date" class="form-control" name="fechainicio" value="{{ request('fechainicio') }}">
                            <span class="input-group-text">a</span>
                            <input type="date" class="form-control" name="fechafin" value="{{ request('fechafin') }}">
                            <button type="submit" class="btn btn-primary">Aplicar</button>
                        </div>
                    </form>
                </div>
                
            </div>
        </div>
    </div>

    <div class="col-lg-4 right-column">
        @include('parciales.columna_derecha')
    </div>
</div>

{{-- Modal Ver Detalles --}}
<div class="modal fade" id="modalDetallePresupuesto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Detalle del Presupuesto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body table-responsive">
                <table class="table table-dark table-striped">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unit.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="tablaDetallesCuerpo"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Eliminar --}}
<div class="modal fade" id="modalEliminarPresupuesto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title">Eliminar Presupuesto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar este presupuesto? Esta acción no afectará tu stock ni tus ventas reales.</p>
                <form id="formEliminarPresupuesto" action="{{ route('historial.presupuestos.eliminar') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="id_presupuesto" id="id_presupuesto_eliminar">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger" form="formEliminarPresupuesto">Sí, Eliminar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Actualizar Precios --}}
<div class="modal fade" id="modalActualizarPrecios" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title">Actualizar Precios</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Seguro que deseas actualizar este presupuesto? El sistema buscará los precios de venta actuales y recalculará el total. La fecha del presupuesto cambiará a la de hoy.</p>
                <form id="formActualizarPrecios" action="" method="POST">
                    @csrf
                    @method('PUT')
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-info" form="formActualizarPrecios">Sí, Actualizar</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-actualizar-precio').forEach(btn => {
            btn.addEventListener('click', function () {
                document.getElementById('formActualizarPrecios').action = `/historial-presupuestos/actualizar-precios/${this.getAttribute('data-id')}`;
            });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Cargar detalles
        document.querySelectorAll('.ver-detalle-presupuesto').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                fetch(`/historial-presupuestos/detalle/${id}`)
                    .then(res => res.json())
                    .then(data => {
                        const tbody = document.getElementById('tablaDetallesCuerpo');
                        tbody.innerHTML = '';
                        data.forEach(prod => {
                            const subtotal = prod.cantidad * parseFloat(prod.precio);
                            tbody.innerHTML += `<tr>
                                <td>${prod.producto}</td>
                                <td>${prod.cantidad}</td>
                                <td>$${parseFloat(prod.precio).toFixed(2)}</td>
                                <td>$${subtotal.toFixed(2)}</td>
                            </tr>`;
                        });
                    });
            });
        });

        // Configurar modal de eliminar
        document.querySelectorAll('.btn-eliminar-presupuesto').forEach(btn => {
            btn.addEventListener('click', function () {
                document.getElementById('id_presupuesto_eliminar').value = this.getAttribute('data-id');
            });
        });
    });
</script>
@endsection