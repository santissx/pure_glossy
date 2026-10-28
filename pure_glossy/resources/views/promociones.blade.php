@extends('layouts.nav')

@section('title', 'Promociones')

@section('ladoizq')
@if ($errors->any())
    <div class="alert alert-danger mb-3">
        @foreach ($errors->all() as $error)
            <p class="mb-0">{{ $error }}</p>
        @endforeach
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show fw-bold mb-3" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show fw-bold mb-3" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row h-100">
    {{-- Columna Izquierda (8 columnas) --}}
    <div class="col-lg-8 d-flex flex-column">
        
        <div class="card mb-3 flex-grow-1 left-table position-relative">
            <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white border-bottom border-secondary py-3">
                <h5 class="card-title mb-0">
                    <i class="fas fa-tags me-2"></i>Gestión de Promociones
                </h5>
                <div class="d-flex gap-2">
                    <form action="{{ route('promociones.actualizar-precios') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success fw-bold">
                            <i class="fas fa-sync-alt me-1"></i> Actualizar
                        </button>
                    </form>

                    <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modalCrearPromo">
                        <i class="fas fa-plus me-1"></i> Nueva Promoción
                    </button>
                </div>
            </div>

            <div class="card-body d-flex flex-column p-0">
                <div class="table-responsive flex-grow-1 table-scrollhist">
                    <table class="table table-dark table-striped align-middle mb-0">
                        <thead>
                            <tr class="border-secondary">
                                <th class="border-secondary">ID</th>
                                <th class="border-secondary">Nombre Promoción</th>
                                <th class="border-secondary">Productos Incluidos</th>
                                <th class="border-secondary">Precio Combo</th>
                                <th class="text-center border-secondary">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($promociones as $promo)
                            <tr class="border-secondary">
                                <td class="border-secondary">{{ $promo->id_promocion }}</td>
                                <td class="fw-bold border-secondary">{{ $promo->nombre }}</td>
                                <td class="border-secondary">
                                    <ul class="mb-0 ps-3 small text-white-50">
                                        @foreach($promo->productos as $prod)
                                            <li>
                                                <strong class="text-white">{{ $prod->cantidad }}x</strong> {{ $prod->nombre }} 
                                                <span>(${{ number_format($prod->precio_venta, 2) }})</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="fw-bold text-success border-secondary">${{ number_format($promo->precio, 2) }}</td>
                                <td class="text-center border-secondary">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-warning btn-sm px-2 py-1 fw-bold text-dark" 
                                                onclick="abrirModalEditar({{ json_encode($promo) }})">
                                            <i class="fas fa-edit me-1"></i> Editar
                                        </button>

                                        <form action="{{ route('promociones.destroy', $promo->id_promocion) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Desactivar esta promoción?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm px-2 py-1">
                                                <i class="fas fa-trash-alt me-1"></i> Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No hay promociones activas registradas.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- Columna Derecha (4 columnas) --}}
    <div class="col-lg-4 d-flex flex-column">
        @include('parciales.columna_derecha')
    </div>
</div>

