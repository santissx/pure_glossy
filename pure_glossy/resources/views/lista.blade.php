@extends('layouts.nav')

@section('title', 'Lista')

@section('ladoizq')


{{-- BLOQUE NUEVO: MUESTRA LOS ERRORES DE VALIDACIÓN (COMO EL CÓDIGO DUPLICADO) --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="w-100">
                    @foreach ($errors->all() as $error)
                        <p class="mb-0 fw-bold">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- BLOQUE NUEVO: MUESTRA LOS MENSAJES EXITOSOS --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <div class="fw-bold">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

<div class="row h-100">
    <div class="col-lg-8 d-flex flex-column">
        {{-- FORMULARIO DE BÚSQUEDA CON BOTÓN DE AGREGAR AL LADO --}}
        <form id="formBusquedaProductos" action="{{ route('views.lista') }}" method="GET" class="row g-2 mb-3">
            <div class="col-12 col-md-6">
                <input type="search" id="inputSearchProductos" name="search" class="form-control" placeholder="Busque su producto" value="{{ $search }}">
            </div>
            <div class="col-6 col-md-3">
                <button type="submit" class="btn btn-primary w-100">Buscar</button>
            </div>
            @if(Auth::check() && Auth::user()->rol === 'administrador')
                <div class="col-6 col-md-3">
                    <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#agregarModal">
                        <i class="fas fa-plus me-1"></i> Agregar
                    </button>
                </div>
            @endif
        </form>

        @if($productos->isEmpty())
            <div class="alert alert-info" role="alert">
                No se encontraron productos que coincidan con su búsqueda.
            </div>
        @else
            <div class="card mb-3 left-table">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Lista de Productos</h5>
                    
                    <div class="table-responsive table-scrolllis flex-grow-1">
                        <table class="table table-dark table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>id</th>
                                    <th>Nombre</th>
                                    <th>Codigo</th>
                                    <th>Codigo de barra</th>
                                    @if(Auth::check() && Auth::user()->rol === 'administrador')
                                    <th>Precio lista</th>
                                    @endif
                                    <th>Precio venta</th>
                                    <th>
                                        @php
                                            $nextSort = $sort === 'stock_asc' ? 'stock_desc' : 'stock_asc';
                                            $sortIcon = $sort === 'stock_asc' ? '▲' : ($sort === 'stock_desc' ? '▼' : '');
                                        @endphp
                                        <a href="{{ route('views.lista', ['search' => $search, 'sort' => $nextSort]) }}" style="color: inherit; text-decoration: none;">
                                            Stock {{ $sortIcon }}
                                        </a>
                                    </th>
                                    <th>Proveedor</th>
                                    <th>Categoria</th>
                                    <th>Estado</th>
                                    @if(Auth::user()->rol === 'administrador')
                                        <th style="text-align: center">acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($productos as $producto)
                                <tr>
                                    <td>{{ $producto->id_producto }}</td>
                                    <td>{{ $producto->nombre }}</td>
                                    <td>{{ $producto->codigo }}</td>
                                    <td>{{ $producto->codigo_barra }}</td>
                                    @if(Auth::check() && Auth::user()->rol === 'administrador')
                                    <td>{{ $producto->precio_lista }}</td>
                                    @endif
                                    <td>{{ $producto->precio_venta }}</td>
                                    <td>{{ $producto->stock }}</td>
                                    <td>{{ $producto->id_proveedor }} - {{ $producto->nombre_proveedor }}</td>
                                    <td>{{ $producto->id_categoria }} - {{ $producto->categoria }}</td>
                                    <td>{{ $producto->estado }}</td>
                                    
                                    @if(Auth::user()->rol === 'administrador')
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center align-items-center gap-2">
                                            <button type="button" class="btn btn-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#vermodificarproducModal"
                                                    data-id="{{ $producto->id_producto }}"
                                                    data-nombre="{{ $producto->nombre }}"
                                                    data-codigo="{{ $producto->codigo }}"
                                                    data-codigo_barra="{{ $producto->codigo_barra }}"
                                                    data-precio_lista="{{ $producto->precio_lista }}"
                                                    data-precio_venta="{{ $producto->precio_venta }}"
                                                    data-stock="{{ $producto->stock }}"
                                                    data-id_proveedor="{{ $producto->id_proveedor }}"
                                                    data-id_categoria="{{ $producto->id_categoria }}"
                                                    data-estado="{{ $producto->estado }}">
                                                Modificar
                                            </button>
                                            <form class="m-0 d-flex" action="{{ route('lista.borrar', ['id_producto' => $producto->id_producto, 'search' => $search, 'sort' => $sort]) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if(Auth::check() && Auth::user()->rol === 'administrador')
                        <div class="action-buttons d-flex flex-column flex-sm-row gap-2 mt-3">
                            <button type="button" class="btn btn-primary w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#aumentarPreciosventaModal">
                                Aumentar precios de venta por proveedor
                            </button>
                            <button type="button" class="btn btn-primary w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#aumentarPrecioslistaModal">
                                Aumentar precios de lista por proveedor
                            </button>
                        </div>
                    @endif
                </div>
            </div> 
        @endif

        <div class="d-grid d-sm-block mt-1 mb-3">
            <a href="{{ route('exportar.stock') }}" class="btn btn-success w-100 w-sm-auto">
                <i class="fas fa-file-excel me-2"></i> Exportar Inventario a Excel
            </a>
        </div>
    </div>

    <div class="col-lg-4 right-column">
        @include('parciales.columna_derecha')
    </div>
</div>
@endsection

<div class="modal fade" id="agregarModal" tabindex="-1" aria-labelledby="veragregarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-white">
            <form action="{{route ('lista.agregar')}}" method="POST">
                @csrf 
                <div class="modal-header">
                    <h5 class="modal-title" id="verlistaLabel">Agregar Producto</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="codigo" class="form-label">Código</label>
                        <input type="text" class="form-control" id="codigo" name="codigo" required>
                    </div>
                    <div class="mb-3">
                        <label for="codigo_barra" class="form-label">Código de Barra</label>
                        <input type="text" class="form-control" id="codigo_barra" name="codigo_barra" required>
                    </div>
                    <div class="mb-3">
                        <label for="precio_lista" class="form-label">Precio lista (costo)</label>
                        <input type="number" class="form-control" id="precio_lista" name="precio_lista" step="0.01" min="0" required oninput="calcularPrecioSugerido()">
                    </div>
                    <div class="mb-3">
                        <label for="ganancia_pct" class="form-label">% de ganancia</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="ganancia_pct" step="1" min="0" placeholder="Ej: 40" oninput="calcularPrecioSugerido()">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="mb-3" id="sugerido_container" style="display: none;">
                        <div class="alert alert-info d-flex justify-content-between align-items-center py-2 mb-0">
                            <span>Precio sugerido: <strong id="precio_sugerido_valor">$0</strong></span>
                            <button type="button" class="btn btn-sm btn-info" onclick="usarPrecioSugerido()">
                                Usar este precio
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="precio_venta" class="form-label">Precio venta</label>
                        <input type="number" class="form-control" id="precio_venta" name="precio_venta" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="id_proveedor" class="form-label">Proveedor</label>
                        <select class="form-control" id="id_proveedor" name="id_proveedor" required>
                            <option value="" disabled selected>Seleccione un proveedor</option>
                            @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->id_proveedor }}">
                                    {{ $proveedor->id_proveedor }} - {{ $proveedor->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="id_categoria" class="form-label">categoria</label>
                        <select class="form-control" id="id_categoria" name="id_categoria" required>
                            <option value="" disabled selected>Seleccione una categoria</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id_categoria }}">
                                    {{ $categoria->id_categoria }} - {{ $categoria->categoria }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="stock" class="form-label">Stock</label>
                        <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Guardar Producto</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="vermodificarproducModal" tabindex="-1" aria-labelledby="vermodificarproducLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-white">
            <form action="{{ route('lista.modificar', ['search' => $search, 'sort' => $sort]) }}" method="POST">
                @csrf 
                <input type="hidden" name="id_producto" id="id_producto">
                <div class="modal-header">
                    <h5 class="modal-title" id="verlistaLabel">Modificar Producto</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="codigo" class="form-label">Código</label>
                        <input type="text" class="form-control" id="codigo" name="codigo" required>
                    </div>
                    <div class="mb-3">
                        <label for="codigo_barra" class="form-label">Código de Barra</label>
                        <input type="text" class="form-control" id="codigo_barra" name="codigo_barra" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="mod_precio_lista" class="form-label">Precio lista (costo)</label>
                        <input type="number" class="form-control" id="mod_precio_lista" name="precio_lista" step="0.01" min="0" required oninput="calcularPrecioSugeridoMod()">
                    </div>
                    
                    <div class="mb-3">
                        <label for="mod_ganancia_pct" class="form-label">% de ganancia (sugerencia)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="mod_ganancia_pct" step="1" min="0" placeholder="Ej: 40" oninput="calcularPrecioSugeridoMod()">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="mb-3" id="mod_sugerido_container" style="display: none;">
                        <div class="alert alert-info d-flex justify-content-between align-items-center py-2 mb-0">
                            <span>Precio sugerido: <strong id="mod_precio_sugerido_valor">$0</strong></span>
                            <button type="button" class="btn btn-sm btn-info" onclick="usarPrecioSugeridoMod()">
                                Usar este precio
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="mod_precio_venta" class="form-label">Precio venta</label>
                        <input type="number" class="form-control" id="mod_precio_venta" name="precio_venta" step="0.01" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="id_proveedor" class="form-label">Proveedor</label>
                        <select class="form-control" id="id_proveedor" name="id_proveedor" required>
                            <option value="" disabled selected>Seleccione un proveedor</option>
                            @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->id_proveedor }}">
                                    {{ $proveedor->id_proveedor }} - {{ $proveedor->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="id_categoria" class="form-label">categoria</label>
                        <select class="form-control" id="id_categoria" name="id_categoria" required>
                            <option value="" disabled selected>Seleccione una categoria</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id_categoria }}">
                                    {{ $categoria->id_categoria }} - {{ $categoria->categoria }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="stock" class="form-label">Stock</label>
                        <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select text-white bg-secondary" name="estado" id='estado'>
                            <option value="activo">Activo</option>
                            <option value="desactivado">Desactivado</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Modificar Producto</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="aumentarPreciosventaModal" tabindex="-1" aria-labelledby="aumentarPreciosventaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title" id="aumentarPreciosventaModalLabel">Aumentar precios de venta por proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="{{ route('productos.aumentarPrecio') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="id_proveedor" class="form-label">Proveedor</label>
                        <select class="form-select" name="id_proveedor" id="id_proveedor" required>
                            @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->id_proveedor }}">{{ $proveedor->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="porcentaje" class="form-label">Porcentaje de aumento (%)</label>
                        <input type="number" class="form-control" name="porcentaje" id="porcentaje" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Aplicar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="aumentarPrecioslistaModal" tabindex="-1" aria-labelledby="aumentarPrecioslistaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title" id="aumentarPrecioslistaModalLabel">Aumentar precios de lista por proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="{{ route('productos.aumentarPreciolista') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="id_proveedor" class="form-label">Proveedor</label>
                        <select class="form-select" name="id_proveedor" id="id_proveedor" required>
                            @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->id_proveedor }}">{{ $proveedor->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="porcentaje" class="form-label">Porcentaje de aumento (%)</label>
                        <input type="number" class="form-control" name="porcentaje" id="porcentaje" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Aplicar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const vermodificarproducModal = document.getElementById('vermodificarproducModal');
        vermodificarproducModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
    
            const id_producto = button.getAttribute('data-id');
            const nombre = button.getAttribute('data-nombre');
            const codigo = button.getAttribute('data-codigo');
            const codigo_barra = button.getAttribute('data-codigo_barra');
            const precio_lista = button.getAttribute('data-precio_lista');
            const precio_venta = button.getAttribute('data-precio_venta');
            const stock = button.getAttribute('data-stock');
            const id_proveedor = button.getAttribute('data-id_proveedor');
            const id_categoria = button.getAttribute('data-id_categoria');
            const estado = button.getAttribute('data-estado');
    
            vermodificarproducModal.querySelector('#id_producto').value = id_producto;
            vermodificarproducModal.querySelector('#nombre').value = nombre;
            vermodificarproducModal.querySelector('#codigo').value = codigo;
            vermodificarproducModal.querySelector('#codigo_barra').value = codigo_barra;
            
            vermodificarproducModal.querySelector('#mod_precio_lista').value = precio_lista;
            vermodificarproducModal.querySelector('#mod_precio_venta').value = precio_venta;
            
            vermodificarproducModal.querySelector('#stock').value = stock;
            vermodificarproducModal.querySelector('#id_proveedor').value = id_proveedor;
            vermodificarproducModal.querySelector('#id_categoria').value = id_categoria;
            vermodificarproducModal.querySelector('#estado').value = estado;

            document.getElementById('mod_ganancia_pct').value = '';
            document.getElementById('mod_sugerido_container').style.display = 'none';
        });

        // Limpieza de espacios extras antes de enviar el formulario de búsqueda nativo
        document.getElementById('formBusquedaProductos').addEventListener('submit', function(e) {
            const input = document.getElementById('inputSearchProductos');
            if(input) {
                input.value = input.value.trim().replace(/\s+/g, ' ');
            }
        });
    });

    // --- FUNCIONES PARA EL MODAL "AGREGAR" ---
    function calcularPrecioSugerido() {
        const costo = parseFloat(document.getElementById('precio_lista').value);
        const pct = parseFloat(document.getElementById('ganancia_pct').value);
        const container = document.getElementById('sugerido_container');

        if (!isNaN(costo) && !isNaN(pct) && costo > 0 && pct >= 0) {
            const sugerido = costo * (1 + pct / 100);
            document.getElementById('precio_sugerido_valor').textContent = '$' + sugerido.toFixed(2);
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
        }
    }

    function usarPrecioSugerido() {
        const costo = parseFloat(document.getElementById('precio_lista').value);
        const pct = parseFloat(document.getElementById('ganancia_pct').value);

        if (!isNaN(costo) && !isNaN(pct)) {
            const sugerido = costo * (1 + pct / 100);
            document.getElementById('precio_venta').value = sugerido.toFixed(2);
        }
    }

    // --- FUNCIONES PARA EL MODAL "MODIFICAR" ---
    function calcularPrecioSugeridoMod() {
        const costo = parseFloat(document.getElementById('mod_precio_lista').value);
        const pct = parseFloat(document.getElementById('mod_ganancia_pct').value);
        const container = document.getElementById('mod_sugerido_container');

        if (!isNaN(costo) && !isNaN(pct) && costo > 0 && pct >= 0) {
            const sugerido = costo * (1 + pct / 100);
            document.getElementById('mod_precio_sugerido_valor').textContent = '$' + sugerido.toFixed(2);
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
        }
    }

    function usarPrecioSugeridoMod() {
        const costo = parseFloat(document.getElementById('mod_precio_lista').value);
        const pct = parseFloat(document.getElementById('mod_ganancia_pct').value);

        if (!isNaN(costo) && !isNaN(pct)) {
            const sugerido = costo * (1 + pct / 100);
            document.getElementById('mod_precio_venta').value = sugerido.toFixed(2);
        }
    }
</script>