@extends('layouts.nav')

@section('title', 'Venta')

@section('ladoizq')
@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <p class="mb-0">{{ $error }}</p>
        @endforeach
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show fw-bold mb-3" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show fw-bold mb-3" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('cargar_presupuesto') || session('cargar_promociones_presupuesto') || session('id_presupuesto_convertido'))
    <div class="alert alert-info alert-dismissible fade show fw-bold mb-3 d-flex flex-wrap justify-content-between align-items-center gap-2" role="alert">
        <div>
            <i class="fas fa-file-invoice-dollar me-2"></i>
            Presupuesto {{ session('id_presupuesto_convertido') ? '#'.session('id_presupuesto_convertido') : '' }} cargado a Caja.
        </div>
        <a href="{{ route('views.historial_presupuestos') }}" class="btn btn-sm btn-dark text-white fw-bold">
            <i class="fas fa-arrow-left me-1"></i> Volver a Historial de Presupuestos
        </a>
    </div>
@endif
<form id="formVentaPrincipal" method="POST" action="{{ route('ventas.guardar') }}">
    @csrf
    <div class="row h-100">
        <div class="col-lg-8 d-flex flex-column">
            
            {{-- Tabla de productos de la venta --}}
            <div class="card mb-3 flex-grow-1 left-table position-relative">
                
               <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white border-bottom border-secondary py-3">
                <h5 class="card-title mb-0">
                    <i class="fas fa-shopping-cart me-2"></i>Venta
                </h5>
                <div class="d-flex gap-2">
                    {{-- Botón Agregar Promoción (a la izquierda) --}}
                    <button type="button" class="btn btn-warning fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#buscarPromocionModal">
                        <i class="fas fa-gift me-1"></i> Agregar Promoción
                    </button>

                    {{-- Botón Agregar Producto --}}
                    <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#buscarProductoModal">
                        <i class="fas fa-plus me-1"></i> Agregar Producto
                    </button>

                    {{-- Botón Guardar Venta --}}
                    <button type="submit" class="btn btn-success fw-bold">
                        <i class="fas fa-save me-1"></i> Guardar Venta
                    </button>
                </div>
            </div>

                <div class="card-body d-flex flex-column">
                    <div class="table-responsive flex-grow-1 table-scroll">
                        <table class="table table-dark table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Código del producto</th>
                                    <th>Nombre del producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Precio Total</th>
                                    <th class="text-center">Quitar</th> 
                                </tr>
                            </thead>
                            <tbody id="tablaVentas">
                                {{-- Los productos se cargan dinámicamente aquí --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            {{-- Sección de Totales y Filtros --}}
            <div class="filtros mb-3 p-3 bg-dark rounded border border-secondary">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-cash-register me-2"></i>Ventas Totales</h5>
                    
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <select class="form-select mb-3" name="metodo_pago" id="metodo_pago" required onchange="toggleClientesCorrientes()">
                                <option selected disabled value="">Método de pago</option>
                                @foreach($metodosDePago as $metodo)
                                    <option value="{{ $metodo->id_metodo_pago }}">{{ $metodo->nombre }}</option>
                                @endforeach
                            </select>

                            <div id="clientes_corrientes_container" style="display: none;">
                                <select class="form-select mb-3" name="id_cliente" id="id_cliente">
                                    <option selected disabled value="">Seleccione un cliente</option>
                                </select>
                            </div>

                            {{-- Campos opcionales para datos manuales u origen Presupuestos --}}
                            <div id="cliente_casual_container" class="mt-2">
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <input type="text" name="cliente_nombre" id="cliente_nombre" placeholder="Nombre cliente (Opcional)" class="form-control bg-dark text-white border-secondary">
                                    </div>
                                    <div class="col-6">
                                        <input type="text" name="cliente_telefono" id="cliente_telefono" placeholder="Teléfono (Opcional)" class="form-control bg-dark text-white border-secondary">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <textarea name="observaciones" id="observaciones" placeholder="Observaciones internas de la venta (Opcional)" rows="2" class="form-control bg-dark text-white border-secondary"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label for="descuento" class="me-2 mb-0 fw-bold">Descuento:</label>
                                <div class="d-flex gap-1" style="width: 50%;">
                                    <input type="number" id="descuento" class="form-control" placeholder="0.00" step="0.01" min="0" oninput="aplicarDescuento()">
                                    
                                    <select id="tipo_descuento" name="tipo_descuento" class="form-select bg-secondary text-white w-auto" onchange="aplicarDescuento()">
                                        <option value="fijo" selected>$</option>
                                        <option value="porcentaje">%</option>
                                    </select>
                                </div>
                                <input type="hidden" id="descuento_final_pesos" name="descuento" value="0">
                            </div>

                            <div class="d-flex justify-content-between fs-4 fw-bold mb-3">
                                <span>Total:</span>
                                <span id="totalVenta" class="text-success">$0.00</span>
                            </div>

                            <div class="mb-2">
                                <label for="monto_recibido" class="form-label fw-bold">
                                    Monto recibido:
                                </label>

                                <input
                                    type="number"
                                    id="monto_recibido"
                                    class="form-control"
                                    placeholder="Ingrese el monto recibido"
                                    min="0"
                                step="0.01"
                                oninput="calcularVuelto()"
                                >
                            </div>

                            <div class="d-flex justify-content-between align-items-center fs-5 fw-bold">
                                <span>Vuelto:</span>
                                <span id="vueltoVenta" class="text-info">$0.00</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
</form>

    <div class="col-lg-4 right-column">
        @include('parciales.columna_derecha')
        <div class="mt-4">
            <a href="{{ route('backup.db') }}" class="btn btn-outline-info w-100 shadow-sm d-flex justify-content-center align-items-center gap-2">
                <i class="fas fa-database"></i> Descargar Backup Completo
            </a>
        </div>

        <div class="mt-2">
            <a href="{{ route('mis.sesiones') }}" class="btn btn-info w-100 shadow-sm d-flex justify-content-center align-items-center gap-2">
                <i class="fas fa-history"></i> Mis Sesiones
            </a>
        </div>
    </div>
</div>

{{-- MODAL: Buscar Producto --}}
<div class="modal fade" id="buscarProductoModal" tabindex="-1" aria-labelledby="buscarProductoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-white border border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="buscarProductoModalLabel">Buscar Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="buscarProductoInput" class="form-control mb-3" placeholder="Buscar por nombre o código...">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-dark table-striped">
                        <thead>
                            <tr>
                                <th>Código de barra</th>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resultadosProductos">
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
                    <input type="text" id="inputBuscarPromoVenta" class="form-control bg-dark text-white border-secondary" placeholder="Buscar promoción por nombre..." onkeyup="filtrarPromocionesVenta()">
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
                        <tbody id="tablaPromocionesVenta">
                            @forelse($promociones as $promo)
                            <tr class="fila-promo-venta border-secondary" data-nombre="{{ $promo->nombre }}">
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
                                            onclick='agregarPromocionAVenta({{ json_encode($promo) }})'>
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

@if (session('nueva_venta_id'))
    <div class="modal fade" id="ticketModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">¡Venta Procesada con éxito!</h5>
                </div>
                <div class="modal-body text-center">
                    <p class="mb-0">¿Deseás imprimir o visualizar el ticket de esta venta?</p>
                </div>
                <div class="modal-footer justify-content-center border-0">
                    <a href="{{ route('ventas.ticket', ['idVenta' => session('nueva_venta_id')]) }}" class="btn btn-success">
                        <i class="fas fa-print me-1"></i> Sí, ver ticket
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        No, continuar vendiendo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ticketModal = new bootstrap.Modal(document.getElementById('ticketModal'));
            ticketModal.show();
        });
    </script>
