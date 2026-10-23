<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\PromocionController;
use App\Http\Controllers\ReservaPromocionController;
use App\Http\Controllers\ReservasController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ReservaProductoController;
use App\Http\Controllers\PagoController;


/*
|--------------------------------------------------------------------------
| Web Routes - Salón de Belleza Miss Uñas Valentina
|--------------------------------------------------------------------------
*/


// ==========================================================
// RUTAS PÚBLICAS
// ==========================================================

Route::get(
    '/',
    [ProductoController::class, 'catalogo']
)->name('inicio');

Route::post('/reserva-producto/guardar', [ReservaProductoController::class, 'store'])
    ->name('reservas.productos.store');
Route::get('/api/clientes/buscar', [ReservaProductoController::class, 'buscarClientes'])
    ->name('clientes.buscarAjax');

Route::get('/login', [UsuarioController::class, 'index'])->name('login');


Route::post(
    '/validar',
    [UsuarioController::class, 'validar']
)->name('validar');


Route::post(
    '/logout',
    [UsuarioController::class, 'cerrar']
)->name('logout');


Route::get(
    '/volver',
    [UsuarioController::class, 'volver']
)->name('volver');


// ==========================================================
// RUTAS PROTEGIDAS
// ==========================================================

Route::middleware(['check.auth'])->group(function () {


    // ======================================================
    // DASHBOARDS
    // ======================================================

    Route::get(
        '/admin',
        [UsuarioController::class, 'adminDashboard']
    )
    ->middleware('check.auth:admin')
    ->name('admin.dashboard');

    Route::get('/admin/calendario-reservas', [UsuarioController::class, 'reservasPorDiaCalendario'])
        ->middleware('check.auth:admin')
        ->name('admin.calendario.reservas');


    Route::get(
        '/empleado',
        [UsuarioController::class, 'empleadoDashboard']
    )
    ->name('empleado.dashboard');

    // Perfil propio del personal. Los datos administrativos se gestionan solo desde Usuarios.
    Route::get('/mi-perfil', [UsuarioController::class, 'perfilEmpleado'])
        ->middleware('check.auth:personal')
        ->name('empleado.perfil');
    Route::put('/mi-perfil', [UsuarioController::class, 'actualizarPerfilEmpleado'])
        ->middleware('check.auth:personal')
        ->name('empleado.perfil.update');


    Route::post(
        '/reservas/{id}/cambiar-estado',
        [UsuarioController::class, 'cambiarEstadoReserva']
    )
    ->name('reservas.cambiarEstado');


    // ======================================================
    // USUARIOS - SOLO ADMINISTRADOR
    // ======================================================

    Route::middleware('check.auth:admin')->group(function () {

        Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
        Route::post('/pagos/registrar', [PagoController::class, 'registrarPago'])->name('pagos.registrar');
        Route::get('/pagos/{id}/comprobante', [PagoController::class, 'comprobante'])->name('pagos.comprobante');
        Route::post('/adelantos', [PagoController::class, 'guardarAdelanto'])->name('adelantos.store');

        // Vista
        Route::get(
            '/usuarios',
            [UsuarioController::class, 'usuarios']
        )
        ->name('usuarios.index');


        // Listar
        Route::get(
            '/usuarios/listar',
            [UsuarioController::class, 'listar']
        )
        ->name('usuarios.listar');


        // Obtener uno
        Route::get(
            '/usuarios/{id}',
            [UsuarioController::class, 'obtener']
        )
        ->name('usuarios.obtener');


        // INSERTAR
        Route::post(
            '/usuarios',
            [UsuarioController::class, 'registrar']
        )
        ->name('usuarios.registrar');


        // ACTUALIZAR
        Route::put(
            '/usuarios/{id}',
            [UsuarioController::class, 'actualizar']
        )
        ->name('usuarios.actualizar');


        // ELIMINAR
        Route::delete(
            '/usuarios/{id}',
            [UsuarioController::class, 'eliminar']
        )
        ->name('usuarios.eliminar');

    });


    // ======================================================
    // CLIENTES
    // ======================================================

    Route::get(
        '/clientes',
        [ClienteController::class, 'index']
    )
    ->name('cliente.lista');

    Route::get('/cliente/{id}/historial', [ClienteController::class, 'historial'])
        ->middleware('check.auth:admin')
        ->name('cliente.historial');


    Route::get(
        '/cliente/nuevo',
        [ClienteController::class, 'create']
    )
    ->name('cliente.crear');


    Route::post(
        '/cliente/guardar',
        [ClienteController::class, 'store']
    )
    ->name('cliente.guardar');


    Route::get(
        '/cliente/editar/{id}',
        [ClienteController::class, 'edit']
    )
    ->name('cliente.editar');


    Route::put(
        '/cliente/actualizar/{id}',
        [ClienteController::class, 'update']
    )
    ->name('cliente.actualizar');


    Route::delete(
        '/cliente/eliminar/{id}',
        [ClienteController::class, 'destroy']
    )
    ->name('cliente.eliminar');


    // ======================================================
    // SERVICIOS
    // ======================================================

    Route::get(
        '/servicios',
        [ServicioController::class, 'index']
    )
    ->name('servicio.lista');

    Route::get(
        '/servicio/{id}/informacion',
        [ServicioController::class, 'informacion']
    )
    ->middleware('check.auth:administrador')
    ->name('servicio.informacion');


    Route::get(
        '/servicio/nuevo',
        [ServicioController::class, 'create']
    )
    ->name('servicio.crear');


    Route::post(
        '/servicio/guardar',
        [ServicioController::class, 'store']
    )
    ->name('servicio.guardar');


    Route::get(
        '/servicio/editar/{id}',
        [ServicioController::class, 'edit']
    )
    ->name('servicio.editar');


    Route::put(
        '/servicio/actualizar/{id}',
        [ServicioController::class, 'update']
    )
    ->name('servicio.actualizar');


    Route::delete(
        '/servicio/eliminar/{id}',
        [ServicioController::class, 'destroy']
    )
    ->name('servicio.eliminar');

    Route::middleware('check.auth:administrador')->group(function () {
        Route::get('/promociones/nueva', [PromocionController::class, 'create'])->name('promocion.crear');
        Route::post('/promociones', [PromocionController::class, 'store'])->name('promocion.guardar');
        Route::get('/promociones/{id}/editar', [PromocionController::class, 'edit'])->name('promocion.editar');
        Route::put('/promociones/{id}', [PromocionController::class, 'update'])->name('promocion.actualizar');
        Route::delete('/promociones/{id}', [PromocionController::class, 'destroy'])->name('promocion.eliminar');
        Route::get('/promociones/{id}/informacion', [PromocionController::class, 'informacion'])->name('promocion.informacion');
        Route::get('/reservas-promociones', [ReservaPromocionController::class, 'index'])->name('reservas.promociones.index');
        Route::get('/reservas-promociones/nueva', [ReservaPromocionController::class, 'create'])->name('reservas.promociones.create');
        Route::post('/reservas-promociones', [ReservaPromocionController::class, 'store'])->name('reservas.promociones.store');
        Route::get('/reservas-promociones/{id}/editar', [ReservaPromocionController::class, 'edit'])->name('reservas.promociones.edit');
        Route::put('/reservas-promociones/{id}', [ReservaPromocionController::class, 'update'])->name('reservas.promociones.update');
        Route::delete('/reservas-promociones/{id}', [ReservaPromocionController::class, 'destroy'])->name('reservas.promociones.destroy');
        Route::get('/reservas-promociones/{id}/comprobante', [ReservaPromocionController::class, 'comprobante'])->name('reservas.promociones.comprobante');
        Route::post('/reservas-promociones/{id}/cambiar-estado', [ReservaPromocionController::class, 'cambiarEstado'])->name('reservas.promociones.cambiarEstado');
    });


    // ======================================================
    // RESERVAS
    // ======================================================

    Route::get(
        '/reservas',
        [ReservasController::class, 'index']
    )
    ->name('reservas.index');

    Route::get(
        '/reserva/{id}/comprobante',
        [ReservasController::class, 'comprobante']
    )
    ->name('reservas.comprobante');


    Route::get(
        '/reserva/nueva',
        [ReservasController::class, 'create']
    )
    ->name('reservas.create');

    Route::get('/reserva/horas-disponibles', [ReservasController::class, 'horasDisponibles'])
        ->name('reservas.horasDisponibles');


    Route::post(
        '/reserva/guardar',
        [ReservasController::class, 'store']
    )
    ->name('reservas.store');


    Route::get(
        '/reserva/editar/{id}',
        [ReservasController::class, 'edit']
    )
    ->name('reservas.edit');


    Route::put(
        '/reserva/actualizar/{id}',
        [ReservasController::class, 'update']
    )
    ->name('reservas.update');

    Route::post(
        '/reserva/{id}/traspasar-servicio',
        [ReservasController::class, 'traspasarServicio']
    )
    ->name('reservas.traspasar');


    Route::delete(
        '/reserva/eliminar/{id}',
        [ReservasController::class, 'destroy']
    )
    ->name('reservas.destroy');


    // ======================================================
    // PRODUCTOS
    // ======================================================

    Route::get(
        '/productos',
        [ProductoController::class, 'index']
    )
    ->middleware('check.auth:administrador,personal')
    ->name('productos.index');


    Route::middleware('check.auth:admin')->group(function () {

        // Categorías de productos
        Route::post('/categorias', [ProductoController::class, 'guardarCategoria'])
            ->name('categorias.store');
        Route::put('/categorias/{id}', [ProductoController::class, 'actualizarCategoria'])
            ->name('categorias.update');
        Route::delete('/categorias/{id}', [ProductoController::class, 'eliminarCategoria'])
            ->name('categorias.destroy');

        Route::get('/productos-reservados', [ReservaProductoController::class, 'index'])
            ->name('reservas.productos.index');
        Route::patch('/productos-reservados/{id}/estado', [ReservaProductoController::class, 'actualizarEstado'])
            ->name('reservas.productos.estado');
        Route::post('/productos-reservados/estado-grupo', [ReservaProductoController::class, 'actualizarEstadoGrupo'])
            ->name('reservas.productos.estadoGrupo');
        Route::post('/productos-reservados/confirmar-grupo', [ReservaProductoController::class, 'confirmarGrupo'])
            ->name('reservas.productos.confirmarGrupo');

        Route::get(
            '/producto/nuevo',
            [ProductoController::class, 'create']
        )
        ->name('productos.create');


        Route::post(
            '/producto/guardar',
            [ProductoController::class, 'store']
        )
        ->name('productos.store');


        Route::get(
            '/producto/editar/{id}',
            [ProductoController::class, 'edit']
        )
        ->name('productos.edit');


        Route::put(
            '/producto/actualizar/{id}',
            [ProductoController::class, 'update']
        )
        ->name('productos.update');


        Route::delete(
            '/producto/eliminar/{id}',
            [ProductoController::class, 'destroy']
        )
        ->name('productos.destroy');

    });


    Route::get(
        '/api/productos/buscar',
        [ProductoController::class, 'buscarAjax']
    )
    ->name('productos.buscarAjax');


    // ======================================================
    // PROVEEDORES
    // ======================================================

    Route::middleware('check.auth:admin')->group(function () {

        Route::get(
            '/proveedores',
            [ProveedorController::class, 'index']
        )
        ->name('proveedores.index');


        Route::get(
            '/proveedor/nuevo',
            [ProveedorController::class, 'create']
        )
        ->name('proveedores.create');


        Route::post(
            '/proveedor/guardar',
            [ProveedorController::class, 'store']
        )
        ->name('proveedores.store');


        Route::get(
            '/proveedor/editar/{id}',
            [ProveedorController::class, 'edit']
        )
        ->name('proveedores.edit');


        Route::put(
            '/proveedor/actualizar/{id}',
            [ProveedorController::class, 'update']
        )
        ->name('proveedores.update');


        Route::delete(
            '/proveedor/eliminar/{id}',
            [ProveedorController::class, 'destroy']
        )
        ->name('proveedores.destroy');

    });


    // ======================================================
    // VENTAS
    // ======================================================

    Route::get(
        '/ventas',
        [VentaController::class, 'index']
    )
    ->middleware('check.auth:admin')
    ->name('ventas.index');


    Route::get(
        '/venta/nueva',
        [VentaController::class, 'create']
    )
    ->name('ventas.create');


    Route::post(
        '/venta/guardar',
        [VentaController::class, 'store']
    )
    ->name('ventas.store');


    Route::get(
        '/venta/{id}',
        [VentaController::class, 'show']
    )
    ->name('ventas.show');


    Route::delete(
        '/venta/{id}',
        [VentaController::class, 'destroy']
    )
    ->middleware('check.auth:admin')
    ->name('ventas.destroy');


    // ======================================================
    // COMPRAS
    // ======================================================

    Route::middleware('check.auth:admin')->group(function () {

        Route::get(
            '/compras',
            [CompraController::class, 'index']
        )
        ->name('compras.index');


        Route::get(
            '/compra/nueva',
            [CompraController::class, 'create']
        )
        ->name('compras.create');


        Route::post(
            '/compra/guardar',
            [CompraController::class, 'store']
        )
        ->name('compras.store');

        Route::get(
            '/compras/{id}/comprobante',
            [CompraController::class, 'comprobante']
        )
        ->name('compras.comprobante');

    });


    // ======================================================
    // REPORTES
    // ======================================================

    Route::get(
        '/reportes',
        [ReporteController::class, 'index']
    )
    ->middleware('check.auth:admin')
    ->name('reportes.index');

});
