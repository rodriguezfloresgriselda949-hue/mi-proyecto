<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Salón Miss Uñas Valentina - Panel Administrativo</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/select-buscable.css') }}?v=2">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Custom Style -->
    <link rel="stylesheet" href="{{ asset('style/style.css') }}?v=menu-compacto-20261006">

    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('datatables/datatables.min.css') }}">
</head>

<body>

<div class="container-fluid px-3 px-xl-4 my-3">
    <!-- Banner Principal -->
    <div class="banner rounded-4 shadow-sm overflow-hidden mb-3">
        <img src="{{ asset('img/admin.png') }}" class="w-100 h-100 object-fit-cover" alt="Banner Salón Miss Uñas">
    </div>

    <!-- Barra Superior -->
    <div class="topbar d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0 text-danger fw-bold d-flex align-items-center"><i class="bi bi-stars me-2"></i> Salón Miss Uñas Valentina</h4>
        <div class="d-flex align-items-center">
            <span class="badge bg-danger rounded-pill px-3 py-2 me-3">
                <i class="bi bi-person-fill"></i> {{ session('username') }} ({{ session('rol') }})
            </span>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill fw-bold">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                </button>
            </form>
        </div>
    </div>

    <!-- Mensajes Globales -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i> {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4 panel-layout-row">
        <!-- Menú Lateral -->
        <div class="col-lg-3 panel-menu-col" id="menuLateral">
            <div class="sidebar">
                <h5 class="d-flex justify-content-between align-items-center mb-3"><span class="menu-title"><i class="bi bi-grid-fill me-1"></i>Menú Principal</span><button type="button" class="btn btn-sm btn-outline-danger rounded-circle menu-toggle" id="alternarMenuLateral" aria-controls="menuLateral" aria-expanded="true" aria-label="Contraer menú" title="Contraer menú"><i class="bi bi-chevron-left"></i></button></h5>

                <a href="{{ route('admin.dashboard') }}" aria-label="Inicio" title="Inicio" class="btn-menu {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-house-door-fill me-2"></i><span class="menu-label">Inicio</span>
                </a>

                <a href="{{ route('usuarios.index') }}" aria-label="Usuarios" title="Usuarios" class="btn-menu {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill me-2"></i><span class="menu-label">Usuarios</span>
                </a>

                <a href="{{ route('cliente.lista') }}" aria-label="Clientes" title="Clientes" class="btn-menu {{ request()->routeIs('cliente.*') ? 'active' : '' }}">
                    <i class="bi bi-person-lines-fill me-2"></i><span class="menu-label">Clientes</span>
                </a>

                <a href="{{ route('reservas.index') }}" aria-label="Reservas" title="Reservas" class="btn-menu {{ request()->routeIs('reservas.*') && !request()->routeIs('reservas.promociones.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check-fill me-2"></i><span class="menu-label">Reservas</span>
                </a>

                <a href="{{ route('reservas.promociones.index') }}" aria-label="Reservas de promociones" title="Reservas de promociones" class="btn-menu {{ request()->routeIs('reservas.promociones.*') ? 'active' : '' }}">
                    <i class="bi bi-tags-fill me-2"></i><span class="menu-label">Reservas de promociones</span>
                </a>

                <a href="{{ route('servicio.lista') }}" aria-label="Servicios" title="Servicios" class="btn-menu {{ request()->routeIs('servicio.*') ? 'active' : '' }}">
                    <i class="bi bi-flower1 me-2"></i><span class="menu-label">Servicios</span>
                </a>

                <a href="{{ route('productos.index') }}" aria-label="Productos y stock" title="Productos y stock" class="btn-menu {{ request()->routeIs('productos.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam-fill me-2"></i><span class="menu-label">Productos / Stock</span>
                </a>

                <a href="{{ route('reservas.productos.index') }}" aria-label="Productos reservados" title="Productos reservados" class="btn-menu {{ request()->routeIs('reservas.productos.*') ? 'active' : '' }}">
                    <i class="bi bi-box2-heart-fill me-2"></i><span class="menu-label">Productos reservados</span>
                </a>

                <a href="{{ route('proveedores.index') }}" aria-label="Proveedores" title="Proveedores" class="btn-menu {{ request()->routeIs('proveedores.*') ? 'active' : '' }}">
                    <i class="bi bi-truck me-2"></i><span class="menu-label">Proveedores</span>
                </a>

                <a href="{{ route('ventas.index') }}" aria-label="Ventas" title="Ventas" class="btn-menu {{ request()->routeIs('ventas.*') ? 'active' : '' }}">
                    <i class="bi bi-cart-fill me-2"></i><span class="menu-label">Ventas</span>
                </a>

                <a href="{{ route('compras.index') }}" aria-label="Compras" title="Compras" class="btn-menu {{ request()->routeIs('compras.*') ? 'active' : '' }}">
                    <i class="bi bi-bag-plus-fill me-2"></i><span class="menu-label">Compras</span>
                </a>

                <a href="{{ route('pagos.index') }}" aria-label="Pagos" title="Pagos" class="btn-menu {{ request()->routeIs('pagos.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-coin me-2"></i><span class="menu-label">Pagos</span>
                </a>

                <a href="{{ route('reportes.index') }}" aria-label="Reportes" title="Reportes" class="btn-menu {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-line-fill me-2"></i><span class="menu-label">Reportes</span>
                </a>
            </div>
        </div>

        <!-- Contenido Central -->
        <div class="col-lg-9 panel-content-col">
            <div class="content">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Pie de página -->
    <div class="footer text-center mt-4">
        Salón de Belleza <b>Miss Uñas Valentina</b> &copy; {{ date('Y') }} - Sistema de Gestión
    </div>
</div>

<!-- Scripts -->
<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('js/select-buscable.js') }}?v=2" defer></script>
<script src="{{ asset('datatables/datatables.min.js') }}"></script>
<script src="{{ asset('js/form-validation.js') }}"></script>
<script>
(() => {
    const boton = document.getElementById('alternarMenuLateral');
    if (!boton) return;
    const menu = document.getElementById('menuLateral');
    const contenido = document.querySelector('.panel-content-col');
    const clave = 'menuLateralCompacto';
    const cambiar = compacto => {
        document.body.classList.toggle('menu-lateral-compacto', compacto);
        if (menu && contenido) {
            menu.style.flex = compacto ? '0 0 88px' : '';
            menu.style.width = compacto ? '88px' : '';
            menu.style.maxWidth = compacto ? '88px' : '';
            contenido.style.flex = compacto ? '1 0 0%' : '';
            contenido.style.width = compacto ? 'auto' : '';
            contenido.style.maxWidth = compacto ? 'none' : '';
        }
        boton.setAttribute('aria-expanded', String(!compacto));
        boton.setAttribute('aria-label', compacto ? 'Expandir menú' : 'Contraer menú');
        boton.title = compacto ? 'Expandir menú' : 'Contraer menú';
        boton.innerHTML = `<i class="bi ${compacto ? 'bi-chevron-right' : 'bi-chevron-left'}"></i>`;
    };
    let compacto = false;
    try { compacto = localStorage.getItem(clave) === 'true'; } catch (_) {}
    cambiar(compacto);
    boton.addEventListener('click', () => {
        compacto = !document.body.classList.contains('menu-lateral-compacto');
        cambiar(compacto);
        try { localStorage.setItem(clave, String(compacto)); } catch (_) {}
    });
})();
</script>
@yield('scripts')

</body>
</html>