@endif

<script>
    // Variable global para que cada fila del formulario tenga name="productos[itemIndex][...]" único
    let itemIndex = 0;

    // ==========================================
    // 0. FUNCIONES DE APOYO Y NORMALIZACIÓN
    // ==========================================
    function normalizarTexto(texto) {
        if (!texto) return '';
        return texto.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
    }

    // ==========================================
    // 1. BÚSQUEDA Y AGREGADO DE PRODUCTOS INDIVIDUALES (NORMAL)
    // ==========================================
    document.getElementById('buscarProductoInput').addEventListener('input', function () {
        const termino = this.value;

        if (termino.length > 1) { 
            fetch(`/buscar-productos?q=${encodeURIComponent(termino)}`)
                .then(response => response.json())
                .then(data => {
                    const resultados = document.getElementById('resultadosProductos');
                    resultados.innerHTML = '';

                    data.forEach(producto => {
                        const fila = document.createElement('tr');
                        fila.innerHTML = `
                            <td>${producto.codigo_barra || '-'}</td>
                            <td>${producto.nombre}</td>
                            <td>$${parseFloat(producto.precio_venta).toFixed(2)}</td>
                            <td>${producto.stock}</td>
                            <td>
                                <button type="button" class="btn btn-success btn-sm agregar-producto" data-id="${producto.id_producto}">
                                    Agregar
                                </button>
                            </td>
                        `;
                        resultados.appendChild(fila);
                    });
                })
                .catch(error => console.error('Error en la búsqueda:', error));
        }
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('agregar-producto')) {
            const fila = e.target.closest('tr');
            const id_producto = e.target.dataset.id;
            if (!id_producto) return;
            
            const codigo_barra = fila.cells[0].textContent.trim();
            const nombre = fila.cells[1].textContent.trim();
            const precio = parseFloat(fila.cells[2].textContent.replace('$', '').trim());
            const stock = parseInt(fila.cells[3].textContent.trim());

            // Identificador exclusivo para productos vendidos a precio lista
            const rowId = `normal-${id_producto}`;
            const filaExistente = document.querySelector(`tr[data-row-id="${rowId}"]`);

            if (filaExistente) {
                const inputCant = filaExistente.querySelector('.cantidad');
                let nuevaCant = parseInt(inputCant.value) + 1;
                inputCant.value = nuevaCant;
                actualizarTotal(inputCant);
            } else {
                itemIndex++;
                const tablaVentas = document.getElementById('tablaVentas');
                const nuevaFila = document.createElement('tr');
                nuevaFila.setAttribute('data-row-id', rowId);

                nuevaFila.innerHTML = `
                    <td>${codigo_barra}</td>
                    <td>${nombre}</td>
                    <td>
                        <input type="number" name="productos[${itemIndex}][cantidad]" value="1" min="1" class="form-control cantidad" data-precio="${precio}" data-stock="${stock}" oninput="actualizarTotal(this)">
                        <input type="hidden" name="productos[${itemIndex}][id_producto]" value="${id_producto}">
                        <input type="hidden" name="productos[${itemIndex}][precio]" value="${precio}">
                    </td>
                    <td>$${precio.toFixed(2)}</td>
                    <td class="precio-total">$${precio.toFixed(2)}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm px-2 py-1" onclick="quitarProductoDelCarrito(this)">×</button>
                    </td>`;
                tablaVentas.appendChild(nuevaFila);
            }

            const modal = bootstrap.Modal.getInstance(document.getElementById('buscarProductoModal'));
            if (modal) modal.hide();

            calcularTotalVenta();
        }
    });

    // ==========================================
    // 2. LÓGICA DE PROMOCIONES EN VENTA
    // ==========================================
    function filtrarPromocionesVenta() {
        const filtro = normalizarTexto(document.getElementById('inputBuscarPromoVenta').value);
        const palabras = filtro.split(' ').filter(p => p !== '');
        const filas = document.querySelectorAll('.fila-promo-venta');

        filas.forEach(fila => {
            const nombre = normalizarTexto(fila.getAttribute('data-nombre'));
            const coincide = palabras.every(palabra => nombre.includes(palabra));
            fila.style.display = coincide ? '' : 'none';
        });
    }

    let promoIndex = 0; // Índice único para el array promociones[]

    function agregarPromocionAVenta(promo) {
        if (!promo || !promo.id_promocion) return;

        const rowId = `promo-row-${promo.id_promocion}`;
        const filaExistente = document.querySelector(`tr[data-row-id="${rowId}"]`);
        const precioCombo = parseFloat(promo.precio) || 0;

        if (filaExistente) {
            // Si ya está la promo en la tabla, aumentamos su multiplicador
            const inputCant = filaExistente.querySelector('.cantidad');
            inputCant.value = parseInt(inputCant.value) + 1;
            actualizarTotal(inputCant);
        } else {
            promoIndex++;
            const tablaVentas = document.getElementById('tablaVentas');
            const nuevaFila = document.createElement('tr');
            nuevaFila.setAttribute('data-row-id', rowId);

            nuevaFila.innerHTML = `
                <td>PROMO</td>
                <td>
                    <strong>Promo:</strong> ${promo.nombre}
                </td>
                <td>
                    <input type="number" name="promociones[${promoIndex}][cantidad]" value="1" min="1" class="form-control cantidad" data-precio="${precioCombo.toFixed(2)}" oninput="actualizarTotal(this)">
                    <input type="hidden" name="promociones[${promoIndex}][id_promocion]" value="${promo.id_promocion}">
                    <input type="hidden" name="promociones[${promoIndex}][precio]" value="${precioCombo.toFixed(2)}">
                </td>
                <td>$${precioCombo.toFixed(2)}</td>
                <td class="precio-total">$${precioCombo.toFixed(2)}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm px-2 py-1" onclick="quitarProductoDelCarrito(this)">×</button>
                </td>`;

            tablaVentas.appendChild(nuevaFila);
        }

        calcularTotalVenta();

        // Cerrar modal
        const modalElement = document.getElementById('buscarPromocionModal');
        const modalInstance = bootstrap.Modal.getInstance(modalElement);
        if (modalInstance) {
            modalInstance.hide();
        }
    }

    // ==========================================
    // 3. OPERACIONES EN LA TABLA DE VENTAS Y CÁLCULOS
    // ==========================================
    function quitarProductoDelCarrito(boton) {
        const fila = boton.closest('tr');
        if (fila) {
            fila.remove();
            calcularTotalVenta();
        }
    }

    function actualizarTotal(input) {
        const cantidad = parseInt(input.value, 10) || 0;
        const precioUnitario = parseFloat(input.getAttribute('data-precio')) || 0;
        const precioTotal = cantidad * precioUnitario;

        const fila = input.closest('tr');
        fila.querySelector('.precio-total').textContent = `$${precioTotal.toFixed(2)}`;

        calcularTotalVenta();
    }

    function aplicarDescuento() {
        calcularTotalVenta();
    }

    function calcularTotalVenta() {
        let total = 0;
        const descuentoInput = parseFloat(document.getElementById('descuento').value) || 0;
        const tipoDescuento = document.getElementById('tipo_descuento').value;

        document.querySelectorAll('.precio-total').forEach(celda => {
            const precio = parseFloat(celda.textContent.replace('$', '').trim()) || 0;
            total += precio;
        });

        let descuentoCalculado = 0;
        if (tipoDescuento === 'porcentaje') {
            descuentoCalculado = total * (descuentoInput / 100);
        } else {
            descuentoCalculado = descuentoInput;
        }

        let totalConDescuento = total - descuentoCalculado;
        if (totalConDescuento < 0) {
            totalConDescuento = 0;
            descuentoCalculado = total; 
        }

        document.getElementById('descuento_final_pesos').value = descuentoCalculado.toFixed(2);

        const totalVentaElement = document.querySelector('#totalVenta');
        if (totalVentaElement) {
            totalVentaElement.textContent = `$${totalConDescuento.toFixed(2)}`;
        }

        calcularVuelto();
    }

    function calcularVuelto() {
        const montoRecibidoInput = document.getElementById('monto_recibido');
        const vueltoElement = document.getElementById('vueltoVenta');
        const totalVentaElement = document.getElementById('totalVenta');

        if (!montoRecibidoInput || !vueltoElement || !totalVentaElement) {
            return;
        }

        const montoRecibido = parseFloat(montoRecibidoInput.value) || 0;

        const totalTexto = totalVentaElement.textContent
            .replace('$', '')
            .trim();

        const totalVenta = parseFloat(totalTexto) || 0;

        const vuelto = montoRecibido - totalVenta;

        if (vuelto >= 0) {
            vueltoElement.textContent = `$${vuelto.toFixed(2)}`;
            vueltoElement.classList.remove('text-danger');
            vueltoElement.classList.add('text-info');
        } else {
            vueltoElement.textContent = '$0.00';
            vueltoElement.classList.remove('text-info');
            vueltoElement.classList.add('text-danger');
        }
    }

    // ==========================================
    // 4. VALIDACIÓN DEL FORMULARIO DE VENTA
    // ==========================================
    document.getElementById('formVentaPrincipal').addEventListener('submit', function (e) {
        const tablaVentas = document.getElementById('tablaVentas');
        const metodoPago = document.getElementById('metodo_pago').value;
        const idCliente = document.getElementById('id_cliente').value;
        const descuentoInput = parseFloat(document.getElementById('descuento').value) || 0;

        if (tablaVentas.children.length === 0) {
            e.preventDefault();
            alert('¡Atención! No podés guardar una venta vacía. Agregá al menos un producto al listado.');
            return;
        }

        let subtotalProductos = 0;
        document.querySelectorAll('.precio-total').forEach(celda => {
            const precio = parseFloat(celda.textContent.replace('$', '').trim()) || 0;
            subtotalProductos += precio;
        });

        const tipoDescuento = document.getElementById('tipo_descuento').value;
        let descuentoEnPesos = 0;

        if (tipoDescuento === 'porcentaje') {
            if (descuentoInput > 100) {
                e.preventDefault();
                alert('¡Atención! El porcentaje de descuento no puede ser superior al 100%.');
                document.getElementById('descuento').focus();
                return;
            }
            descuentoEnPesos = subtotalProductos * (descuentoInput / 100);
        } else {
            descuentoEnPesos = descuentoInput;
        }

        if (descuentoEnPesos > subtotalProductos) {
            e.preventDefault();
            alert('¡Atención! El descuento ingresado equivale a $' + descuentoEnPesos.toFixed(2) + ' y no puede ser mayor al total acumulado de los productos ($' + subtotalProductos.toFixed(2) + '). Por favor, corregilo.');
            document.getElementById('descuento').focus();
            return;
        }

        if (!metodoPago) {
            e.preventDefault();
            alert('¡Atención! Es obligatorio que selecciones un Método de pago válido para guardar el registro.');
            document.getElementById('metodo_pago').focus();
            return;
        }

        if (metodoPago === '3' && !idCliente) {
            e.preventDefault();
            alert('¡Atención! Seleccionaste la opción "Cliente Corriente". Por lo tanto, debes indicar el cliente titular de la cuenta obligatoriamente.');
            document.getElementById('id_cliente').focus();
            return;
        }
    });

    // ==========================================
    // 5. CLIENTES CORRIENTES & INICIALIZACIÓN
    // ==========================================
    function toggleClientesCorrientes() {
        const metodoPago = document.getElementById('metodo_pago');
        const clientesCorrientesContainer = document.getElementById('clientes_corrientes_container');
        const clienteCasualContainer = document.getElementById('cliente_casual_container');
        const selectClientes = document.getElementById('id_cliente');
        
        if (metodoPago && metodoPago.value === '3') { 
            clientesCorrientesContainer.style.display = 'block';
            clienteCasualContainer.style.display = 'none';
            selectClientes.setAttribute('required', 'required');
            cargarClientesCorrientes();
        } else if (clientesCorrientesContainer && clienteCasualContainer) {
            clientesCorrientesContainer.style.display = 'none';
            clienteCasualContainer.style.display = 'block';
            selectClientes.removeAttribute('required');
        }
    }

    function cargarClientesCorrientes() {
        fetch('/obtener-clientes-corrientes')
            .then(response => response.json())
            .then(data => {
                const selectClientes = document.getElementById('id_cliente');
                selectClientes.innerHTML = '<option selected disabled value="">Seleccione un cliente</option>';
                
                data.forEach(cliente => {
                    const option = document.createElement('option');
                    option.value = cliente.id_cliente;
                    option.textContent = `${cliente.nombre_y_apellido} - DNI: ${cliente.dni}`;
                    selectClientes.appendChild(option);
                });
            })
            .catch(error => console.error('Error:', error));
    }

    document.addEventListener('DOMContentLoaded', toggleClientesCorrientes);
