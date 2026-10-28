@extends('layouts.nav')

@section('title', 'Balances y Estadísticas')

@section('ladoizq')
<div class="row h-100">
    <div class="col-lg-8 d-flex flex-column">
        
        <div class="card mb-3 flex-grow-1 left-table text-white bg-dark border-secondary">
            <div class="card-body d-flex flex-column h-100 p-0">
                
                <div class="row g-0 flex-grow-1" style="min-height: 55vh;">
                    
                    <div class="col-md-5 col-lg-4 border-end border-secondary bg-dark p-3 d-flex flex-column justify-content-between">
                        <div class="d-flex flex-column gap-2 w-100">
                            <div class="text-center py-2 border-bottom border-secondary mb-2 text-info">
                                <strong class="text-uppercase small tracking-wider">Opciones</strong>
                            </div>
                            
                            <button id="btn-gastos_proveedor" class="btn btn-dark border-secondary text-start" onclick="cambiarVista('gastos_proveedor')">
                                <i class="fas fa-chart-pie me-2 text-danger"></i> Gastos por proveedor (gráfico torta)
                            </button>
                            <button id="btn-formas_pago" class="btn btn-dark border-secondary text-start" onclick="cambiarVista('formas_pago')">
                                <i class="fas fa-money-check-alt me-2 text-warning"></i> Balances de formas de pago (gráfico de barras)
                            </button>
                            <button id="btn-balance_positivo" class="btn btn-dark border-secondary text-start" onclick="cambiarVista('balance_positivo')">
                                <i class="fas fa-calculator me-2 text-success"></i> Cuánto de balance positivo hubo (ventas - gastos - compras) - formato de tabla
                            </button>
                            <button id="btn-vendedores" class="btn btn-dark border-secondary text-start" onclick="cambiarVista('vendedores')">
                                <i class="fas fa-user-tag me-2 text-primary"></i> Qué vendedor vendió más (gráfico de torta)
                            </button>
                            <button id="btn-ventas_mes" class="btn btn-dark border-secondary text-start" onclick="cambiarVista('ventas_mes')">
                                <i class="fas fa-chart-bar me-2 text-info"></i> Ventas a lo largo del mes (gráfico de barras)
                            </button>
                            <button id="btn-ventas_anio" class="btn btn-dark border-secondary text-start" onclick="cambiarVista('ventas_anio')">
                                <i class="fas fa-calendar-alt me-2 text-purple"></i> Ventas por mes en el año (gráfico de barras)
                            </button>
                        </div>
                        
                        <div class="mt-auto pt-3 border-top border-secondary w-100">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary w-100">
                                <i class="fas fa-arrow-left me-1"></i> Volver atrás
                            </a>
                        </div>
                    </div>

                    <div class="col-md-7 col-lg-8 p-4 d-flex flex-column justify-content-center align-items-center" style="background-color: #1e1e1e !important;">
                        
                        <div id="contenedor-grafico" style="position: relative; width: 100%; height: 45vh; display: block;">
                            <canvas id="canvasBalance"></canvas>
                        </div>

                        <div id="contenedor-tabla" class="w-100 table-responsive" style="display: none;">
                            <h5 class="text-center mb-3 text-success">Balance Positivo (Ventas - Gastos - Compras)</h5>
                            <table class="table table-dark table-striped border border-secondary align-middle m-0">
                                <thead>
                                    <tr class="text-info text-center">
                                        <th>Concepto</th>
                                        <th>Monto</th>
                                        <th>Impacto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><i class="fas fa-arrow-up text-success me-2"></i> (+) Total Ventas</td>
                                        <td class="text-end text-success font-monospace">${{ number_format($dataBalance['totalVentas'], 2) }}</td>
                                        <td class="text-center"><span class="badge bg-success">Ingreso</span></td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-arrow-down text-danger me-2"></i> (-) Total Compras</td>
                                        <td class="text-end text-danger font-monospace">${{ number_format($dataBalance['totalCompras'], 2) }}</td>
                                        <td class="text-center"><span class="badge bg-danger">Inversión</span></td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-minus text-warning me-2"></i> (-) Total Gastos</td>
                                        <td class="text-end text-warning font-monospace">${{ number_format($dataBalance['totalGastos'], 2) }}</td>
                                        <td class="text-center"><span class="badge bg-warning">Gasto</span></td>
                                    </tr>
                                    <tr class="table-active border-top border-secondary fw-bold fs-6">
                                        <td>Balance Positivo (Neto)</td>
                                        <td class="text-end {{ $dataBalance['balanceNeto'] >= 0 ? 'text-success' : 'text-danger' }} font-monospace">
                                            ${{ number_format($dataBalance['balanceNeto'], 2) }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $dataBalance['balanceNeto'] >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                {{ $dataBalance['balanceNeto'] >= 0 ? 'Ganancia' : 'Pérdida' }}
                                            </span>
                                        </td>
                                    </tr>
                                    
                                    {{-- FILA ADAPTADA EXACTAMENTE A TU FORMATO --}}
                                    <tr class="table-active border-top border-secondary fw-bold fs-6">
                                        <td><i class="fas fa-percentage text-info me-2"></i> Ganancia Real Limpia (Margen de Productos)</td>
                                        <td class="text-end text-info font-monospace">${{ number_format($dataBalance['gananciaLimpia'], 2) }}</td>
                                        <td class="text-center"><span class="badge bg-info text-dark">Ganancia Limpia</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <div class="p-3 bg-dark border-top border-secondary rounded-bottom">
                    <form method="GET" action="{{ route('views.balances') }}" class="row g-3 align-items-center justify-content-center">
                        <input type="hidden" name="vista" id="inputVistaOculta" value="{{ request('vista', 'gastos_proveedor') }}">
                        
                        <div class="col-auto d-flex align-items-center">
                            <span class="text-secondary small me-2"><i class="fas fa-calendar"></i> Año gráfico:</span>
                            <select class="form-select form-select-sm text-white bg-secondary border-0" name="anio" id="filtroAnio" style="min-width: 110px;">
                                <option value="">-- Año --</option>
                                @for ($i = 2024; $i <= now()->year; $i++)
                                    <option value="{{ $i }}" {{ (request('anio', now()->year) == $i && !request('fechainicio') && !request('fechafin')) ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-auto text-secondary d-none d-sm-block">|</div>

                        <div class="col-auto d-flex align-items-center">
                            <span class="text-secondary small me-2"><i class="fas fa-filter"></i> Rango Fechas:</span>
                            <input type="date" class="form-control form-control-sm text-white bg-secondary border-0 d-inline-block" style="width: auto;" name="fechainicio" id="filtroFechaInicio" value="{{ request('fechainicio') }}">
                            <span class="mx-2 text-muted text-center small">a</span>
                            <input type="date" class="form-control form-control-sm text-white bg-secondary border-0 d-inline-block" style="width: auto;" name="fechafin" id="filtroFechaFin" value="{{ request('fechafin') }}">
                        </div>

                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm px-3">
                                <i class="fas fa-sync-alt"></i> Filtrar
                            </button>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const datosVendedores = @json($dataVendedores);
    const datosFormasPago = @json($dataFormasPago);
    const datosGastosProveedor = @json($dataGastosProveedor);
    const datosVentasMes = @json($dataVentasMes);
    const datosVentasAnio = @json($dataVentasAnio);
    const anioActivo = "{{ $anioSeleccionado }}";

    let graficoActivo = null;

    // SOLUCIÓN JS: Control de exclusión mutua para limpiar los campos e impedir que se pisen los filtros
    document.getElementById('filtroAnio').addEventListener('change', function() {
        if(this.value !== "") {
            document.getElementById('filtroFechaInicio').value = "";
            document.getElementById('filtroFechaFin').value = "";
        }
    });

    function limpiarAnioSiHayFecha() {
        document.getElementById('filtroAnio').value = "";
    }
    document.getElementById('filtroFechaInicio').addEventListener('input', limpiarAnioSiHayFecha);
    document.getElementById('filtroFechaFin').addEventListener('input', limpiarAnioSiHayFecha);


    function cambiarVista(tipo) {
        document.getElementById('inputVistaOculta').value = tipo;

        document.querySelectorAll('[id^="btn-"]').forEach(btn => btn.classList.remove('active-balance-btn'));
        const botonActivo = document.getElementById(`btn-${tipo}`);
        if (botonActivo) { botonActivo.classList.add('active-balance-btn'); }

        const contenedorGrafico = document.getElementById('contenedor-grafico');
        const contenedorTabla = document.getElementById('contenedor-tabla');

        if (tipo === 'balance_positivo') {
            contenedorGrafico.style.display = 'none';
            contenedorTabla.style.display = 'block';
            return;
        } else {
            contenedorGrafico.style.display = 'block';
            contenedorTabla.style.display = 'none';
        }

        if (graficoActivo) { graficoActivo.destroy(); }

        const ctx = document.getElementById('canvasBalance').getContext('2d');
        let config = {};
        const coloresPastel = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#6366f1'];

        switch (tipo) {
            case 'gastos_proveedor':
                config = {
                    type: 'pie',
                    data: {
                        labels: datosGastosProveedor.map(d => d.proveedor),
                        datasets: [{ data: datosGastosProveedor.map(d => d.total), backgroundColor: coloresPastel }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: '#fff' } } } }
                };
                break;

            case 'formas_pago':
                config = {
                    type: 'bar',
                    data: {
                        labels: datosFormasPago.map(d => d.metodo),
                        datasets: [{ label: 'Recaudado ($)', data: datosFormasPago.map(d => d.total), backgroundColor: '#f59e0b' }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { ticks: { color: '#fff' }, grid: { color: '#3a3a3a' } },
                            y: { ticks: { color: '#fff' }, grid: { color: '#3a3a3a' } }
                        },
                        plugins: { legend: { labels: { color: '#fff' } } }
                    }
                };
                break;

            case 'vendedores':
                config = {
                    type: 'pie',
                    data: {
                        labels: datosVendedores.map(d => d.nombre),
                        datasets: [{ data: datosVendedores.map(d => d.total), backgroundColor: coloresPastel }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: '#fff' } } } }
                };
                break;

            case 'ventas_mes':
                config = {
                    type: 'bar',
                    data: {
                        labels: datosVentasMes.map(d => `Día ${d.dia}`),
                        datasets: [{ label: 'Ventas ($)', data: datosVentasMes.map(d => d.total), backgroundColor: '#3b82f6' }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { ticks: { color: '#fff' }, grid: { color: '#3a3a3a' } },
                            y: { ticks: { color: '#fff' }, grid: { color: '#3a3a3a' } }
                        },
                        plugins: { legend: { labels: { color: '#fff' } } }
                    }
                };
                break;

            case 'ventas_anio':
                config = {
                    type: 'bar',
                    data: {
                        labels: datosVentasAnio.map(d => {
                            const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                            return meses[d.mes - 1];
                        }),
                        datasets: [{ 
                            label: `Facturación Mensual Año ${anioActivo} ($)`, 
                            data: datosVentasAnio.map(d => d.total), 
                            backgroundColor: '#10b981' 
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { ticks: { color: '#fff' }, grid: { color: '#3a3a3a' } },
                            y: { ticks: { color: '#fff' }, grid: { color: '#3a3a3a' } }
                        },
                        plugins: { legend: { labels: { color: '#fff' } } }
                    }
                };
                break;
        }
        graficoActivo = new Chart(ctx, config);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const vistaInicial = document.getElementById('inputVistaOculta').value || 'gastos_proveedor';
        cambiarVista(vistaInicial);
    });
</script>

<style>
    .active-balance-btn {
        background-color: #3a3a3a !important;
        border-color: #3b82f6 !important;
        color: #3b82f6 !important;
        font-weight: bold;
    }
</style>
@endsection