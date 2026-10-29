<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Promocioncontroller;
use App\Http\Controllers\ListaController;
use App\Http\Controllers\ProveedoresController;
use App\Http\Controllers\GastosController;
use App\Http\Controllers\EmpleadosController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ComprasController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\HistorialController;
use App\Http\Controllers\VentasAnuController;
use App\Http\Controllers\FallbackController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\PresupuestoController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\HistorialPresupuestoController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\CategoriaController;

//rutas del login 

Route::get('/dashboard', [VentaController::class, 'mostrar'])->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/mis-sesiones', [EmpleadosController::class, 'misSesiones'])
    ->middleware('auth')
    ->name('mis.sesiones');

require __DIR__.'/auth.php';

//rutas del sistema

Route::middleware(['admin'])->group(function () {

    Route::get('/gastos', function () {
        return view('gastos');
    })->name('views.gastos');
    Route::get('/empleados', function () {
        return view('empleados');
    }) ->name('views.empleados');
    Route::get('/compras', function () {
        return view('compras');
    }) ->name('views.compras');

//rutas para proveedores
Route::get('/proveedores', [ProveedoresController::class, 'mostrar'])->name('views.proveedores');
Route::POST('/proveedores', [ProveedoresController::class, 'agregar'])->name('proveedores.agregar');
Route::get('/proveedores/modificar', [ProveedoresController::class, 'modificar'])->name('proveedores.modificar');
Route::delete('/proveedores/{id_proveedor}', [ProveedoresController::class, 'borrar'])->name('proveedores.borrar');

//rutas gastos
Route::get('/gastos', [GastosController::class, 'mostrar'])->name('views.gastos');
Route::POST('/gastos', [GastosController::class, 'agregar'])->name('gastos.agregar');
Route::get('/gastos/modificar', [GastosController::class, 'modificar'])->name('gastos.modificar');
Route::delete('/gastos/{id_gasto}', [GastosController::class, 'borrar'])->name('gastos.borrar');

//ruta para empleados
Route::get('/empleados', [EmpleadosController::class, 'mostrar'])->name('views.empleados');
Route::POST('/empleados', [EmpleadosController::class, 'agregar'])->name('empleados.agregar');
Route::get('/empleados/modificar', [EmpleadosController::class, 'modificar'])->name('empleados.modificar');
Route::delete('/empleados/{id}', [EmpleadosController::class, 'borrar'])->name('empleados.borrar');

Route::get('/empleados/{id}/sesiones', [EmpleadosController::class, 'sesiones'])
    ->name('empleados.sesiones');

//ruta para clientes
Route::get('/clientes', [ClienteController::class, 'mostrar'])->name('views.clientes');
Route::POST('/clientes', [ClienteController::class, 'agregar'])->name('clientes.agregar');
Route::get('/clientes/modificar', [ClienteController::class, 'modificar'])->name('clientes.modificar');
Route::delete('/clientes/{id_cliente}', [ClienteController::class, 'borrar'])->name('clientes.borrar');


//rutas para compras
Route::get('/compras', [ComprasController::class, 'mostrar'])->name('views.compras');
Route::post('/compras', [ComprasController::class, 'agregar'])->name('compras.agregar');
Route::get('/compras/buscar-productos', [ComprasController::class, 'buscarProductosCompra'])->name('compras.buscar-productos');
// Ruta para obtener productos según el proveedor en select dependiente (ajax)
Route::get('/productos-por-proveedor/{id}', [ComprasController::class, 'getProductosPorProveedor'])->name('productos-por-proveedor');
Route::delete('/compras/{id_compra}', [ComprasController::class, 'eliminar'])->name('compras.eliminar');
});


//rutas presupuesto
Route::get('/presupuesto', [PresupuestoController::class, 'mostrar'])->name('views.presupuesto');
Route::post('/presupuesto/generar', [PresupuestoController::class, 'generar'])->name('presupuesto.generar');

Route::get('/ventas', function () {
    return view('welcome');
}) ->name('views.ventas');
Route::get('/historial', function () {
    return view('historial');
}) ->name('views.historial');
Route::get('/lista', function () {
    return view('lista');
}) ->name('views.lista');
Route::get('/anuladas', function () {
    return view('ventas_anu');
}) ->name('views.anuladas');




//rutas para lista
Route::get('/lista', [ListaController::class, 'mostrar'])->name('views.lista');
Route::POST('/lista', [ListaController::class, 'agregar'])->name('lista.agregar');
Route::POST('/lista/modificar', [ListaController::class, 'modificar'])->name('lista.modificar');
Route::delete('/lista/{id_producto}', [ListaController::class, 'borrar'])->name('lista.borrar');
Route::post('/lista/aumentar-precio-venta', [ListaController::class, 'aumentarPrecioPorProveedor'])->name('productos.aumentarPrecio');
Route::post('/lista/aumentar-precio-lista', [ListaController::class, 'aumentarPreciolistaPorProveedor'])->name('productos.aumentarPreciolista');