{{-- Modal Crear Promoción --}}
<div class="modal fade" id="modalCrearPromo" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-white border-secondary">
            <form action="{{ route('promociones.store') }}" method="POST" id="formCrearPromo">
                @csrf
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title"><i class="fas fa-gift me-2"></i>Crear Nueva Promoción</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre de la Promoción</label>
                        <input type="text" name="nombre" class="form-control bg-dark text-white border-secondary" placeholder="Ej: Combo 2 Lámparas + 2 Tomacorrientes" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de descuento</label>

                        <select name="tipo_descuento"
                                id="tipo_descuento"
                                class="form-select bg-dark text-white border-secondary"
                                onchange="cambiarTipoDescuentoCrear()"
                                required>
                            <option value="promocion">Descuento sobre toda la promoción</option>
                            <option value="producto">Descuento por producto</option>
                        </select>

                        <small class="text-muted">
                            Elegí una sola modalidad de descuento para esta promoción.
                        </small>
                    </div>

                    <div class="card p-3 bg-dark border border-secondary mb-3">
                        <h6 class="fw-bold mb-2"><i class="fas fa-search me-1"></i> Buscar Productos para el Combo</h6>
                        
                        <div class="mb-2">
                            <input type="text" id="inputBuscadorPromo" class="form-control bg-dark text-white border-secondary" placeholder="Escribe para buscar... (ej: lampara 12w)" onkeyup="filtrarProductosPromo('inputBuscadorPromo', '.fila-producto-promo')">
                        </div>

                        <div class="table-responsive border border-secondary rounded mb-3" style="max-height: 180px; overflow-y: auto;">
                            <table class="table table-dark table-hover table-sm mb-0 align-middle">
                                <thead>
                                    <tr class="bg-dark text-white border-bottom border-secondary">
                                        <th class="border-secondary bg-dark text-white">Producto</th>
                                        <th class="border-secondary bg-dark text-white">Precio Venta</th>
                                        <th style="width: 90px;" class="border-secondary bg-dark text-white">Cant.</th>
                                        <th style="width: 70px;" class="text-center border-secondary bg-dark text-white">Añadir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($productos as $p)
                                    <tr class="fila-producto-promo border-secondary" 
                                        data-id="{{ $p->id_producto }}" 
                                        data-nombre="{{ $p->nombre }}" 
                                        data-codigo="{{ $p->codigo_barra ?? '' }}" 
                                        data-precio="{{ $p->precio_venta }}">
                                        <td class="border-secondary">{{ $p->nombre }}</td>
                                        <td class="text-success fw-bold border-secondary">${{ number_format($p->precio_venta, 2) }}</td>
                                        <td class="border-secondary">
                                            <input type="number" value="1" min="1" class="form-control form-control-sm bg-dark text-white border-secondary input-cant-item">
                                        </td>
                                        <td class="text-center border-secondary">
                                            <button type="button" class="btn btn-primary btn-sm px-2 py-0" onclick="agregarItemDesdeFila(this, 'crear')">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <h6 class="fw-bold mb-2"><i class="fas fa-boxes me-1"></i> Productos Agregados a esta Promo</h6>
                        <div class="table-responsive border border-secondary rounded">
                            <table class="table table-dark table-striped mb-0 align-middle">
                                <thead>
                                    <tr class="bg-dark text-white border-bottom border-secondary">
                                        <thead>
                                            <tr class="bg-dark text-white border-bottom border-secondary">
                                                <th class="border-secondary bg-dark text-white">Producto</th>
                                                <th style="width: 100px;" class="border-secondary bg-dark text-white">Cantidad</th>
                                                <th style="width: 120px;" class="border-secondary bg-dark text-white">Subtotal</th>
                                                <th style="width: 50px;" class="text-center border-secondary bg-dark text-white">Acción</th>
                                            </tr>
                                        </thead>
                                    </tr>
                                </thead>
                                <tbody id="tablaProductosCombo">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted small py-3 border-secondary">Usa el buscador arriba para añadir productos al combo.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mb-2" id="bloqueDescuentoPromocion">
                        <label class="form-label fw-bold text-success">Descuento (%)</label>
                        <input type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            id="descuento_porcentaje"
                            name="descuento_porcentaje"
                            class="form-control form-control-lg fw-bold bg-dark text-white border-secondary"
                            value="0"
                            required
                            onchange="renderizarTablaCombo()">
                        <small class="text-muted">
                            Ingresá el porcentaje de descuento que tendrá la promoción.
                        </small>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold text-success">Precio Final de la Promoción ($)</label>
                        <input type="number" step="0.01" id="precio" name="precio" class="form-control form-control-lg fw-bold bg-dark text-white border-secondary" required placeholder="0.00">
                        <small class="text-muted">
                            Se calcula automáticamente sumando los productos, pero puedes cambiar el valor si deseas aplicar un descuento.
                        </small>
                    </div>
                </div>

                <div class="modal-footer border-top border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold"><i class="fas fa-save me-1"></i> Guardar Promoción</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Editar Promoción --}}