</script>

{{-- Carga Automática desde Presupuesto (Si Aplica) --}}
@if(session('cargar_presupuesto') || session('cargar_promociones_presupuesto'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Cargar datos del cliente y descuento
            const descuentoPresupuesto = {{ session('descuento_presupuesto') ?? 0 }};
            const nombrePresupuesto = "{{ session('nombre_cliente') ?? '' }}";
            const telefonoPresupuesto = "{{ session('telefono_cliente') ?? '' }}";
            const observacionesPresupuesto = @json(session('observaciones_presupuesto') ?? '');

            const inputNombre = document.getElementById('cliente_nombre');
            if (inputNombre && nombrePresupuesto) inputNombre.value = nombrePresupuesto;
            
            const inputTelefono = document.getElementById('cliente_telefono');
            if (inputTelefono && telefonoPresupuesto) inputTelefono.value = telefonoPresupuesto;

            const inputObs = document.getElementById('observaciones');
            if (inputObs && observacionesPresupuesto) inputObs.value = observacionesPresupuesto;

            const inputDescuento = document.getElementById('descuento');
            if (inputDescuento) inputDescuento.value = descuentoPresupuesto;

            // 2. Cargar Promociones en un solo renglón
            @if(session('cargar_promociones_presupuesto'))
                const promocionesPresupuesto = @json(session('cargar_promociones_presupuesto'));

                promocionesPresupuesto.forEach(promo => {
                    // Agrega el renglón de la promoción usando la función existente
                    agregarPromocionAVenta({
                        id_promocion: promo.id_promocion,
                        nombre: promo.nombre,
                        precio: parseFloat(promo.precio)
                    });

                    // Si la cantidad en el presupuesto era mayor a 1, actualizamos el input
                    if (promo.cantidad && promo.cantidad > 1) {
                        const rowId = `promo-row-${promo.id_promocion}`;
                        const fila = document.querySelector(`tr[data-row-id="${rowId}"]`);
                        if (fila) {
                            const inputCant = fila.querySelector('.cantidad');
                            if (inputCant) {
                                inputCant.value = promo.cantidad;
                                actualizarTotal(inputCant);
                            }
                        }
                    }
                });
            @endif

            // 3. Cargar Productos Individuales
            @if(session('cargar_presupuesto'))
                const productosPresupuesto = @json(session('cargar_presupuesto'));
                const tablaVentas = document.getElementById('tablaVentas');

                productosPresupuesto.forEach(prod => {
                    itemIndex++;
                    const codigo = prod.codigo_barra || prod.codigo || '-';
                    const precio = parseFloat(prod.precio) || 0;
                    const cantidad = parseInt(prod.cantidad) || 1;
                    const stock = parseInt(prod.stock) || 0;
                    const precioTotal = precio * cantidad;

                    const rowId = `normal-${prod.id_producto}`;

                    const nuevaFila = document.createElement('tr');
                    nuevaFila.setAttribute('data-row-id', rowId);
                    nuevaFila.innerHTML = `
                        <td>${codigo}</td>
                        <td>${prod.nombre}</td>
                        <td>
                            <input type="number" name="productos[${itemIndex}][cantidad]" value="${cantidad}" min="1" class="form-control cantidad" data-precio="${precio}" data-stock="${stock}" oninput="actualizarTotal(this)">
                            <input type="hidden" name="productos[${itemIndex}][id_producto]" value="${prod.id_producto}">
                            <input type="hidden" name="productos[${itemIndex}][precio]" value="${precio}">
                        </td>
                        <td>$${precio.toFixed(2)}</td>
                        <td class="precio-total">$${precioTotal.toFixed(2)}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm px-2 py-1" onclick="quitarProductoDelCarrito(this)">×</button>
                        </td>`;
                    
                    if (tablaVentas) tablaVentas.appendChild(nuevaFila);
                });
            @endif

            // 4. Recalcular el total final
            if (typeof calcularTotalVenta === 'function') {
                calcularTotalVenta();
            }
        });
    </script>
@endif

@endsection