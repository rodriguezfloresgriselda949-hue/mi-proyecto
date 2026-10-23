<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Catálogo de Productos | Miss Uñas Valentina</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style/style.css') }}">
    <style>
        .catalogo-hero {
            background: linear-gradient(135deg, #fff0f3 0%, #ffe3e8 100%);
            border-bottom: 2px solid #f8d7da;
        }
        .card-producto {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border-radius: 1rem;
        }
        .card-producto:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(220, 53, 69, 0.15) !important;
        }
        .img-producto-container {
            height: 220px;
            overflow: hidden;
            background-color: #fff5f7;
        }
        .img-producto-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .selector-producto { cursor: pointer; }
        .boton-carrito { position: fixed; right: 1.25rem; bottom: 1.25rem; z-index: 1040; }
        .qr-pago-imagen { width: min(100%, 340px); height: auto; }
        .servicios-promocion { background: radial-gradient(ellipse at center top, #fff 0%, #fff2f5 58%, #ffe4ea 100%); overflow: hidden; }
        .servicios-carrusel-marco { max-width: 1100px; margin-inline: auto; padding: 1rem 1.25rem 1.5rem; border: 1px solid rgba(220,53,69,.12); border-radius: 2rem; background: rgba(255,255,255,.58); box-shadow: 0 18px 55px rgba(119,31,49,.09); }
        .servicio-carrusel-escena { height: 470px; position: relative; perspective: 1500px; perspective-origin: 50% 43%; overflow: hidden; cursor: grab; touch-action: pan-y; }
        .servicio-carrusel-escena:active { cursor: grabbing; }
        .servicio-carrusel-anillo { position: absolute; width: 250px; height: 325px; left: 50%; top: 44%; margin-left: -125px; margin-top: -162px; transform-style: preserve-3d; }
        .servicio-carrusel-tarjeta { position: absolute; inset: 0; border: 1px solid rgba(220,53,69,.2); border-radius: 1.35rem; background: #fff; overflow: hidden; box-shadow: 0 18px 45px rgba(119,31,49,.2); backface-visibility: hidden; cursor: pointer; transition: box-shadow .2s ease, border-color .2s ease; }
        .servicio-carrusel-tarjeta:hover, .servicio-carrusel-tarjeta:focus-visible { border-color: rgba(220,53,69,.65); box-shadow: 0 22px 55px rgba(119,31,49,.28); outline: 3px solid rgba(220,53,69,.2); outline-offset: 3px; }
        .servicio-carrusel-foto, .servicio-carrusel-placeholder { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .servicio-carrusel-placeholder { background: linear-gradient(145deg, #f8d7da, #fff 55%, #f4b9c3); }
        .servicio-carrusel-titulo { position: absolute; inset: auto 0 0; padding: 2.5rem 1rem 1.2rem; color: #fff; text-align: center; background: linear-gradient(transparent, rgba(50,12,22,.88)); }
        .servicio-carrusel-titulo h3 { margin: 0; font-family: Georgia, 'Times New Roman', serif; font-size: clamp(1.2rem, 2.2vw, 1.55rem); font-weight: 600; letter-spacing: .035em; line-height: 1.2; text-shadow: 0 2px 8px rgba(0,0,0,.7); }
        .servicio-ruleta-controles { position: relative; z-index: 2; display: flex; justify-content: center; gap: .75rem; margin-top: -.25rem; }
        .servicio-ruleta-controles .btn { width: 46px; height: 46px; display: inline-flex; align-items: center; justify-content: center; }
        @keyframes girarServicios { from { transform: rotateY(0deg); } to { transform: rotateY(360deg); } }
        @media (max-width: 576px) { .servicios-carrusel-marco { padding: .5rem .25rem 1rem; border-radius: 1.25rem; } .servicio-carrusel-escena { height: 410px; perspective: 1100px; } .servicio-carrusel-anillo { width: 220px; height: 300px; margin-left: -110px; margin-top: -150px; } }
        @media (prefers-reduced-motion: reduce) { .servicio-carrusel-anillo { animation-duration: 180s; } }
        .card-producto:hover .img-producto-container img {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-light">

    <!-- Barra de Navegación Principal -->
    <header class="bg-white shadow-sm sticky-top">
        <div class="container py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center">
                <img src="{{ asset('img/logo.jpeg') }}" alt="Logo" class="rounded-circle me-2 border shadow-sm" width="45" height="45" style="object-fit:cover" onerror="this.style.display='none'">
                <div>
                    <h3 class="m-0 text-danger fw-bold" style="letter-spacing: 1px;">MISS UÑAS VALENTINA</h3>
                    <small class="text-muted fw-semibold">Belleza, Estilo y Cuidado Personal</small>
                </div>
            </div>
            <div>
                <a href="{{ route('login') }}" class="btn btn-danger rounded-pill px-4 shadow-sm fw-bold">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
                </a>
            </div>
        </div>
    </header>

    @if($servicios->isNotEmpty())
        <section class="servicios-promocion py-4" aria-label="Servicios del salón">
            <div class="container">
                <div class="text-center mb-3">
                    <span class="badge rounded-pill text-bg-danger px-3 py-2 mb-2"><i class="bi bi-stars me-1"></i> Nuestros servicios</span>
                    <h2 class="fw-bold text-danger mb-1">Un momento para consentirte</h2>
                    <p class="text-secondary mb-0">Conoce los servicios que tenemos preparados para ti.</p>
                </div>
                @php($cantidadServicios = $servicios->count())
                @php($anchoTarjetaRuleta = 250)
                @php($profundidadCarrusel = $cantidadServicios > 2 ? max(230, (int) round($anchoTarjetaRuleta / (2 * sin(pi() / $cantidadServicios)))) : 230)
                <div class="servicios-carrusel-marco">
                <div class="servicio-carrusel-escena" aria-label="Ruleta de servicios. Usa las flechas para girar">
                    <div class="servicio-carrusel-anillo" style="--profundidad: {{ $profundidadCarrusel }}px; --duracion-ruleta: {{ max(42, $cantidadServicios * 6) }}s">
                        @foreach($servicios as $indice => $servicio)
                            <article class="servicio-carrusel-tarjeta" role="button" tabindex="0" aria-label="Ver descripción de {{ $servicio->nombre }}" data-servicio-nombre="{{ $servicio->nombre }}" data-servicio-descripcion="{{ $servicio->descripcion ?: 'Un servicio pensado para realzar tu belleza y estilo.' }}" style="transform: rotateY({{ $indice * (360 / $servicios->count()) }}deg) translateZ(var(--profundidad)); animation-delay: -{{ $indice * 0.35 }}s">
                                @if($servicio->imagen && file_exists(public_path('servicios/' . $servicio->imagen)))
                                    <img src="{{ asset('servicios/' . $servicio->imagen) }}" class="servicio-carrusel-foto" alt="{{ $servicio->nombre }}">
                                @else
                                    <div class="servicio-carrusel-placeholder d-flex align-items-center justify-content-center text-danger"><i class="bi bi-flower1" style="font-size:4rem"></i></div>
                                @endif
                                <div class="servicio-carrusel-titulo"><h3>{{ $servicio->nombre }}</h3></div>
                            </article>
                        @endforeach
                    </div>
                </div>
                </div>
                <div class="servicio-ruleta-controles" aria-label="Controles de la ruleta">
                    <button type="button" class="btn btn-light border-danger text-danger rounded-circle" id="acelerarServiciosAtras" aria-label="Acelerar ruleta"><i class="bi bi-arrow-left"></i></button>
                    <button type="button" class="btn btn-light border-danger text-danger rounded-circle" id="acelerarServiciosAdelante" aria-label="Acelerar ruleta"><i class="bi bi-arrow-right"></i></button>
                </div>
                <p class="small text-center text-muted mt-3 mb-0"><i class="bi bi-hand-index-thumb me-1"></i>Usa las flechas para acelerar el giro. Selecciona una tarjeta para ver su descripción.</p>
                </div>
            </div>
        </section>
        <div class="modal fade" id="modalDescripcionServicio" tabindex="-1" aria-labelledby="modalDescripcionServicioTitulo" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header bg-danger text-white"><h5 class="modal-title" id="modalDescripcionServicioTitulo"></h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                <div class="modal-body"><p id="modalDescripcionServicioTexto" class="mb-0 text-secondary"></p></div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cerrar</button></div>
            </div></div>
        </div>
    @endif

    <!-- Encabezado de Bienvenida -->
    <section class="catalogo-hero py-5 text-center">
        <div class="container">
            <h1 class="fw-bold text-danger mb-2">Catálogo de Productos</h1>
            <p class="text-secondary mx-auto" style="max-width: 650px;">
                Explora nuestra selección exclusiva de esmaltes, insumos y productos de belleza profesional disponibles para ti en el salón.
            </p>

            <!-- Buscador -->
            <div class="row justify-content-center mt-4">
                <div class="col-md-6 col-lg-5">
                    <form method="GET" action="{{ route('inicio') }}" class="d-flex gap-2 shadow-sm rounded-pill p-1 bg-white border">
                        <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-control border-0 rounded-pill ps-3" placeholder="Buscar por producto, categoría o marca...">
                        @if(request('buscar'))
                            <a href="{{ route('inicio') }}" class="btn btn-light rounded-circle text-muted" title="Limpiar"><i class="bi bi-x-lg"></i></a>
                        @endif
                        <button type="submit" class="btn btn-danger rounded-pill px-4">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Cuadrícula de Productos -->
    <main class="container py-5">
        @if(session('success'))<div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}@if(session('whatsapp_url')) <a href="{{ session('whatsapp_url') }}" target="_blank" rel="noopener" class="alert-link">Abrir mensaje de WhatsApp</a>@endif<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        @if(session('error'))<div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
        @if(request('buscar'))
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="text-muted m-0">Resultados para: <strong class="text-danger">"{{ request('buscar') }}"</strong></h5>
                <a href="{{ route('inicio') }}" class="btn btn-sm btn-outline-secondary rounded-pill">Ver todos los productos</a>
            </div>
        @endif

        <div class="row g-4">
            @forelse($productos as $producto)
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <article class="card card-producto h-100 border-0 shadow-sm overflow-hidden bg-white">
                        <div class="img-producto-container d-flex align-items-center justify-content-center position-relative">
                            @if($producto->imagen && file_exists(public_path('productos/' . $producto->imagen)))
                                <img src="{{ asset('productos/' . $producto->imagen) }}" alt="{{ $producto->descripcion }}">
                            @else
                                <div class="text-center text-danger p-4">
                                    <i class="bi bi-image fs-1 opacity-50"></i>
                                    <small class="d-block text-muted mt-1">Sin fotografía</small>
                                </div>
                            @endif
                            @if($producto->destacado)
                                <span class="position-absolute top-0 start-0 m-2 badge bg-warning text-dark rounded-pill shadow-sm">
                                    <i class="bi bi-star-fill me-1"></i> {{ $producto->destacado }}
                                </span>
                            @endif
                        </div>

                        <div class="card-body d-flex flex-column justify-content-between p-3">
                            <div>
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">
                                    {{ $producto->categoria->nombre ?? 'General' }} &bull; {{ $producto->marca->nombre ?? 'Miss Uñas' }}
                                </small>
                                <h5 class="card-title fw-bold text-dark mt-1 mb-2" style="font-size: 1.05rem;">
                                    {{ $producto->descripcion }}
                                </h5>
                            </div>

                            <label class="btn btn-outline-danger w-100 rounded-pill mt-3 selector-producto">
                                <input type="checkbox" class="form-check-input me-2 seleccionar-producto"
                                    value="{{ $producto->id_producto }}" data-nombre="{{ $producto->descripcion }}"
                                    data-stock="{{ $producto->stock }}">
                                <i class="bi bi-cart-plus me-1"></i> Agregar al carrito
                            </label>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="card border-0 shadow-sm rounded-4 p-5 mx-auto" style="max-width: 500px;">
                        <i class="bi bi-box-seam fs-1 text-muted mb-3"></i>
                        <h4 class="text-secondary fw-bold">No hay productos disponibles</h4>
                        <p class="text-muted">No se encontraron productos registrados en este momento o que coincidan con tu búsqueda.</p>
                        @if(request('buscar'))
                            <a href="{{ route('inicio') }}" class="btn btn-outline-danger rounded-pill px-4 mx-auto">Ver todos</a>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>
    </main>

    <button type="button" id="abrirCarrito" class="btn btn-danger rounded-pill shadow-lg boton-carrito d-none">
        <i class="bi bi-bag-heart me-1"></i> Reservar (<span id="contadorCarrito">0</span>)
    </button>

    <div class="modal fade" id="modalReservaProducto" tabindex="-1" aria-labelledby="modalReservaProductoTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form id="formReservaProductos" action="{{ route('reservas.productos.store') }}" method="POST" class="modal-content border-0 shadow">
                @csrf
                <input type="hidden" name="id_cliente" id="reservaClienteId" value="{{ old('id_cliente') }}">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalReservaProductoTitulo"><i class="bi bi-box2-heart me-2"></i>Reservar producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <section id="pasoDatosCliente">
                    <div class="mb-3 position-relative">
                        <label for="buscarCliente" class="form-label fw-bold">¿Ya te registraste? Busca tu nombre</label>
                        <input type="text" id="buscarCliente" class="form-control" autocomplete="off" placeholder="Escribe al menos 2 letras">
                        <div id="resultadosClientes" class="list-group position-absolute w-100 shadow" style="z-index: 2;"></div>
                        <small class="text-muted">Al seleccionarte, tus datos se completarán automáticamente.</small>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Nombre *</label><input name="nombre" id="clienteNombre" value="{{ old('nombre') }}" class="form-control" maxlength="50" required></div>
                        <div class="col-md-6"><label class="form-label">Apellido *</label><input name="apellido" id="clienteApellido" value="{{ old('apellido') }}" class="form-control" maxlength="100" required></div>
                        <div class="col-md-6"><label class="form-label">Teléfono</label><input name="telefono" id="clienteTelefono" value="{{ old('telefono') }}" class="form-control" maxlength="20"></div>
                        <div class="col-md-6"><label class="form-label">Correo electrónico</label><input type="email" name="email" id="clienteEmail" value="{{ old('email') }}" class="form-control" maxlength="50"></div>
                    </div>
                    <button type="button" id="continuarReserva" class="btn btn-danger mt-3"><i class="bi bi-arrow-right-circle me-1"></i>Continuar</button>
                    </section>
                    <section id="pasoDetalleReserva" class="d-none mt-3">
                    <h6 class="fw-bold">Productos seleccionados</h6>
                    <div id="productosCarrito" class="table-responsive mb-3"></div>
                    <div id="alertaStock" class="alert alert-danger d-none" role="alert"></div>
                    @if(session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
                    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label fw-bold d-block">Método de pago *</label>
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="metodo_pago" id="pagoQr" value="qr" @checked(old('metodo_pago') === 'qr') required><label class="form-check-label" for="pagoQr">QR</label></div>
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="metodo_pago" id="pagoEfectivo" value="efectivo" @checked(old('metodo_pago') === 'efectivo') required><label class="form-check-label" for="pagoEfectivo">Efectivo</label></div>
                        </div>
                        <div class="col-12 d-none text-center" id="contenedorQrPago">
                            <p class="fw-semibold mb-2">Escanea este código QR para realizar el pago</p>
                            <img src="{{ asset('img/qr-pago.jpg') }}" alt="Código QR de Banco Unión para pago" class="qr-pago-imagen rounded-3 border shadow-sm">
                        </div>
                        <div class="col-md-6"><label for="fechaRecojo" class="form-label fw-bold">Fecha de recojo *</label><input type="date" id="fechaRecojo" name="fecha_recojo" class="form-control" min="{{ today()->toDateString() }}" value="{{ old('fecha_recojo') }}" required></div>
                        <div class="col-md-6"><label for="horaRecojo" class="form-label fw-bold">Hora de recojo *</label><input type="time" id="horaRecojo" name="hora_recojo" class="form-control" value="{{ old('hora_recojo') }}" required></div>
                    </div>
                    <button type="button" id="consultarWhatsApp" class="btn btn-outline-success mt-3"><i class="bi bi-whatsapp me-1"></i>Consultar precio y confirmar por WhatsApp</button>
                    </section>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button type="submit" id="confirmarReserva" class="btn btn-danger d-none"><i class="bi bi-check2-circle me-1"></i>Confirmar reserva</button></div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-top py-4 text-center text-muted mt-auto">
        <div class="container">
            <p class="mb-1 fw-bold text-danger">Salón de Belleza Miss Uñas Valentina</p>
            <small>&copy; {{ date('Y') }} Todos los derechos reservados. Sistema de Gestión y Catálogo.</small>
        </div>
    </footer>

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script>
        const modalReserva = document.getElementById('modalReservaProducto');
        const escenaServicios = document.querySelector('.servicio-carrusel-escena');
        if (escenaServicios) {
            const modalDescripcion = document.getElementById('modalDescripcionServicio');
            const anilloServicios = escenaServicios.querySelector('.servicio-carrusel-anillo');
            const tarjetasServicios = [...escenaServicios.querySelectorAll('.servicio-carrusel-tarjeta')];
            const velocidadBase = 360 / Math.max(42, tarjetasServicios.length * 6);
            let anguloRuleta = 0;
            let velocidadRuleta = velocidadBase;
            let impulsoRuleta = 0;
            let ultimoFotograma = 0;
            const acelerarRuleta = () => { impulsoRuleta = Math.min(velocidadBase * 3, impulsoRuleta + velocidadBase * 1.5); };
            document.getElementById('acelerarServiciosAtras').addEventListener('click', acelerarRuleta);
            document.getElementById('acelerarServiciosAdelante').addEventListener('click', acelerarRuleta);
            const animarRuleta = tiempo => {
                if (ultimoFotograma) {
                    const delta = Math.min((tiempo - ultimoFotograma) / 1000, 0.05);
                    velocidadRuleta += (velocidadBase + impulsoRuleta - velocidadRuleta) * Math.min(delta * 1.5, 1);
                    impulsoRuleta = Math.max(0, impulsoRuleta - velocidadBase * delta * 0.7);
                    anguloRuleta = (anguloRuleta + velocidadRuleta * delta) % 360;
                    anilloServicios.style.transform = `rotateY(${anguloRuleta}deg)`;
                }
                ultimoFotograma = tiempo;
                requestAnimationFrame(animarRuleta);
            };
            requestAnimationFrame(animarRuleta);
            const mostrarDescripcionServicio = tarjeta => {
                document.getElementById('modalDescripcionServicioTitulo').textContent = tarjeta.dataset.servicioNombre;
                document.getElementById('modalDescripcionServicioTexto').textContent = tarjeta.dataset.servicioDescripcion;
                bootstrap.Modal.getOrCreateInstance(modalDescripcion).show();
            };
            document.querySelectorAll('.servicio-carrusel-tarjeta').forEach(tarjeta => {
                tarjeta.addEventListener('click', () => mostrarDescripcionServicio(tarjeta));
                tarjeta.addEventListener('keydown', evento => {
                    if (evento.key === 'Enter' || evento.key === ' ') {
                        evento.preventDefault();
                        mostrarDescripcionServicio(tarjeta);
                    }
                });
            });
        }
        const carrito = new Map();
        const checkboxes = document.querySelectorAll('.seleccionar-producto');
        const botonCarrito = document.getElementById('abrirCarrito');
        function actualizarCarrito() {
            document.getElementById('contadorCarrito').textContent = carrito.size;
            botonCarrito.classList.toggle('d-none', carrito.size === 0);
            checkboxes.forEach(c => c.closest('label').classList.toggle('active', c.checked));
        }
        checkboxes.forEach(c => c.addEventListener('change', () => {
            if (c.checked) carrito.set(c.value, { id: c.value, nombre: c.dataset.nombre, stock: Number(c.dataset.stock) });
            else carrito.delete(c.value);
            actualizarCarrito();
        }));
        function renderizarCarrito() {
            const contenedor = document.getElementById('productosCarrito');
            contenedor.innerHTML = '';
            const tabla = document.createElement('table'); tabla.className = 'table align-middle';
            tabla.innerHTML = '<thead><tr><th>Producto</th><th style="width:130px">Cantidad</th><th></th></tr></thead>';
            const cuerpo = document.createElement('tbody');
            [...carrito.values()].forEach((producto, indice) => {
                const fila = document.createElement('tr');
                const nombre = document.createElement('td'); nombre.textContent = producto.nombre;
                const cantidad = document.createElement('td');
                const input = document.createElement('input'); input.type = 'number'; input.className = 'form-control cantidad-reserva'; input.min = '1'; input.value = producto.cantidad || '1'; input.required = true; input.name = `productos[${indice}][cantidad]`; input.dataset.productoId = producto.id;
                const id = document.createElement('input'); id.type = 'hidden'; id.name = `productos[${indice}][id_producto]`; id.value = producto.id;
                cantidad.append(input, id);
                const quitar = document.createElement('td'); const borrar = document.createElement('button'); borrar.type = 'button'; borrar.className = 'btn btn-sm btn-outline-secondary'; borrar.innerHTML = '<i class="bi bi-x-lg"></i>'; borrar.setAttribute('aria-label', 'Quitar producto'); borrar.addEventListener('click', () => { carrito.delete(producto.id); document.querySelector(`.seleccionar-producto[value="${producto.id}"]`).checked = false; actualizarCarrito(); renderizarCarrito(); }); quitar.append(borrar);
                fila.append(nombre, cantidad, quitar); cuerpo.append(fila);
            });
            tabla.append(cuerpo); contenedor.append(tabla);
        }
        botonCarrito.addEventListener('click', () => { renderizarCarrito(); bootstrap.Modal.getOrCreateInstance(modalReserva).show(); });
        document.getElementById('formReservaProductos').addEventListener('submit', evento => {
            const alerta = document.getElementById('alertaStock');
            const cantidades = [...document.querySelectorAll('.cantidad-reserva')];
            const excedido = cantidades.find(input => Number(input.value) > carrito.get(input.dataset.productoId)?.stock);
            if (excedido) {
                evento.preventDefault();
                const producto = carrito.get(excedido.dataset.productoId);
                alerta.textContent = `Stock insuficiente para ${producto.nombre}. Ajusta la cantidad solicitada e inténtalo nuevamente.`;
                alerta.classList.remove('d-none');
                excedido.focus();
                return;
            }
            alerta.classList.add('d-none');
        });
        const etapaCliente = document.getElementById('pasoDatosCliente');
        const etapaReserva = document.getElementById('pasoDetalleReserva');
        const botonConfirmar = document.getElementById('confirmarReserva');
        document.getElementById('continuarReserva').addEventListener('click', () => {
            const nombre = document.getElementById('clienteNombre');
            const apellido = document.getElementById('clienteApellido');
            if (!nombre.reportValidity() || !apellido.reportValidity()) return;
            etapaCliente.classList.add('d-none'); etapaReserva.classList.remove('d-none'); botonConfirmar.classList.remove('d-none');
        });
        document.getElementById('consultarWhatsApp').addEventListener('click', () => {
            const lineas = [...carrito.values()].map(p => `- ${p.nombre} × ${document.querySelector(`.cantidad-reserva[data-producto-id="${p.id}"]`).value}`).join('\n');
            const fecha = document.getElementById('fechaRecojo').value || 'por coordinar';
            const hora = document.getElementById('horaRecojo').value || 'por coordinar';
            const metodo = document.querySelector('input[name="metodo_pago"]:checked')?.value || 'por definir';
            const cliente = document.getElementById('clienteNombre').value.trim();
            const texto = `Hola, soy ${cliente}. Quiero consultar el precio y confirmar mi reserva de estos productos:\n${lineas}\nRecojo: ${fecha} a las ${hora}. Pago: ${metodo}. ¿Me confirman, por favor?`;
            window.open(`https://wa.me/59174156411?text=${encodeURIComponent(texto)}`, '_blank', 'noopener');
        });
        const contenedorQrPago = document.getElementById('contenedorQrPago');
        document.querySelectorAll('input[name="metodo_pago"]').forEach(opcion => opcion.addEventListener('change', () => {
            contenedorQrPago.classList.toggle('d-none', document.getElementById('pagoQr').checked === false);
        }));
        contenedorQrPago.classList.toggle('d-none', !document.getElementById('pagoQr').checked);
        const resultados = document.getElementById('resultadosClientes');
        const buscarCliente = document.getElementById('buscarCliente');
        buscarCliente.addEventListener('input', async () => {
            const texto = buscarCliente.value.trim();
            document.getElementById('reservaClienteId').value = '';
            resultados.innerHTML = '';
            if (texto.length < 2) return;
            const respuesta = await fetch(`{{ route('clientes.buscarAjax') }}?buscar=${encodeURIComponent(texto)}`);
            const clientes = await respuesta.json();
            clientes.forEach(cliente => {
                const opcion = document.createElement('button');
                opcion.type = 'button'; opcion.className = 'list-group-item list-group-item-action';
                opcion.textContent = `${cliente.nombre} ${cliente.apellido} — ${cliente.telefono || cliente.email || 'sin contacto'}`;
                opcion.addEventListener('click', () => {
                    document.getElementById('reservaClienteId').value = cliente.id_cliente;
                    document.getElementById('clienteNombre').value = cliente.nombre || '';
                    document.getElementById('clienteApellido').value = cliente.apellido || '';
                    document.getElementById('clienteTelefono').value = cliente.telefono || '';
                    document.getElementById('clienteEmail').value = cliente.email || '';
                    buscarCliente.value = `${cliente.nombre} ${cliente.apellido}`;
                    resultados.innerHTML = '';
                });
                resultados.appendChild(opcion);
            });
        });
        const productosAnteriores = @json(old('productos', []));
        if (Array.isArray(productosAnteriores) && productosAnteriores.length) {
            productosAnteriores.forEach(anterior => {
                const checkbox = [...checkboxes].find(c => c.value === String(anterior.id_producto));
                if (!checkbox) return;
                checkbox.checked = true;
                carrito.set(checkbox.value, { id: checkbox.value, nombre: checkbox.dataset.nombre, stock: Number(checkbox.dataset.stock), cantidad: anterior.cantidad });
            });
            actualizarCarrito();
            renderizarCarrito();
            etapaCliente.classList.add('d-none'); etapaReserva.classList.remove('d-none'); botonConfirmar.classList.remove('d-none');
            bootstrap.Modal.getOrCreateInstance(modalReserva).show();
        }
        @if(session('error') || $errors->any())
            bootstrap.Modal.getOrCreateInstance(modalReserva).show();
        @endif
        @if(session('whatsapp_url')) window.open(@json(session('whatsapp_url')), '_blank', 'noopener'); @endif
    </script>
</body>
</html>