//rutas de ventas 

//Route::get('/buscar-productos', [ProductoController::class, 'buscar'])->name('productos.buscar');
// Ruta para obtener los métodos de pago
Route::get('/ventas', [VentaController::class, 'mostrar'])->name('views.ventas');
Route::get('/buscar-productos', [VentaController::class, 'buscarProductos'])->name('productos.buscar');
Route::post('/ventas/guardar', [VentaController::class, 'guardar'])->name('ventas.guardar');

Route::get('/obtener-clientes-corrientes', [VentaController::class, 'obtenerClientesCorrientes']);

// rutas de Promociones 
Route::get('/promociones', [PromocionController::class, 'index'])->name('promociones.index');
Route::post('/promociones', [PromocionController::class, 'store'])->name('promociones.store');
Route::delete('/promociones/{id}', [PromocionController::class, 'destroy'])->name('promociones.destroy');
Route::put('/promociones/{id}', [PromocionController::class, 'update'])->name('promociones.update');
Route::post('/promociones/actualizar-precios', [PromocionController::class, 'actualizarPrecios'])
    ->name('promociones.actualizar-precios');

//rutas para historial
Route::get('/historial', [HistorialController::class, 'mostrar'])->name('views.historial');
Route::get('/detalle-venta/{id}', [HistorialController::class, 'obtenerDetalleVenta'])->name('detalle.venta');
Route::get('/ticket/{idVenta}', [HistorialController::class, 'generarTicket'])->name('ventas.ticket');


// Mantenemos únicamente la ruta POST que es la que lee los datos validados desde el Form/Modal
Route::post('/ventas/anular', [HistorialController::class, 'anularVenta'])->name('ventas.anular');

Route::get('/anuladas', [VentasAnuController::class, 'index'])->name('views.anuladas');

Route::get('/ticket/{idVenta}', [HistorialController::class, 'generarTicket'])->name('ventas.ticket');

Route::fallback([FallbackController::class, 'notFound']);

// Rutas de Exportación a Excel
Route::get('exportar/ventas', [\App\Http\Controllers\ReporteController::class, 'exportarVentas'])->name('exportar.ventas');
Route::get('exportar/stock', [\App\Http\Controllers\ReporteController::class, 'exportarStock'])->name('exportar.stock');
Route::get('exportar/compras', [\App\Http\Controllers\ReporteController::class, 'exportarCompras'])->name('exportar.compras');
Route::get('exportar/gastos', [\App\Http\Controllers\ReporteController::class, 'exportarGastos'])->name('exportar.gastos');
Route::get('exportar/ventas-anuladas', [\App\Http\Controllers\ReporteController::class, 'exportarVentasAnuladas'])->name('exportar.ventas_anuladas');

// ruta para backup de base de datos
Route::get('backup/database', [\App\Http\Controllers\ReporteController::class, 'backupDatabase'])->name('backup.db');


Route::get('/balances', [\App\Http\Controllers\BalanceController::class, 'mostrar'])->name('views.balances');

// ruta para imprimir y guardar presupuesto
Route::get('/presupuestos/imprimir/{id}', [PresupuestoController::class, 'imprimir'])->name('presupuesto.imprimir');

//rutas para historial de presupuestos
Route::get('/historial-presupuestos', [HistorialPresupuestoController::class, 'mostrar'])->name('views.historial_presupuestos');
Route::get('/historial-presupuestos/detalle/{id}', [HistorialPresupuestoController::class, 'obtenerDetalle']);
Route::delete('/historial-presupuestos/eliminar', [HistorialPresupuestoController::class, 'eliminar'])->name('historial.presupuestos.eliminar');
Route::get('/historial-presupuestos/convertir/{id}', [HistorialPresupuestoController::class, 'convertir'])->name('historial.presupuestos.convertir');

// Actualizar precios de un presupuesto
Route::put('/historial-presupuestos/actualizar-precios/{id}', [HistorialPresupuestoController::class, 'actualizarPrecios'])->name('historial.presupuestos.actualizar_precios');

// Editar un presupuesto por completo
Route::get('/presupuestos/editar/{id}', [PresupuestoController::class, 'editar'])->name('presupuesto.editar');
Route::put('/presupuestos/actualizar/{id}', [PresupuestoController::class, 'actualizar'])->name('presupuesto.actualizar');

// Rutas de Marcas
Route::get('/marcas', [MarcaController::class, 'index'])->name('marcas.index');
Route::post('/marcas', [MarcaController::class, 'agregar'])->name('marcas.agregar');
Route::post('/marcas/modificar', [MarcaController::class, 'modificar'])->name('marcas.modificar');
Route::delete('/marcas/{id_marca}', [MarcaController::class, 'borrar'])->name('marcas.borrar');

// Rutas de Categorías
Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias.index');
Route::post('/categorias', [CategoriaController::class, 'agregar'])->name('categorias.agregar');
Route::post('/categorias/modificar', [CategoriaController::class, 'modificar'])->name('categorias.modificar');