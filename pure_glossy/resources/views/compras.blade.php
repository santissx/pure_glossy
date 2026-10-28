@extends('layouts.nav')

@section('title', 'Compras')

@section('ladoizq')
    <div class="row h-100">
        <div class="col-lg-8 d-flex flex-column">
            <!-- Cuadro de Compras -->
            <div class="card mb-3 flex-grow-1 left-table border-secondary">
                <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white border-bottom border-secondary py-3">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-shopping-bag me-2"></i>Lista de Compras
                    </h5>
                    <button type="button" class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#agregarCompraModal">
                        <i class="fas fa-plus me-1"></i> Agregar Compra
                    </button>
                </div>
                <div class="card-body d-flex flex-column p-0">
                    <div class="table-responsive flex-grow-1 table-scrollcom">
                        <table class="table table-dark table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>ID Compra</th>
                                    <th>Monto Compra</th>
                                    <th>Fecha (A-M-D)</th>
                                    <th>Productos</th>
                                    <th>Proveedor</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($compras as $idCompra => $compraGroup)
                                    @php
                                        $compra = $compraGroup->first();
                                    @endphp
                                    <tr>
                                        <td>{{ $compra->id_compra }}</td>
                                        <td class="fw-bold text-success">${{ number_format($compra->monto_compra, 2) }}</td>
                                        <td>{{ $compra->fecha }}</td>
                                        <td>
                                            <select class="onlyread styled-select">
                                                @foreach ($compraGroup as $producto)
                                                    <option>
                                                        {{ $producto->producto }} ({{ $producto->proveedor ?: 'Sin proveedor' }}) - Cant: {{ $producto->cantidad_agregada }} - P.Unit: ${{ number_format($producto->precio_unitario ?? 0, 2) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        @php
                                            $provsUnicos = $compraGroup->pluck('proveedor')->filter()->unique();
                                        @endphp
                                        <td>
                                            @if($provsUnicos->count() > 1)
                                                <span class="badge bg-info text-dark" title="{{ $provsUnicos->implode(', ') }}">Varios ({{ $provsUnicos->count() }})</span>
                                            @elseif($provsUnicos->count() === 1)
                                                {{ $provsUnicos->first() }}
                                            @else
                                                Sin proveedor
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <form action="{{ route('compras.eliminar', $compra->id_compra) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar esta compra?')">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-dark border-top border-secondary py-2 text-end">
                    <strong class="text-white small">Gasto en compras total: <span class="text-success font-monospace fs-6">${{ number_format($totalcompras, 2) }}</span></strong>
                </div>
            </div>

            <div class="filtros mb-3">
                <form method="GET" action="{{ route('views.compras') }}">

                    <label for="selectproveedor" class="form-label">Filtrar por proveedor</label>
                    <select class="form-select" id="selectproveedor" name="proveedor">
                        <option value="">Selecciona un proveedor</option>
                        @foreach ($proveedores as $proveedor)
                            <option value="{{ $proveedor->id_proveedor }}">{{ $proveedor->nombre }}</option>
                        @endforeach
                    </select>

                    <label for="rango" class="form-label">Filtrar por rango de fechas</label>
                    <div class="input-group">
                        <input type="date" class="form-control" name="fechainicio" placeholder="Fecha inicio">
                        <span class="input-group-text">a</span>
                        <input type="date" class="form-control" name="fechafin" placeholder="Fecha fin">
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">Aplicar filtros</button>

                        <a href="{{ route('exportar.compras', request()->query()) }}" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Exportar Resultados a Excel
                        </a>
                    </div>
                </form>
            </div>

        </div>

        <div class="col-lg-4 right-column">
            @include('parciales.columna_derecha')
        </div>
    </div>

    <!-- Modal Agregar Compra -->
    <div class="modal fade" id="agregarCompraModal" tabindex="-1" aria-labelledby="agregarCompraLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark text-white border-secondary">
                <form action="{{ route('compras.agregar') }}" method="POST" id="formAgregarCompra">
                    @csrf
                    <div class="modal-header border-bottom border-secondary">
                        <h5 class="modal-title" id="agregarCompraLabel"><i class="fas fa-shopping-bag me-2"></i>Agregar Compra</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="monto" class="form-label fw-bold">Monto total de la compra ($)</label>
                            <input type="number" step="0.01" class="form-control bg-dark text-white border-secondary" id="monto" name="monto" required placeholder="Ej: 1500.00">
                        </div>

                        <!-- Select Proveedor -->
                        <div class="mb-3">
                            <label for="id_proveedor" class="form-label fw-bold">Filtrar Productos por Proveedor (Opcional)</label>
                            <select id="id_proveedor" class="form-select bg-dark text-white border-secondary" name="id_proveedor" onchange="buscarProductosCompra()">
                                <option value="todos" selected>Todos / Múltiples proveedores</option>
                                @foreach ($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id_proveedor }}">{{ $proveedor->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="card p-3 bg-dark border border-secondary mb-3">
                            <h6 class="fw-bold mb-2"><i class="fas fa-search me-1"></i> Buscar Productos para la Compra</h6>
                            
                            <div class="mb-2">
                                <input type="text" id="inputBuscarProductoCompra" class="form-control bg-dark text-white border-secondary" placeholder="Escribe para buscar por nombre o código..." oninput="buscarProductosCompra()">
                            </div>

                            <div class="table-responsive border border-secondary rounded mb-3" style="max-height: 180px; overflow-y: auto;">
                                <table class="table table-dark table-hover table-sm mb-0 align-middle">
                                    <thead>
                                        <tr class="bg-dark text-white border-bottom border-secondary">
                                            <th class="border-secondary bg-dark text-white">Producto</th>
                                            <th class="border-secondary bg-dark text-white">Proveedor</th>
                                            <th style="width: 80px;" class="border-secondary bg-dark text-white">Cant.</th>
                                            <th style="width: 100px;" class="border-secondary bg-dark text-white">P. Unitario ($)</th>
                                            <th style="width: 60px;" class="text-center border-secondary bg-dark text-white">Añadir</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaResultadosProductosCompra">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted small py-3 border-secondary">Escribe en el buscador para encontrar productos.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h6 class="fw-bold mb-2"><i class="fas fa-boxes me-1"></i> Productos en esta Compra</h6>
                            <div class="table-responsive border border-secondary rounded">
                                <table class="table table-dark table-striped mb-0 align-middle">
                                    <thead>
                                        <tr class="bg-dark text-white border-bottom border-secondary">
                                            <th class="border-secondary bg-dark text-white">Producto</th>
                                            <th class="border-secondary bg-dark text-white">Proveedor</th>
                                            <th style="width: 80px;" class="border-secondary bg-dark text-white">Cant.</th>
                                            <th style="width: 100px;" class="border-secondary bg-dark text-white">P. Unit.</th>
                                            <th style="width: 50px;" class="text-center border-secondary bg-dark text-white">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaProductosAgregadosCompra">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted small py-3 border-secondary">Aún no has agregado productos a esta compra.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success fw-bold"><i class="fas fa-save me-1"></i> Guardar Compra</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let productosCompra = [];
        let debounceTimerSearchCompra = null;

        function buscarProductosCompra() {
            clearTimeout(debounceTimerSearchCompra);
            debounceTimerSearchCompra = setTimeout(() => {
                const query = document.getElementById('inputBuscarProductoCompra').value;
                const proveedorId = document.getElementById('id_proveedor').value;

                fetch(`/compras/buscar-productos?q=${encodeURIComponent(query)}&proveedor=${encodeURIComponent(proveedorId)}`)
                    .then(response => response.json())
                    .then(data => {
                        const tbody = document.getElementById('tablaResultadosProductosCompra');
                        tbody.innerHTML = '';

                        if (data.length === 0) {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="5" class="text-center text-muted small py-3 border-secondary">No se encontraron productos.</td>
                                </tr>`;
                            return;
                        }

                        data.forEach(prod => {
                            const nombreEscapado = prod.nombre.replace(/'/g, "\\'").replace(/"/g, "&quot;");
                            const provNombreEscapado = (prod.proveedor_nombre || 'Sin proveedor').replace(/'/g, "\\'").replace(/"/g, "&quot;");
                            
                            // Toma el costo actual o precio base del producto
                            const precioSugerido = prod.precio_compra || prod.precio || 0;

                            tbody.innerHTML += `
                                <tr class="border-secondary">
                                    <td class="border-secondary">${prod.nombre}</td>
                                    <td class="border-secondary small text-white-50">${prod.proveedor_nombre || 'Sin proveedor'}</td>
                                    <td class="border-secondary">
                                        <input type="number" value="1" min="1" class="form-control form-control-sm bg-dark text-white border-secondary input-cant-compra-${prod.id_producto}">
                                    </td>
                                    <td class="border-secondary">
                                        <input type="number" step="0.01" value="${precioSugerido}" min="0" class="form-control form-control-sm bg-dark text-white border-secondary input-precio-compra-${prod.id_producto}">
                                    </td>
                                    <td class="text-center border-secondary">
                                        <button type="button" class="btn btn-primary btn-sm px-2 py-0" onclick="agregarProductoACompra(${prod.id_producto}, '${nombreEscapado}', '${provNombreEscapado}')">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </td>
                                </tr>`;
                        });
                    })
                    .catch(err => console.error('Error al buscar productos para compra:', err));
            }, 250);
        }

        function agregarProductoACompra(idProducto, nombre, proveedor) {
            const inputCant = document.querySelector(`.input-cant-compra-${idProducto}`);
            const inputPrecio = document.querySelector(`.input-precio-compra-${idProducto}`);
            
            const cantidad = parseInt(inputCant ? inputCant.value : 1);
            const precioUnitario = parseFloat(inputPrecio ? inputPrecio.value : 0);

            if (isNaN(cantidad) || cantidad <= 0) {
                alert("La cantidad debe ser al menos 1.");
                return;
            }

            if (isNaN(precioUnitario) || precioUnitario < 0) {
                alert("El precio unitario no es válido.");
                return;
            }

            const existe = productosCompra.find(item => item.id_producto == idProducto);
            if (existe) {
                existe.cantidad += cantidad;
                existe.precio_unitario = precioUnitario; // Actualiza con el último valor ingresado
            } else {
                productosCompra.push({
                    id_producto: idProducto,
                    nombre: nombre,
                    proveedor: proveedor,
                    cantidad: cantidad,
                    precio_unitario: precioUnitario
                });
            }

            if (inputCant) inputCant.value = '1';
            renderizarTablaProductosCompra();
        }

        function eliminarProductoCompra(index) {
            productosCompra.splice(index, 1);
            renderizarTablaProductosCompra();
        }

        function renderizarTablaProductosCompra() {
            const tbody = document.getElementById('tablaProductosAgregadosCompra');
            tbody.innerHTML = '';

            if (productosCompra.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-muted small py-3 border-secondary">Aún no has agregado productos a esta compra.</td>
                    </tr>`;
                return;
            }

            productosCompra.forEach((item, index) => {
                tbody.innerHTML += `
                    <tr class="border-secondary">
                        <td class="border-secondary">${item.nombre}
                            <input type="hidden" name="productos[${index}][id_producto]" value="${item.id_producto}">
                        </td>
                        <td class="border-secondary small text-white-50">${item.proveedor}</td>
                        <td class="border-secondary font-monospace fw-bold">${item.cantidad}
                            <input type="hidden" name="productos[${index}][cantidad]" value="${item.cantidad}">
                        </td>
                        <td class="border-secondary font-monospace fw-bold">$${item.precio_unitario.toFixed(2)}
                            <input type="hidden" name="productos[${index}][precio_unitario]" value="${item.precio_unitario}">
                        </td>
                        <td class="text-center border-secondary">
                            <button type="button" class="btn btn-danger btn-sm px-2 py-0" onclick="eliminarProductoCompra(${index})">&times;</button>
                        </td>
                    </tr>`;
            });
        }

        document.getElementById('formAgregarCompra').addEventListener('submit', function(e) {
            if (productosCompra.length === 0) {
                e.preventDefault();
                alert("Debes agregar al menos un producto a la compra.");
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            const modalCompra = document.getElementById('agregarCompraModal');
            if (modalCompra) {
                modalCompra.addEventListener('shown.bs.modal', () => {
                    buscarProductosCompra();
                });
            }
        });
    </script>
@endsection

