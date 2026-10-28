<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Presupuesto</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 13px; color: #222; background: #fff; padding: 30px; }
        
        /* Contenedor X (Integrado en el layout del header) */
        .contenedor-x {
            width: 70px; height: 70px; border: 2px solid #000;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            background: #fff; margin: 0 20px;
        }
        .x-grande { font-size: 40px; font-weight: bold; line-height: 1; }
        .leyenda-x { font-size: 7px; font-weight: bold; text-transform: uppercase; text-align: center; line-height: 1; margin-top: 2px; }

        .ticket { position: relative; max-width: 800px; margin: 0 auto; border: 1px solid #ccc; padding: 30px; }

        .no-print { margin-bottom: 20px; display: flex; gap: 10px; }
        .btn { padding: 8px 18px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .btn-volver { background: #6c757d; color: #fff; }
        .btn-imprimir { background: #0d6efd; color: #fff; }

        /* Header estructurado con Flexbox */
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header-izq { flex: 1; }
        .header-der { flex: 1; text-align: right; }
        
        .header-izq h1 { font-size: 22px; font-weight: bold; margin-bottom: 5px; }
        .logo { width: 100px; height: auto; margin-bottom: 8px; }
        .datos-local { font-size: 12px; line-height: 1.5; color: #444; }

        hr { border: none; border-top: 1px solid #ccc; margin: 16px 0; }
        
        .datos-cliente { display: flex; justify-content: flex-end; margin-bottom: 16px; }
        .datos-cliente table td { padding: 2px 8px; font-size: 13px; }
        .datos-cliente table td:first-child { font-weight: bold; text-align: right; }

        .tabla-productos { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .tabla-productos thead tr { background: #333; color: #fff; }
        .tabla-productos th, .tabla-productos td { padding: 7px 10px; text-align: left; border-bottom: 1px solid #ddd; }
        .tabla-productos th:last-child, .tabla-productos td:last-child { text-align: right; }
        .tabla-productos tbody tr:nth-child(even) { background: #f9f9f9; }

        .totales { display: flex; justify-content: flex-end; }
        .totales table { min-width: 260px; }
        .fila-total td { font-size: 18px; font-weight: bold; border-top: 2px solid #333; padding-top: 10px; }
        .fila-total td:last-child { color: #c0392b; }

        .avisos { margin-top: 24px; font-size: 12px; color: #555; border-top: 1px solid #ddd; padding-top: 12px; }
        .footer { text-align: center; margin-top: 24px; font-size: 11px; color: #888; }

        @media print {
            .no-print { display: none !important; }
            .ticket { border: none; padding: 10px; }
        }
    </style>
</head>

<body>

    <div class="no-print">
        <button class="btn btn-volver" onclick="history.back()">← Volver</button>
        <button class="btn btn-imprimir" onclick="window.print()">Imprimir presupuesto</button>
    </div>

    <div class="ticket">
        <div class="header">
            <div class="header-izq">
                <h1>{{ $local['nombre'] }}</h1>
                <img src="{{ asset('logo_se.jpeg')  }}" alt="Logo Empresa" class="logo">
                <div class="datos-local">
                    <div>Facebook: {{ $local['facebook'] }}</div>
                    <div>Instagram: {{ $local['instagram'] }}</div>
                    <div>Teléfono: {{ $local['telefono'] }}</div>
                    <div>{{ $local['direccion'] }}</div>
                </div>
            </div>

            <div class="contenedor-x">
                <div class="x-grande">X</div>
                <div class="leyenda-x">TICKET NO VÁLIDO<br>COMO FACTURA</div>
            </div>

            <div class="header-der">
                <div>Fecha: {{ \Carbon\Carbon::parse($presupuesto->fecha)->format('d/m/Y H:i') }}</div>
                <div style="font-weight:bold; font-size:14px;">PRESUPUESTO</div>
            </div>
        </div>

        <hr>

        <div class="datos-cliente">
            <table>
                @if (!empty($presupuesto->titulo))
                    <tr>
                        <td colspan="2" style="padding-bottom: 5px;">
                            <strong>Título / Obra:</strong> {{ $presupuesto->titulo }}
                        </td>
                    </tr>
                @endif
                <tr>
                    <td>Cliente: {{ $presupuesto->nombre_cliente ?: 'Consumidor final' }}</td>
                </tr>
                <tr>
                    <td>Teléfono: {{ $presupuesto->telefono_cliente ?: '-' }}</td>
                </tr>
            </table>
        </div>

        <hr>

        @php
            $serviciosCategorias = [13, 14];
            $articulos = $productos->filter(function($item) use ($serviciosCategorias) {
                $cat = strtolower($item->nombre_categoria ?? '');
                return !in_array($item->id_categoria ?? 0, $serviciosCategorias) 
                    && !str_starts_with($cat, 's -') 
                    && !str_starts_with($cat, 'in -') 
                    && !str_contains($cat, 'servicio') 
                    && !str_contains($cat, 'instalac');
            });

            $servicios = $productos->filter(function($item) use ($serviciosCategorias) {
                $cat = strtolower($item->nombre_categoria ?? '');
                return in_array($item->id_categoria ?? 0, $serviciosCategorias) 
                    || str_starts_with($cat, 's -') 
                    || str_starts_with($cat, 'in -') 
                    || str_contains($cat, 'servicio') 
                    || str_contains($cat, 'instalac');
            });
        @endphp

        @if($articulos->count() > 0 || (isset($promociones) && count($promociones) > 0))
            @if($servicios->count() > 0)
                <div style="font-weight: bold; font-size: 12px; text-transform: uppercase; margin-bottom: 5px; background: #f0f0f0; padding: 4px 8px; border-left: 3px solid #333;">
                    PRODUCTOS
                </div>
            @endif
            <table class="tabla-productos">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Cant.</th>
                        <th>Precio unit.</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Imprimir Productos Individuales --}}
                    @foreach ($articulos as $producto)
                        <tr>
                            <td>{{ $producto->codigo ?: $producto->codigo_barra ?: '-' }}</td>
                            <td>{{ $producto->nombre }}</td>
                            <td>{{ $producto->cantidad }}</td>
                            <td>${{ number_format($producto->precio, 2) }}</td>
                            <td>${{ number_format($producto->cantidad * $producto->precio, 2) }}</td>
                        </tr>
                    @endforeach

                    {{-- Imprimir Promociones (Si hay) --}}
                    @if(isset($promociones) && count($promociones) > 0)
                        @foreach ($promociones as $promo)
                            <tr>
                                <td>PROMO</td>
                                <td>{{ $promo->nombre }}</td>
                                <td>{{ $promo->cantidad }}</td>
                                <td>${{ number_format($promo->precio, 2) }}</td>
                                <td>${{ number_format($promo->cantidad * $promo->precio, 2) }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        @endif

        @if($servicios->count() > 0)
            <div style="font-weight: bold; font-size: 12px; text-transform: uppercase; margin-top: 15px; margin-bottom: 5px; background: #f0f0f0; padding: 4px 8px; border-left: 3px solid #333;">
                SERVICIOS E INSTALACIONES
            </div>
            <table class="tabla-productos">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Cant.</th>
                        <th>Precio unit.</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($servicios as $servicio)
                        <tr>
                            <td>{{ $servicio->codigo ?: $servicio->codigo_barra ?: '-' }}</td>
                            <td>{{ $servicio->nombre }}</td>
                            <td>{{ $servicio->cantidad }}</td>
                            <td>${{ number_format($servicio->precio, 2) }}</td>
                            <td>${{ number_format($servicio->cantidad * $servicio->precio, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if (!empty($presupuesto->observaciones))
            <div style="margin-top: 15px; padding: 8px 12px; border: 1px dashed #999; border-radius: 4px; background-color: #f8f9fa;">
                <strong>Observaciones:</strong>
                <p style="margin-top: 3px; font-size: 12px; white-space: pre-line;">{{ $presupuesto->observaciones }}</p>
            </div>
        @endif
        <div class="totales">
            <table>
                <tr>
                    <td>SUBTOTAL $</td>
                    {{-- El subtotal es el Total final + el Descuento --}}
                    <td>{{ number_format($presupuesto->monto_total + $presupuesto->descuento, 2) }}</td>
                </tr>
                
                @if ($presupuesto->descuento > 0)
                    <tr>
                        <td>DESCUENTO $</td>
                        <td>{{ number_format($presupuesto->descuento, 2) }}</td>
                    </tr>
                @endif
                
                <tr class="fila-total">
                    <td>Total $</td>
                    <td>{{ number_format($presupuesto->monto_total, 2) }}</td>
                </tr>
            </table>
        </div>

        <div class="avisos">
            <p>* Precios sujetos a cambios sin previo aviso.</p>
            <p>* Consultar stock al momento de realizar el pedido.</p>
        </div>

        <div class="footer">
            <p>{{ $local['nombre'] }} — {{ $local['telefono'] }}</p>
        </div>
    </div>

</body>
</html>