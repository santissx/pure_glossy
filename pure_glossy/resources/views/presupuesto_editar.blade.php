@extends('layouts.nav')
@section('title', 'Modificar Presupuesto')
@section('ladoizq')

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('presupuesto.actualizar', $presupuesto->id_presupuesto) }}">
        @csrf
        @method('PUT')
        <div class="row h-100">
            <div class="col-lg-8 d-flex flex-column">
                
                {{-- Tabla de productos seleccionados --}}
                <!-- 👇 ESTA ES LA ETIQUETA QUE FALTABA 👇 -->
                <div class="card mb-3 flex-grow-1 left-table position-relative">
                    
                    <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white border-bottom border-secondary py-3">
                        <h5 class="card-title text-warning mb-0">
                            <i class="fas fa-edit me-2"></i>Editando Presupuesto #{{ $presupuesto->id_presupuesto }}
                        </h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-warning fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#buscarPromocionModal">
                                <i class="fas fa-gift me-1"></i> Agregar Promoción
                            </button>
                            <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#buscarProductoPresupuestoModal">
                                <i class="fas fa-plus me-1"></i> Agregar Producto
                            </button>
                            <button type="submit" class="btn btn-success fw-bold">
                                <i class="fas fa-save me-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>

                    <div class="card-body d-flex flex-column">
                        <div class="table-responsive flex-grow-1 table-scroll">
                            <table class="table table-dark table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Cantidad</th>
                                        <th>Precio unitario</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="tablaPresupuesto"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- TARJETA: DATOS DEL CLIENTE Y TOTALES --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            
                            {{-- Datos del Cliente e Inputs --}}
                            <div class="col-12">
                                <h5 class="card-title mb-3">Datos del cliente</h5>
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-2 mb-md-0">
                                        <input type="text" name="nombre_cliente" class="form-control" value="{{ $presupuesto->nombre_cliente }}" placeholder="Nombre del cliente (opcional)">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="telefono_cliente" class="form-control" value="{{ $presupuesto->telefono_cliente }}" placeholder="Teléfono (opcional)">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <textarea name="observaciones" class="form-control bg-dark text-white border-secondary" rows="2" placeholder="Observaciones internas (Opcional)">{{ $presupuesto->observaciones }}</textarea>
                                    </div>
                                </div>
                                
                                <div class="row align-items-center">
                                    <div class="col-md-6 d-flex align-items-center mb-3 mb-md-0">
                                        <label for="descuento" class="me-2 mb-0 fw-bold">Descuento ($):</label>
                                        <input type="number" id="descuento" name="descuento" class="form-control w-50" value="{{ $presupuesto->descuento }}" step="0.01" min="0" oninput="calcularTotalPresupuesto()">
                                    </div>

                                    <div class="col-md-6 text-md-end">
                                        <div class="fs-4 fw-bold">
                                            <span>Total:</span>
                                            <span id="totalPresupuesto" class="text-warning">${{ number_format($presupuesto->monto_total, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div> {{-- Fin row interno --}}
                    </div>
                </div>
            </div>

            <div class="col-lg-4 right-column">
                @include('parciales.columna_derecha')
            </div>
        </div>
    </form>

    {{-- MODAL BUSCAR PRODUCTO --}}
    <div class="modal fade" id="buscarProductoPresupuestoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark text-white border border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Buscar Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="buscadorPresupuesto" class="form-control mb-3" placeholder="Buscar por nombre o código...">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Stock</th>
                                    <th>Precio</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="listaProductosPresupuesto">
                                @foreach ($productos as $p)
                                    @php $nombreEscapado = str_replace(['"', "'"], ['\\"', "\\'"], $p->nombre); @endphp
                                    <tr class="item-producto-presupuesto">
                                        <td>{{ $p->codigo ?: $p->codigo_barra }}</td>
                                        <td class="nombre-prod">{{ $p->nombre }}</td>
                                        <td>{{ $p->stock }}</td>
                                        <td>${{ number_format($p->precio_venta, 2) }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-success" onclick="agregarProductoPresupuesto('{{ $p->id_producto }}', '{{ $nombreEscapado }}', '{{ $p->codigo ?: $p->codigo_barra }}', '{{ $p->precio_venta }}')">
                                                Seleccionar
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL BUSCAR PROMOCIÓN --}}
    <div class="modal fade" id="buscarPromocionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title"><i class="fas fa-tags me-2"></i>Seleccionar Promoción</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="buscadorPromoEdit" class="form-control mb-3 bg-dark text-white border-secondary" placeholder="Buscar promoción por nombre...">
                    
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-dark table-hover align-middle mb-0">
                            <thead>
                                <tr class="border-secondary bg-dark text-white">
                                    <th class="border-secondary">Promoción</th>
                                    <th class="border-secondary">Productos Incluidos</th>
                                    <th class="border-secondary">Precio Combo</th>
                                    <th class="text-center border-secondary">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="listaPromocionesEdit">
                                @foreach ($promociones as $promo)
                                    @php $promoNomEscapado = str_replace(['"', "'"], ['\\"', "\\'"], $promo->nombre); @endphp
                                    <tr class="item-promo-edit border-secondary" data-nombre="{{ $promoNomEscapado }}">
                                        <td class="fw-bold border-secondary">{{ $promo->nombre }}</td>
                                        <td class="border-secondary">
                                            <ul class="mb-0 ps-3 small text-white-50">
                                                @foreach($promo->productos as $p)
                                                    <li><strong class="text-white">{{ $p->cantidad }}x</strong> {{ $p->nombre }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td class="fw-bold text-success border-secondary">${{ number_format($promo->precio, 2) }}</td>
                                        <td class="text-center border-secondary">
                                            <button type="button" class="btn btn-warning btn-sm fw-bold text-dark px-3" 
                                                    onclick="agregarPromocionPresupuesto('{{ $promo->id_promocion }}', '{{ $promoNomEscapado }}', '{{ $promo->precio }}')">
                                                <i class="fas fa-plus me-1"></i> Agregar
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Buscador interno del modal de EDICIÓN tolerante a múltiples palabras y tildes
        document.getElementById('buscadorPresupuesto').addEventListener('keyup', function() {
            // Convertimos la búsqueda a minúsculas, quitamos acentos y limpiamos espacios de más
            const filtroRaw = this.value
                .toLowerCase()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .trim()
                .replace(/\s+/g, ' ');
                
            const filas = document.querySelectorAll('.item-producto-presupuesto');

            if (filtroRaw === '') {
                filas.forEach(fila => fila.style.display = '');
                return;
            }

            // Dividimos los términos introducidos en un array de palabras individuales
            const palabrasFiltro = filtroRaw.split(' ');

            filas.forEach(fila => {
                // Obtenemos todo el contenido de la fila y removemos tildes para igualar la comparación
                const textoFila = fila.textContent
                    .toLowerCase()
                    .normalize("NFD")
                    .replace(/[\u0300-\u036f]/g, "");
                
                // Evaluamos que CADA una de las palabras buscadas existan dentro de la fila (.every)
                const coincideTodo = palabrasFiltro.every(palabra => textoFila.includes(palabra));
                
                // Mostramos u ocultamos la fila según la condición cruzada
                fila.style.display = coincideTodo ? '' : 'none';
            });
        });

        function agregarProductoPresupuesto(id, nombre, codigo, precio, cantidadInicial = 1) {
            const tbody = document.getElementById('tablaPresupuesto');
            if (document.getElementById(`cant_${id}`)) {
                alert('Este producto ya está. Modifique su cantidad.');
                return;
            }

            const subtotalLinea = (cantidadInicial * parseFloat(precio)).toFixed(2);
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${codigo}</td>
                <td>${nombre}<input type="hidden" name="productos[${id}][id_producto]" value="${id}"></td>
                <td><input type="number" id="cant_${id}" name="productos[${id}][cantidad]" class="form-control form-control-sm w-75" value="${cantidadInicial}" min="1" data-precio="${precio}" oninput="actualizarFilaPresupuesto(this)"></td>
                <td>$${parseFloat(precio).toFixed(2)}</td>
                <td class="total-linea">$${subtotalLinea}</td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarFilaPresupuesto(this)">✕</button></td>
            `;
            tbody.appendChild(tr);

            const modalElement = document.getElementById('buscarProductoPresupuestoModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            if(modal) modal.hide();
            calcularTotalPresupuesto();
        }

        function actualizarFilaPresupuesto(input) {
            const cant = parseInt(input.value) || 0;
            const prec = parseFloat(input.getAttribute('data-precio')) || 0;
            input.closest('tr').querySelector('.total-linea').textContent = '$' + (cant * prec).toFixed(2);
            calcularTotalPresupuesto();
        }

        function eliminarFilaPresupuesto(btn) {
            btn.closest('tr').remove();
            calcularTotalPresupuesto();
        }

        function calcularTotalPresupuesto() {
            let subtotal = 0;
            document.querySelectorAll('#tablaPresupuesto .total-linea').forEach(celda => {
                subtotal += parseFloat(celda.textContent.replace('$', '')) || 0;
            });
            const desc = parseFloat(document.getElementById('descuento').value) || 0;
            document.getElementById('totalPresupuesto').textContent = '$' + Math.max(0, subtotal - desc).toFixed(2);
        }

        // Buscador del modal de Promociones
    document.getElementById('buscadorPromoEdit').addEventListener('keyup', function() {
        const filtroRaw = this.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim().replace(/\s+/g, ' ');
        const filas = document.querySelectorAll('.item-promo-edit');

        if (filtroRaw === '') {
            filas.forEach(fila => fila.style.display = '');
            return;
        }
        const palabrasFiltro = filtroRaw.split(' ');
        filas.forEach(fila => {
            const textoFila = fila.getAttribute('data-nombre').toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            const coincideTodo = palabrasFiltro.every(palabra => textoFila.includes(palabra));
            fila.style.display = coincideTodo ? '' : 'none';
        });
    });

    function agregarPromocionPresupuesto(id, nombre, precio, cantidadInicial = 1) {
        const tbody = document.getElementById('tablaPresupuesto');
        if (document.getElementById(`cant_promo_${id}`)) {
            alert('Esta promoción ya está en la lista. Modifique su cantidad.');
            return;
        }

        const subtotalLinea = (cantidadInicial * parseFloat(precio)).toFixed(2);
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>PROMO</td>
            <td><strong>Promo:</strong> ${nombre}<input type="hidden" name="promociones[${id}][id_promocion]" value="${id}"></td>
            <td>
                <input type="number" id="cant_promo_${id}" name="promociones[${id}][cantidad]" class="form-control form-control-sm w-75" value="${cantidadInicial}" min="1" data-precio="${precio}" oninput="actualizarFilaPresupuesto(this)">
                <input type="hidden" name="promociones[${id}][precio]" value="${precio}">
            </td>
            <td>$${parseFloat(precio).toFixed(2)}</td>
            <td class="total-linea">$${subtotalLinea}</td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="eliminarFilaPresupuesto(this)">✕</button></td>
        `;
        tbody.appendChild(tr);

        const modalElement = document.getElementById('buscarPromocionModal');
        const modal = bootstrap.Modal.getInstance(modalElement);
        if(modal) modal.hide();
        calcularTotalPresupuesto();
    }

    // Auto-carga de los productos y promociones que el presupuesto ya tenía guardados
    document.addEventListener('DOMContentLoaded', function() {
        // Cargar Promociones Detectadas
        @foreach($promocionesDetectadas as $promo)
            @php 
                $nomPromo = str_replace(['"', "'"], ['\\"', "\\'"], $promo->nombre);
            @endphp
            agregarPromocionPresupuesto('{{ $promo->id_promocion }}', '{!! $nomPromo !!}', '{{ $promo->precio_guardado }}', {{ $promo->cantidad }});
        @endforeach

        // Cargar Productos Individuales
        @foreach($productosSeleccionados as $prod)
            @php 
                $cod = $prod->codigo ?: $prod->codigo_barra;
                $nom = str_replace(['"', "'"], ['\\"', "\\'"], $prod->nombre);
            @endphp
            agregarProductoPresupuesto('{{ $prod->id_producto }}', '{!! $nom !!}', '{{ $cod }}', '{{ $prod->precio_guardado }}', {{ $prod->cantidad }});
        @endforeach
    });
    </script>
@endsection