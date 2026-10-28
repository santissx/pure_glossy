@extends('layouts.nav')

@section('title', 'Generar Presupuesto')

@section('ladoizq')

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Formulario principal: Enmarca toda la estructura row/col --}}
    <form id="formPresupuestoPrincipal" method="POST" action="{{ route('presupuesto.generar') }}">
        @csrf
        <div class="row h-100">
            
            
            <div class="col-lg-8 d-flex flex-column">

                
                <div class="card mb-3 flex-grow-1 left-table position-relative">
                    <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white border-bottom border-secondary py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-invoice-dollar me-2"></i>Presupuesto
                        </h5>
                        <div class="d-flex gap-2">
                            {{-- Botón Agregar Promoción --}}
                            <button type="button" class="btn btn-warning fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#buscarPromocionModal">
                                <i class="fas fa-gift me-1"></i> Agregar Promoción
                            </button>

                            {{-- Botón Agregar Producto --}}
                            <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#buscarProductoPresupuestoModal">
                                <i class="fas fa-plus me-1"></i> Agregar Producto
                            </button>

                            {{-- Botón Generar Presupuesto --}}
                            <button type="submit" class="btn btn-success fw-bold">
                                <i class="fas fa-save me-1"></i> Generar Presupuesto
                            </button>
                        </div>
                    </div>

                    <div class="card-body d-flex flex-column">
                        <div class="table-responsive flex-grow-1 table-scroll" style="max-height: 350px; overflow-y: auto;">
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
                                <tbody id="tablaPresupuesto">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            
                            <div class="col-12">
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3 mb-md-0 border-end border-secondary">
                                        <h5 class="card-title mb-3"><i class="fas fa-file-alt me-2"></i>Detalles del Presupuesto</h5>
                                        <input type="text" name="titulo" class="form-control mb-2 bg-dark text-white border-secondary" value="{{ old('titulo') }}" placeholder="Título (ej: Obra Bº Centro) (Opcional)">
                                        <textarea name="observaciones" class="form-control bg-dark text-white border-secondary" rows="2" placeholder="Observaciones internas (Opcional)">{{ old('observaciones') }}</textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <h5 class="card-title mb-3"><i class="fas fa-user me-2"></i>Datos del cliente</h5>
                                        <input type="text" name="nombre_cliente" class="form-control mb-2 bg-dark text-white border-secondary" value="{{ old('nombre_cliente') }}" placeholder="Nombre del cliente (opcional)">
                                        <input type="text" name="telefono_cliente" class="form-control bg-dark text-white border-secondary" value="{{ old('telefono_cliente') }}" placeholder="Teléfono (opcional)">
                                    </div>
                                </div>
                            
                                <div class="row align-items-center bg-dark p-3 rounded border border-secondary mx-0">
                                    <div class="col-md-6 d-flex align-items-center mb-3 mb-md-0">
                                        <label for="descuento" class="me-2 mb-0 fw-bold">Descuento:</label>
                                        <div class="d-flex gap-1" style="width: 50%;">
                                            <input type="number" id="descuento" class="form-control bg-secondary text-white border-0" placeholder="0.00" step="0.01" min="0" oninput="calcularTotalPresupuesto()">
                                            
                                            <select id="tipo_descuento" name="tipo_descuento" class="form-select bg-secondary text-white border-0 w-auto" onchange="calcularTotalPresupuesto()">
                                                <option value="fijo" selected>$</option>
                                                <option value="porcentaje">%</option>
                                            </select>
                                        </div>
                                        <input type="hidden" id="descuento_final_pesos" name="descuento" value="0">
                                    </div>

                                    <div class="col-md-6 text-md-end">
                                        <div class="fs-4 fw-bold">
                                            <span>Total:</span>
                                            <span id="totalPresupuesto" class="text-success">$0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div> 
                    </div>
                </div>

            </div> 

            <div class="col-lg-4 right-column">
                @include('parciales.columna_derecha')
            </div>

        </div> 
    </form>

    {{-- MODAL: Buscar Producto para Presupuesto --}}
    <div class="modal fade" id="buscarProductoPresupuestoModal" tabindex="-1" aria-labelledby="buscarProductoPresupuestoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="buscarProductoPresupuestoModalLabel">Buscar Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                    @php
                                        // Escapamos tanto comillas simples como dobles para evitar fallos de sintaxis en el HTML inline de JS
                                        $nombreEscapado = str_replace(['"', "'"], ['\\"', "\\'"], $p->nombre);
                                    @endphp
                                    <tr class="item-producto-presupuesto">
                                        <td>{{ $p->codigo ?: $p->codigo_barra }}</td>
                                        <td class="nombre-prod">{{ $p->nombre }}</td>
                                        <td>{{ $p->stock }}</td>
                                        <td>${{ number_format($p->precio_venta, 2) }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-success" 
                                                onclick="agregarProductoPresupuesto('{{ $p->id_producto }}', '{{ $nombreEscapado }}', '{{ $p->codigo ?: $p->codigo_barra }}', '{{ $p->precio_venta }}')">
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

    <div class="modal fade" id="buscarPromocionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title"><i class="fas fa-tags me-2"></i>Seleccionar Promoción</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" id="inputBuscarPromoPresupuesto" class="form-control bg-dark text-white border-secondary" placeholder="Buscar promoción por nombre... (ej: combo lampara)" onkeyup="filtrarPromocionesPresupuesto()">
                    </div>

                    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                        <table class="table table-dark table-hover align-middle mb-0">
                            <thead>
                                <tr class="border-secondary bg-dark text-white">
                                    <th class="border-secondary">Promoción</th>
                                    <th class="border-secondary">Productos Incluidos</th>
                                    <th class="border-secondary">Precio Combo</th>
                                    <th class="text-center border-secondary">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($promociones as $promo)
                                <tr class="fila-promo-presupuesto border-secondary" data-nombre="{{ $promo->nombre }}">
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
                                                onclick='agregarPromocionAPresupuesto({{ json_encode($promo) }})'>
                                            <i class="fas fa-plus me-1"></i> Agregar
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4 border-secondary">
                                        No hay promociones activas disponibles.
                                    </td>
                                </tr>
                                @endforelse
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
        // Buscador interno del modal optimizado para múltiples palabras y tolerante a acentos (tildes)
        document.getElementById('buscadorPresupuesto').addEventListener('keyup', function() {
            // Convertimos a minúsculas, quitamos acentos y limpiamos múltiples espacios
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

            // Separamos por palabras individuales
            const palabrasFiltro = filtroRaw.split(' ');

            filas.forEach(fila => {
                // Obtenemos el texto de la fila y le removemos los acentos para una comparación justa
                const textoFila = fila.textContent
                    .toLowerCase()
                    .normalize("NFD")
                    .replace(/[\u0300-\u036f]/g, "");
                
                // CADA una de las palabras buscadas debe estar dentro del texto de la fila
                const coincideTodo = palabrasFiltro.every(palabra => textoFila.includes(palabra));
                
                fila.style.display = coincideTodo ? '' : 'none';
            });
        });

        // Agregar un producto a la tabla de presupuestos
        function agregarProductoPresupuesto(id, nombre, codigo, precio) {
            const tbody = document.getElementById('tablaPresupuesto');

            // Validar si el producto ya fue agregado antes
            if (document.getElementById(`cant_${id}`)) {
                alert('Este producto ya está en el presupuesto. Modifique su cantidad directamente.');
                return;
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${codigo}</td>
                <td>
                    ${nombre}
                    <input type="hidden" name="productos[${id}][id_producto]" value="${id}">
                </td>
                <td>
                    <input type="number" id="cant_${id}" name="productos[${id}][cantidad]" class="form-control form-control-sm w-75" 
                        value="1" min="1" data-precio="${precio}" oninput="actualizarFilaPresupuesto(this)">
                </td>
                <td>$${parseFloat(precio).toFixed(2)}</td>
                <td class="total-linea">$${parseFloat(precio).toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm" onclick="eliminarFilaPresupuesto(this)">✕</button>
                </td>
            `;
            tbody.appendChild(tr);

            // Cerrar el modal correctamente usando la API de Bootstrap
            const modalElement = document.getElementById('buscarProductoPresupuestoModal');
            const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
            modal.hide();

            // Limpiamos el buscador para la próxima apertura
            document.getElementById('buscadorPresupuesto').value = '';
            filas.forEach(fila => fila.style.display = '');

            calcularTotalPresupuesto();
        }

        // Modificar la cantidad en una fila
        function actualizarFilaPresupuesto(input) {
            const cantidad = parseInt(input.value) || 0;
            const precio = parseFloat(input.getAttribute('data-precio')) || 0;
            const fila = input.closest('tr');
            
            fila.querySelector('.total-linea').textContent = '$' + (cantidad * precio).toFixed(2);
            calcularTotalPresupuesto();
        }

        // Eliminar fila de la tabla
        function eliminarFilaPresupuesto(btn) {
            btn.closest('tr').remove();
            calcularTotalPresupuesto();
        }

        // Recalcular el total general
        function calcularTotalPresupuesto() {
            let subtotal = 0;
            const descuentoInput = parseFloat(document.getElementById('descuento').value) || 0;
            const tipoDescuento = document.getElementById('tipo_descuento').value;

            // Sumar subtotales de la tabla
            document.querySelectorAll('#tablaPresupuesto .total-linea').forEach(celda => {
                const valorTexto = celda.textContent.replace('$', '').trim();
                subtotal += parseFloat(valorTexto) || 0;
            });
            
            // Calcular el valor real a restar en pesos ($)
            let descuentoCalculado = 0;
            if (tipoDescuento === 'porcentaje') {
                descuentoCalculado = subtotal * (descuentoInput / 100);
            } else {
                descuentoCalculado = descuentoInput;
            }

            // Evitar que dé números negativos si el descuento supera el subtotal
            let totalFinal = subtotal - descuentoCalculado;
            if (totalFinal < 0) {
                totalFinal = 0;
                descuentoCalculado = subtotal;
            }

            // Guardar valor real en pesos para la base de datos
            document.getElementById('descuento_final_pesos').value = descuentoCalculado.toFixed(2);

            document.getElementById('totalPresupuesto').textContent = '$' + totalFinal.toFixed(2);
        }


        let promoIndexPresupuesto = 0;

        // Helper para normalizar acentos y minúsculas
        function normalizarTexto(texto) {
            if (!texto) return '';
            return texto.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
        }

        // Filtrar promociones dentro del modal de presupuestos
        function filtrarPromocionesPresupuesto() {
            const filtro = normalizarTexto(document.getElementById('inputBuscarPromoPresupuesto').value);
            const palabras = filtro.split(' ').filter(p => p !== '');
            const filas = document.querySelectorAll('.fila-promo-presupuesto');

            filas.forEach(fila => {
                const nombre = normalizarTexto(fila.getAttribute('data-nombre'));
                const coincide = palabras.every(palabra => nombre.includes(palabra));
                fila.style.display = coincide ? '' : 'none';
            });
        }

        // Agregar todos los productos de la promoción desglosados al presupuesto
        function agregarPromocionAPresupuesto(promo) {
            if (!promo || !promo.id_promocion) return;

            const rowId = `promo-row-${promo.id_promocion}`;
            const filaExistente = document.querySelector(`tr[data-row-id="${rowId}"]`);
            const precioCombo = parseFloat(promo.precio) || 0;

            if (filaExistente) {
                // Si la promo ya está en la tabla, aumentamos su cantidad
                const inputCant = filaExistente.querySelector('.cantidad');
                inputCant.value = parseInt(inputCant.value, 10) + 1;
                actualizarFilaPresupuesto(inputCant);
            } else {
                promoIndexPresupuesto++;
                const tablaPresupuesto = document.getElementById('tablaPresupuesto');
                const nuevaFila = document.createElement('tr');
                nuevaFila.setAttribute('data-row-id', rowId);

                nuevaFila.innerHTML = `
                    <td>PROMO</td>
                    <td>
                        <strong>Promo:</strong> ${promo.nombre}
                        <input type="hidden" name="promociones[${promoIndexPresupuesto}][id_promocion]" value="${promo.id_promocion}">
                        <input type="hidden" name="promociones[${promoIndexPresupuesto}][precio]" value="${precioCombo.toFixed(2)}">
                    </td>
                    <td>
                        <input type="number" name="promociones[${promoIndexPresupuesto}][cantidad]" value="1" min="1" 
                            class="form-control form-control-sm w-75 cantidad" data-precio="${precioCombo.toFixed(2)}" 
                            oninput="actualizarFilaPresupuesto(this)">
                    </td>
                    <td>$${precioCombo.toFixed(2)}</td>
                    <td class="total-linea">$${precioCombo.toFixed(2)}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="eliminarFilaPresupuesto(this)">✕</button>
                    </td>`;

                tablaPresupuesto.appendChild(nuevaFila);
            }

            calcularTotalPresupuesto();

            // Cerrar modal
            const modalElement = document.getElementById('buscarPromocionModal');
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.hide();
            }
        }
    </script>

    @if (session('nuevo_presupuesto_id'))
        <div class="modal fade" id="presupuestoGuardadoModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-dark text-white border border-secondary">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title">¡Presupuesto Generado con Éxito!</h5>
                    </div>
                    <div class="modal-body text-center">
                        <p class="mb-0">El presupuesto se ha guardado en el historial.</p>
                        <p class="mt-2">¿Deseás imprimir el comprobante ahora?</p>
                    </div>
                    <div class="modal-footer justify-content-center border-0">
                        <a href="{{ route('presupuesto.imprimir', ['id' => session('nuevo_presupuesto_id')]) }}" class="btn btn-success">
                            <i class="fas fa-print me-1"></i> Sí, imprimir
                        </a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            No, seguir trabajando
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modal = new bootstrap.Modal(document.getElementById('presupuestoGuardadoModal'));
                modal.show();
            });
        </script>
    @endif

@endsection