<div class="modal fade" id="modalEditarPromo" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-white border-secondary">
            <form id="formEditarPromo" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Modificar Promoción</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre de la Promoción</label>
                        <input type="text" id="edit_nombre" name="nombre" class="form-control bg-dark text-white border-secondary" required>
                    </div>

                    <div class="card p-3 bg-dark border border-secondary mb-3">
                        <h6 class="fw-bold mb-2"><i class="fas fa-search me-1"></i> Buscar Productos para Añadir/Sumar</h6>
                        
                        <div class="mb-2">
                            <input type="text" id="inputBuscadorPromoEdit" class="form-control bg-dark text-white border-secondary" placeholder="Escribe para buscar..." onkeyup="filtrarProductosPromo('inputBuscadorPromoEdit', '.fila-producto-promo-edit')">
                        </div>

                        <div class="table-responsive border border-secondary rounded mb-3" style="max-height: 180px; overflow-y: auto;">
                            <table class="table table-dark table-hover table-sm mb-0 align-middle">
                                <thead>
                                    <tr class="bg-dark text-white border-bottom border-secondary">
                                        <th class="border-secondary bg-dark text-white">Producto</th>
                                        <th class="border-secondary bg-dark text-white">Precio Venta</th>
                                        <th style="width: 90px;" class="border-secondary bg-dark text-white">Cant.</th>
                                        <th style="width: 70px;" class="text-center border-secondary bg-dark text-white">Añadir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($productos as $p)
                                    <tr class="fila-producto-promo-edit border-secondary" 
                                        data-id="{{ $p->id_producto }}" 
                                        data-nombre="{{ $p->nombre }}" 
                                        data-codigo="{{ $p->codigo_barra ?? '' }}" 
                                        data-precio="{{ $p->precio_venta }}">
                                        <td class="border-secondary">{{ $p->nombre }}</td>
                                        <td class="text-success fw-bold border-secondary">${{ number_format($p->precio_venta, 2) }}</td>
                                        <td class="border-secondary">
                                            <input type="number" value="1" min="1" class="form-control form-control-sm bg-dark text-white border-secondary input-cant-item">
                                        </td>
                                        <td class="text-center border-secondary">
                                            <button type="button" class="btn btn-primary btn-sm px-2 py-0" onclick="agregarItemDesdeFila(this, 'editar')">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <h6 class="fw-bold mb-2"><i class="fas fa-boxes me-1"></i> Productos en esta Promo</h6>
                        <div class="table-responsive border border-secondary rounded">
                            <table class="table table-dark table-striped mb-0 align-middle">
                                <thead>
                                    <tr class="bg-dark text-white border-bottom border-secondary">
                                        <th class="border-secondary bg-dark text-white">Producto</th>
                                        <th style="width: 100px;" class="border-secondary bg-dark text-white">Cantidad</th>
                                        <th style="width: 120px;" class="border-secondary bg-dark text-white">Descuento (%)</th>
                                        <th style="width: 120px;" class="border-secondary bg-dark text-white">Subtotal</th>
                                        <th style="width: 50px;" class="text-center border-secondary bg-dark text-white">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaProductosComboEdit">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de descuento</label>

                        <select name="tipo_descuento"
                                id="edit_tipo_descuento"
                                class="form-select bg-dark text-white border-secondary"
                                onchange="cambiarTipoDescuentoEditar()"
                                required>
                            <option value="promocion">Descuento sobre toda la promoción</option>
                            <option value="producto">Descuento por producto</option>
                        </select>

                        <small class="text-muted">
                            Elegí una sola modalidad de descuento para esta promoción.
                        </small>
                    </div>

                    <div class="mb-2" id="bloqueDescuentoPromocionEdit">
                        <label class="form-label fw-bold text-success">Descuento (%)</label>
                        <input type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            id="edit_descuento_porcentaje"
                            name="descuento_porcentaje"
                            class="form-control form-control-lg fw-bold bg-dark text-white border-secondary"
                            value="0"
                            required
                            onchange="renderizarTablaComboEdit()">
                        <small class="text-muted">
                            Ingresá el porcentaje de descuento que tendrá la promoción.
                        </small>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold text-success">Precio Final Comercial ($)</label>
                        <input type="number" step="0.01" id="edit_precio" name="precio" class="form-control form-control-lg fw-bold bg-dark text-white border-secondary" required>
                    </div>
                </div>

                <div class="modal-footer border-top border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark"><i class="fas fa-save me-1"></i> Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let productosCombo = [];
    let productosComboEdit = [];

    // Helper para normalizar acentos
    function normalizarTexto(texto) {
    if (!texto) return '';
    return texto.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
}

