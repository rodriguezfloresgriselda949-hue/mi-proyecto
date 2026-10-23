@php
    $layout = (strtolower(session('rol')) === 'administrador') ? 'adm_plantilla' : 'empleado_plantilla';
@endphp

@extends($layout)

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="m-0 text-danger fw-bold"><i class="bi bi-person-lines-fill me-2"></i> Directorio de Clientes</h4>
        <small class="text-muted">Gestión de clientes y registros de contacto</small>
    </div>
    <a href="{{ route('cliente.crear') }}" class="btn btn-danger rounded-pill shadow-sm">
        <i class="bi bi-person-plus-fill me-1"></i> Nuevo Cliente
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-danger">
                    <tr>
                        <th class="rounded-start">#</th>
                        <th>Nombre y Apellido</th>
                        <th>Teléfono</th>
                        <th>Correo Electrónico</th>
                        <th class="text-center rounded-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientes as $cliente)
                        <tr>
                            <td class="fw-bold">{{ $cliente->id_cliente }}</td>
                            <td>
                                <span class="fw-bold"><i class="bi bi-person-circle text-danger me-1"></i> {{ $cliente->nombre }} {{ $cliente->apellido }}</span>
                            </td>
                            <td>
                                @if($cliente->telefono)
                                    <i class="bi bi-telephone text-secondary me-1"></i> {{ $cliente->telefono }}
                                @else
                                    <span class="text-muted">Sin teléfono</span>
                                @endif
                            </td>
                            <td>{{ $cliente->email ?? 'Sin correo' }}</td>
                            <td class="text-center">
                                @if(strtolower(session('rol')) === 'administrador')
                                    <button type="button" class="btn btn-outline-info btn-sm rounded-pill px-3 me-1" data-id="{{ $cliente->id_cliente }}" onclick="abrirHistorialCliente(this)">
                                        <i class="bi bi-info-circle-fill"></i> Más información
                                    </button>
                                @endif
                                <a href="{{ route('cliente.editar', $cliente->id_cliente) }}" class="btn btn-outline-warning btn-sm rounded-pill px-3 me-1">
                                    <i class="fa-solid fa-pen"></i> Editar
                                </a>
                                @if(strtolower(session('rol')) === 'administrador')
                                    @php
                                        $tieneRegistros = ($cliente->reservas_count + $cliente->ventas_count + $cliente->reservas_productos_count) > 0;
                                    @endphp
                                    @if($tieneRegistros)
                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" disabled title="No se puede eliminar: este cliente tiene registros asociados.">
                                            <i class="fa-solid fa-trash"></i> Eliminar
                                        </button>
                                    @else
                                    <form action="{{ route('cliente.eliminar', $cliente->id_cliente) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar este cliente?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                            <i class="fa-solid fa-trash"></i> Eliminar
                                        </button>
                                    </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No hay clientes registrados en el sistema.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(strtolower(session('rol')) === 'administrador')
    <div class="modal fade" id="historialClienteModal" tabindex="-1" aria-labelledby="historialClienteTitulo" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <div>
                        <h5 id="historialClienteTitulo" class="modal-title"><i class="bi bi-clock-history me-2"></i>Historial del cliente</h5>
                        <small id="historialClienteNombre"></small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div id="historialCargando" class="text-center py-5">
                        <div class="spinner-border text-danger" role="status"></div>
                        <p class="text-muted mt-2 mb-0">Cargando información del cliente...</p>
                    </div>
                    <div id="historialContenido" class="d-none">
                        <h6 class="text-danger fw-bold mb-3"><i class="bi bi-scissors me-2"></i>Servicios y reservas</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-hover align-middle">
                                <thead class="table-danger"><tr><th>Fecha</th><th>Hora</th><th>Servicio(s)</th><th>Empleado</th><th>Estado</th><th class="text-end">Total</th></tr></thead>
                                <tbody id="historialServicios"></tbody>
                            </table>
                        </div>

                        <h6 class="text-danger fw-bold mb-3"><i class="bi bi-bag-check-fill me-2"></i>Compras de productos</h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-danger"><tr><th>Fecha</th><th>Productos</th><th class="text-end">Total</th></tr></thead>
                                <tbody id="historialCompras"></tbody>
                            </table>
                        </div>
                    </div>
                    <div id="historialError" class="alert alert-danger d-none mb-0"></div>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection

@section('scripts')
@if(strtolower(session('rol')) === 'administrador')
<script>
    const historialClienteModal = document.getElementById('historialClienteModal');
    const historialBaseUrl = @json(url('/cliente'));
    document.body.appendChild(historialClienteModal);

    const escaparHtml = (texto) => String(texto ?? '').replace(/[&<>'"]/g, caracter => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    }[caracter]));
    const moneda = (valor) => `Bs. ${Number(valor || 0).toFixed(2)}`;
    const filaVacia = (columnas, mensaje) => `<tr><td colspan="${columnas}" class="text-center text-muted py-4">${mensaje}</td></tr>`;

    async function abrirHistorialCliente(boton) {
        const cargando = document.getElementById('historialCargando');
        const contenido = document.getElementById('historialContenido');
        const error = document.getElementById('historialError');
        const nombre = document.getElementById('historialClienteNombre');
        cargando.classList.remove('d-none');
        contenido.classList.add('d-none');
        error.classList.add('d-none');
        nombre.textContent = '';
        bootstrap.Modal.getOrCreateInstance(historialClienteModal).show();

        try {
            const respuesta = await fetch(`${historialBaseUrl}/${boton.dataset.id}/historial`, { headers: { Accept: 'application/json' } });
            if (!respuesta.ok) throw new Error('No se pudo obtener el historial del cliente.');
            const datos = await respuesta.json();
            nombre.textContent = datos.cliente;

            document.getElementById('historialServicios').innerHTML = datos.servicios.length
                ? datos.servicios.map(servicio => `<tr><td>${escaparHtml(servicio.fecha)}</td><td>${escaparHtml(servicio.hora)}</td><td>${servicio.servicios.map(escaparHtml).join('<br>')}</td><td>${escaparHtml(servicio.empleado)}</td><td><span class="badge text-bg-secondary rounded-pill">${escaparHtml(servicio.estado)}</span></td><td class="text-end fw-semibold">${moneda(servicio.total)}</td></tr>`).join('')
                : filaVacia(6, 'El cliente aún no tiene servicios o reservas registrados.');

            document.getElementById('historialCompras').innerHTML = datos.compras.length
                ? datos.compras.map(compra => `<tr><td>${escaparHtml(compra.fecha)}</td><td>${compra.productos.map(producto => `${escaparHtml(producto.nombre)} <span class="text-muted">x${producto.cantidad}</span>`).join('<br>')}</td><td class="text-end fw-semibold">${moneda(compra.total)}</td></tr>`).join('')
                : filaVacia(3, 'El cliente aún no tiene compras de productos registradas.');

            contenido.classList.remove('d-none');
        } catch (excepcion) {
            error.textContent = excepcion.message;
            error.classList.remove('d-none');
        } finally {
            cargando.classList.add('d-none');
        }
    }
</script>
@endif
@endsection
