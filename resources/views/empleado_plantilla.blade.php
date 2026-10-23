<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Salón Miss Uñas Valentina - Panel del Personal</title>
    
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/select-buscable.css') }}?v=2">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style/style.css') }}?v=menu-compacto-20261006">
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
        <h4 class="m-0 text-danger fw-bold d-flex align-items-center"><i class="bi bi-scissors me-2"></i> Salón Miss Uñas Valentina</h4>
        <div class="d-flex align-items-center">
            <span class="badge bg-danger rounded-pill px-3 py-2 me-3">
                <i class="bi bi-person-heart"></i> {{ session('username') }} (Personal)
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

    <div class="row g-4 panel-layout-row">
        <!-- Menú Lateral Empleado -->
        <div class="col-lg-3 panel-menu-col" id="menuLateral">
            <div class="sidebar">
                <h5 class="d-flex justify-content-between align-items-center mb-3"><span class="menu-title"><i class="bi bi-grid-fill me-1"></i>Mi Menú</span><button type="button" class="btn btn-sm btn-outline-danger rounded-circle menu-toggle" id="alternarMenuLateral" aria-controls="menuLateral" aria-expanded="true" aria-label="Contraer menú" title="Contraer menú"><i class="bi bi-chevron-left"></i></button></h5>
                
                <a href="{{ route('empleado.dashboard') }}" aria-label="Mi agenda" title="Mi agenda" class="btn-menu {{ request()->routeIs('empleado.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-calendar-day-fill me-2"></i><span class="menu-label">Mi Agenda</span>
                </a>

                <a href="{{ route('empleado.perfil') }}" aria-label="Mi perfil" title="Mi perfil" class="btn-menu {{ request()->routeIs('empleado.perfil*') ? 'active' : '' }}">
                    <i class="bi bi-person-circle me-2"></i><span class="menu-label">Mi Perfil</span>
                </a>
                
                <a href="{{ route('cliente.lista') }}" aria-label="Clientes" title="Clientes" class="btn-menu {{ request()->routeIs('cliente.*') ? 'active' : '' }}">
                    <i class="bi bi-person-lines-fill me-2"></i><span class="menu-label">Clientes</span>
                </a>
                
                <a href="{{ route('reservas.index') }}" aria-label="Todas las reservas" title="Todas las reservas" class="btn-menu {{ request()->routeIs('reservas.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check-fill me-2"></i><span class="menu-label">Todas las Reservas</span>
                </a>
                
                <a href="{{ route('servicio.lista') }}" aria-label="Servicios" title="Servicios" class="btn-menu {{ request()->routeIs('servicio.*') ? 'active' : '' }}">
                    <i class="bi bi-flower1 me-2"></i><span class="menu-label">Servicios</span>
                </a>

                <a href="{{ route('productos.index') }}" aria-label="Consultar stock" title="Consultar stock" class="btn-menu {{ request()->routeIs('productos.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam-fill me-2"></i><span class="menu-label">Consultar Stock</span>
                </a>
                
                <a href="{{ route('ventas.create') }}" aria-label="Registrar venta" title="Registrar venta" class="btn-menu {{ request()->routeIs('ventas.create') ? 'active' : '' }}">
                    <i class="bi bi-cart-plus-fill me-2"></i><span class="menu-label">Registrar Venta</span>
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
        Salón de Belleza <b>Miss Uñas Valentina</b> &copy; {{ date('Y') }} - Panel del Personal
    </div>
</div>

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