// Función de filtrado multipalabra e insensible a acentos
function filtrarProductosPromo(inputId = 'inputBuscadorPromo', filaSelector = '.fila-producto-promo') {
    const filtroRaw = document.getElementById(inputId).value;
    const filtroLimpio = normalizarTexto(filtroRaw);
    const palabras = filtroLimpio.split(' ').filter(p => p !== '');
    const filas = document.querySelectorAll(filaSelector);

    filas.forEach(fila => {
        const nombre = normalizarTexto(fila.getAttribute('data-nombre'));
        const codigo = normalizarTexto(fila.getAttribute('data-codigo'));
        const textoCompleto = `${nombre} ${codigo}`;

        const coincide = palabras.every(palabra => textoCompleto.includes(palabra));

        fila.style.display = coincide ? '' : 'none';
    });
}

    // Agregar producto desde tabla buscador
    function agregarItemDesdeFila(btn, modo) {
        const fila = btn.closest('tr');
        const idProducto = fila.getAttribute('data-id');
        const nombre = fila.getAttribute('data-nombre');
        const precio = parseFloat(fila.getAttribute('data-precio'));
        const inputCant = fila.querySelector('.input-cant-item');
        const cantidad = parseInt(inputCant.value);

        if (isNaN(cantidad) || cantidad <= 0) {
            alert("La cantidad debe ser al menos 1.");
            return;
        }

        let arrayTarget = (modo === 'crear') ? productosCombo : productosComboEdit;

        const existe = arrayTarget.find(item => item.id_producto == idProducto);
        if (existe) {
            existe.cantidad += cantidad;
        } else {
            arrayTarget.push({
                id_producto: idProducto,
                nombre: nombre,
                precio: precio,
                cantidad: cantidad
            });
        }

        inputCant.value = '1';

        if (modo === 'crear') {
            renderizarTablaCombo();
        } else {
            renderizarTablaComboEdit();
        }
    }

    // Modal Crear
    function eliminarProductoCombo(index) {
        productosCombo.splice(index, 1);
        renderizarTablaCombo();
    }

    function cambiarTipoDescuentoCrear() {
        const tipoDescuento = document.getElementById('tipo_descuento').value;
        const bloqueDescuento = document.getElementById('bloqueDescuentoPromocion');
        const inputDescuento = document.getElementById('descuento_porcentaje');

        if (tipoDescuento === 'promocion') {
            bloqueDescuento.style.display = 'block';
            inputDescuento.disabled = false;
        } else {
            bloqueDescuento.style.display = 'none';
            inputDescuento.value = 0;
            inputDescuento.disabled = true;
        }

        renderizarTablaCombo();
    }

    function cambiarTipoDescuentoEditar() {
    const tipoDescuento = document.getElementById('edit_tipo_descuento').value;
    const bloqueDescuento = document.getElementById('bloqueDescuentoPromocionEdit');
    const inputDescuento = document.getElementById('edit_descuento_porcentaje');

    if (tipoDescuento === 'promocion') {
        bloqueDescuento.style.display = 'block';
        inputDescuento.disabled = false;
    } else {
        bloqueDescuento.style.display = 'none';
        inputDescuento.value = 0;
        inputDescuento.disabled = true;
    }

    renderizarTablaComboEdit();
}

    function renderizarTablaCombo() {
        const tbody = document.getElementById('tablaProductosCombo');
        tbody.innerHTML = '';

        if (productosCombo.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted small py-3 border-secondary">
                        Usa el buscador arriba para añadir productos al combo.
                    </td>
                </tr>`;

            document.getElementById('precio').value = '';
            return;
        }

        let sumaDefecto = 0;

        const tipoDescuento = document.getElementById('tipo_descuento').value;

        productosCombo.forEach((item, index) => {
            const subtotal = item.precio * item.cantidad;
            sumaDefecto += subtotal;

            let descuentoProducto = parseFloat(item.descuento_porcentaje) || 0;
            let subtotalFinal = subtotal;

            if (tipoDescuento === 'producto') {
                subtotalFinal = subtotal * (1 - descuentoProducto / 100);
            }

            tbody.innerHTML += `
                <tr class="border-secondary">
                    <td class="border-secondary">
                        ${item.nombre}
                        <input type="hidden"
                            name="productos[${index}][id_producto]"
                            value="${item.id_producto}">
                    </td>

                    <td class="border-secondary">
                        ${item.cantidad}
                        <input type="hidden"
                            name="productos[${index}][cantidad]"
                            value="${item.cantidad}">
                    </td>

                    <td class="border-secondary">
                        ${
                            tipoDescuento === 'producto'
                            ? `
                                <input type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    class="form-control form-control-sm bg-dark text-white border-secondary"
                                    value="${descuentoProducto}"
                                    onchange="productosCombo[${index}].descuento_porcentaje = parseFloat(this.value) || 0; renderizarTablaCombo();">
                                <input type="hidden"
                                    name="productos[${index}][descuento_porcentaje]"
                                    value="${descuentoProducto}">
                            `
                            : `
                                <span class="text-muted">Sin descuento individual</span>
                                <input type="hidden"
                                    name="productos[${index}][descuento_porcentaje]"
                                    value="0">
                            `
                        }
                    </td>

                    <td class="fw-bold text-success border-secondary">
                        $${subtotalFinal.toFixed(2)}
                    </td>

                    <td class="text-center border-secondary">
                        <button type="button"
                            class="btn btn-danger btn-sm px-2 py-0"
                            onclick="eliminarProductoCombo(${index})">
                            &times;
                        </button>
                    </td>
                </tr>`;
        });

        let precioFinal = sumaDefecto;

        if (tipoDescuento === 'promocion') {
            const descuento = parseFloat(
                document.getElementById('descuento_porcentaje').value
            ) || 0;

            precioFinal = sumaDefecto * (1 - descuento / 100);
        } else {
            precioFinal = productosCombo.reduce((total, item) => {
                const subtotal = item.precio * item.cantidad;
                const descuento = parseFloat(item.descuento_porcentaje) || 0;

                return total + (subtotal * (1 - descuento / 100));
            }, 0);
        }

        document.getElementById('precio').value = precioFinal.toFixed(2);
    }

    // Modal Editar
    function abrirModalEditar(promo) {
        document.getElementById('formEditarPromo').action = `/promociones/${promo.id_promocion}`;
        document.getElementById('edit_nombre').value = promo.nombre;
        document.getElementById('edit_precio').value = parseFloat(promo.precio).toFixed(2);
        document.getElementById('edit_descuento_porcentaje').value = parseFloat(promo.descuento_porcentaje) || 0;

        document.getElementById('edit_tipo_descuento').value = promo.tipo_descuento || 'promocion';

        productosComboEdit = [];

        if (promo.productos) {
            promo.productos.forEach(p => {
                productosComboEdit.push({
                    id_producto: p.id_producto,
                    nombre: p.nombre,
                    precio: parseFloat(p.precio_venta),
                    cantidad: parseInt(p.cantidad),
                    descuento_porcentaje: parseFloat(p.descuento_porcentaje) || 0
                });
            });
        }

        cambiarTipoDescuentoEditar();
        renderizarTablaComboEdit(false);

        const modal = new bootstrap.Modal(document.getElementById('modalEditarPromo'));
        modal.show();
    }

    function eliminarProductoComboEdit(index) {
        productosComboEdit.splice(index, 1);
        renderizarTablaComboEdit(true);
    }

    function renderizarTablaComboEdit(recalcularPrecio = true) {
        const tbody = document.getElementById('tablaProductosComboEdit');
        tbody.innerHTML = '';

        if (productosComboEdit.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted small py-3 border-secondary">
                        Usa el buscador arriba para añadir productos al combo.
                    </td>
                </tr>`;

            if (recalcularPrecio) {
                document.getElementById('edit_precio').value = '';
            }

            return;
        }

        let sumaDefecto = 0;

        const tipoDescuento = document.getElementById('edit_tipo_descuento').value;

        productosComboEdit.forEach((item, index) => {
            const subtotal = item.precio * item.cantidad;
            sumaDefecto += subtotal;

            let descuentoProducto = parseFloat(item.descuento_porcentaje) || 0;
            let subtotalFinal = subtotal;

            if (tipoDescuento === 'producto') {
                subtotalFinal = subtotal * (1 - descuentoProducto / 100);
            }

            tbody.innerHTML += `
                <tr class="border-secondary">
                    <td class="border-secondary">
                        ${item.nombre}
                        <input type="hidden"
                            name="productos[${index}][id_producto]"
                            value="${item.id_producto}">
                    </td>

                    <td class="border-secondary">
                        ${item.cantidad}
                        <input type="hidden"
                            name="productos[${index}][cantidad]"
                            value="${item.cantidad}">
                    </td>

                    <td class="border-secondary">
                        ${
                            tipoDescuento === 'producto'
                            ? `
                                <input type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    class="form-control form-control-sm bg-dark text-white border-secondary"
                                    value="${descuentoProducto}"
                                    onchange="productosComboEdit[${index}].descuento_porcentaje = parseFloat(this.value) || 0; renderizarTablaComboEdit();">

                                <input type="hidden"
                                    name="productos[${index}][descuento_porcentaje]"
                                    value="${descuentoProducto}">
                            `
                            : `
                                <span class="text-muted">Sin descuento individual</span>
                                <input type="hidden"
                                    name="productos[${index}][descuento_porcentaje]"
                                    value="0">
                            `
                        }
                    </td>

                    <td class="fw-bold text-success border-secondary">
                        $${subtotalFinal.toFixed(2)}
                    </td>

                    <td class="text-center border-secondary">
                        <button type="button"
                            class="btn btn-danger btn-sm px-2 py-0"
                            onclick="eliminarProductoComboEdit(${index})">
                            &times;
                        </button>
                    </td>
                </tr>`;
        });

        if (recalcularPrecio) {
            let precioFinal = sumaDefecto;

            if (tipoDescuento === 'promocion') {
                const descuento = parseFloat(
                    document.getElementById('edit_descuento_porcentaje').value
                ) || 0;

                precioFinal = sumaDefecto * (1 - descuento / 100);
            } else {
                precioFinal = productosComboEdit.reduce((total, item) => {
                    const subtotal = item.precio * item.cantidad;
                    const descuento = parseFloat(item.descuento_porcentaje) || 0;

                    return total + (subtotal * (1 - descuento / 100));
                }, 0);
            }

            document.getElementById('edit_precio').value = precioFinal.toFixed(2);
        }
    }

    // Validaciones al enviar
    document.getElementById('formCrearPromo').addEventListener('submit', function(e) {
        if (productosCombo.length === 0) {
            e.preventDefault();
            alert("Debes agregar al menos un producto a la promoción.");
        }
    });

    document.getElementById('formEditarPromo').addEventListener('submit', function(e) {
        if (productosComboEdit.length === 0) {
            e.preventDefault();
            alert("Debes agregar al menos un producto a la promoción.");
        }
    });
</script>
@endsection