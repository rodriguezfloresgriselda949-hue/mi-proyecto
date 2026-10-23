@php
    $layout = (strtolower(session('rol')) === 'administrador') ? 'adm_plantilla' : 'empleado_plantilla';
@endphp

@extends($layout)

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="m-0 text-danger fw-bold"><i class="bi bi-flower1 me-2"></i> Catálogo de Servicios</h4>
        <small class="text-muted">Servicios ofrecidos en el salón: manicure, pedicure, uñas acrílicas y más</small>
    </div>
    @if(strtolower(session('rol')) === 'administrador')
        <a href="{{ route('servicio.crear') }}" class="btn btn-danger rounded-pill shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Servicio
        </a>
    @endif
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-danger">
                    <tr>
                        <th class="rounded-start">#</th>
                        <th>Imagen</th>
                        <th>Nombre del Servicio</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        @if(strtolower(session('rol')) === 'administrador')
                            <th class="text-center rounded-end">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($servicios as $servicio)
                        <tr>
                            <td class="fw-bold">{{ $servicio->id_servicio }}</td>
                            <td>
                                @if($servicio->imagen && file_exists(public_path('servicios/' . $servicio->imagen)))
                                    <img src="{{ asset('servicios/' . $servicio->imagen) }}" alt="{{ $servicio->nombre }}" class="rounded-3 border" style="width:72px;height:56px;object-fit:cover">
                                @else
                                    <span class="text-muted small">Sin imagen</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold"><i class="bi bi-stars text-danger me-1"></i> {{ $servicio->nombre }}</span>
                            </td>
                            <td>{{ $servicio->descripcion ? \Illuminate\Support\Str::lower($servicio->descripcion) : 'Sin descripción' }}</td>
                            <td class="fw-bold text-danger fs-6">Bs. {{ number_format($servicio->precio, 2) }}</td>
                            @if(strtolower(session('rol')) === 'administrador')
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-1 btn-informacion-servicio" data-id="{{ $servicio->id_servicio }}">
                                        <i class="bi bi-bar-chart-line"></i> Más información
                                    </button>
                                    <a href="{{ route('servicio.editar', $servicio->id_servicio) }}" class="btn btn-outline-warning btn-sm rounded-pill px-3 me-1">
                                        <i class="fa-solid fa-pen"></i> Editar
                                    </a>
                                    <form action="{{ route('servicio.eliminar', $servicio->id_servicio) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar este servicio?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                            <i class="fa-solid fa-trash"></i> Eliminar
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No hay servicios registrados en el sistema.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(strtolower(session('rol')) === 'administrador')
<div class="d-flex justify-content-between align-items-center mt-5 mb-3 flex-wrap gap-2">
    <div>
        <h4 class="m-0 text-danger fw-bold"><i class="bi bi-tag me-2"></i>Promociones</h4>
        <small class="text-muted">Ofertas vigentes y programadas para tus servicios</small>
    </div>
    <a href="{{ route('promocion.crear') }}" class="btn btn-danger rounded-pill shadow-sm"><i class="bi bi-plus-lg me-1"></i>Nueva promoción</a>
</div>
@if(session('success'))<div class="alert alert-success rounded-3">{{ session('success') }}</div>@endif
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-3"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-danger"><tr><th>#</th><th>Imagen</th><th>Promoción / Servicios</th><th>Descripción</th><th>Vigencia</th><th>Precio</th><th>Clientes</th><th class="text-center">Acciones</th></tr></thead>
            <tbody>
                @forelse($promociones as $promocion)
                    <tr>
                        <td class="fw-bold">{{ $promocion->id_promocion }}</td>
                        <td>@if($promocion->imagen && file_exists(public_path('promociones/'.$promocion->imagen)))<img src="{{ asset('promociones/'.$promocion->imagen) }}" alt="{{ $promocion->nombre }}" class="rounded-3 border" style="width:72px;height:56px;object-fit:cover">@else<span class="text-muted small">Sin imagen</span>@endif</td>
                        <td><strong>{{ $promocion->nombre }}</strong><div class="small text-muted">{{ $promocion->servicios->pluck('nombre')->join(', ') ?: 'Sin servicios' }}</div></td>
                        <td>{{ \Illuminate\Support\Str::limit($promocion->descripcion ? \Illuminate\Support\Str::lower($promocion->descripcion) : 'Sin descripción', 70) }}</td>
                        <td class="small">{{ $promocion->fecha_hora_inicio->format('d/m/Y H:i') }}<br><span class="text-muted">hasta {{ $promocion->fecha_hora_fin->format('d/m/Y H:i') }}</span></td>
                        <td class="fw-bold text-danger">Bs. {{ number_format((float)$promocion->precio, 2) }}</td>
                        <td>{{ $promocion->cantidad_clientes }}</td>
                        <td class="text-center text-nowrap">
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill mb-1 btn-informacion-promocion" data-id="{{ $promocion->id_promocion }}"><i class="bi bi-info-circle"></i> Más información</button>
                            <a href="{{ route('promocion.editar', $promocion->id_promocion) }}" class="btn btn-outline-warning btn-sm rounded-pill mb-1"><i class="fa-solid fa-pen"></i> Editar</a>
                            <form action="{{ route('promocion.eliminar', $promocion->id_promocion) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar esta promoción?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm rounded-pill mb-1"><i class="fa-solid fa-trash"></i> Eliminar</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Aún no hay promociones registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div></div>
</div>

<div class="modal fade" id="informacionPromocionModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow rounded-4"><div class="modal-header bg-danger text-white"><h5 class="modal-title" id="tituloInformacionPromocion">Información de promoción</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><div id="cuerpoInformacionPromocion"></div></div></div></div></div>
@endif

@endsection

@if(strtolower(session('rol')) === 'administrador')
    @section('scripts')
    <div class="modal fade" id="informacionServicioModal" tabindex="-1" aria-labelledby="informacionServicioTitulo" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="informacionServicioTitulo"><i class="bi bi-graph-up-arrow me-2"></i>Información del servicio</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="informacionServicioCargando" class="text-center py-5">
                        <div class="spinner-border text-danger" role="status"></div>
                        <p class="text-muted mt-2 mb-0">Cargando información del servicio...</p>
                    </div>
                    <div id="informacionServicioError" class="alert alert-danger d-none mb-0"></div>
                    <div id="informacionServicioContenido" class="d-none">
                        <div class="row g-3 mb-4">
                            <div class="col-6 col-md-3"><div class="border rounded-3 p-3 h-100 text-center"><small class="text-muted d-block">Solicitudes</small><strong class="fs-4 text-danger" id="totalSolicitudes"></strong></div></div>
                            <div class="col-6 col-md-3"><div class="border rounded-3 p-3 h-100 text-center"><small class="text-muted d-block">Clientes</small><strong class="fs-4" id="totalClientes"></strong></div></div>
                            <div class="col-6 col-md-3"><div class="border rounded-3 p-3 h-100 text-center"><small class="text-muted d-block">Finalizadas</small><strong class="fs-4 text-success" id="totalFinalizadas"></strong></div></div>
                            <div class="col-6 col-md-3"><div class="border rounded-3 p-3 h-100 text-center"><small class="text-muted d-block">Tasa de finalización</small><strong class="fs-4 text-primary" id="tasaFinalizacion"></strong></div></div>
                        </div>
                        <div class="alert alert-light border mb-4" id="clienteFrecuente"></div>
                        <div class="d-flex flex-wrap gap-3 text-muted small mb-3">
                            <span><i class="bi bi-calendar-event me-1"></i>Primera solicitud: <strong id="primeraSolicitud"></strong></span>
                            <span><i class="bi bi-calendar-check me-1"></i>Última solicitud: <strong id="ultimaSolicitud"></strong></span>
                            <span><i class="bi bi-clock-history me-1"></i>En curso: <strong id="totalPendientes"></strong></span>
                            <span><i class="bi bi-x-circle me-1"></i>Canceladas: <strong id="totalCanceladas"></strong></span>
                        </div>
                        <h6 class="fw-bold text-danger mb-3"><i class="bi bi-people me-2"></i>Clientes que solicitaron este servicio</h6>
                        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-danger"><tr><th>Cliente</th><th>Teléfono</th><th class="text-center">Solicitudes</th><th class="text-center">Finalizadas</th><th>Última solicitud</th></tr></thead><tbody id="listaClientesServicio"></tbody></table></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const modalPromocion = document.getElementById('informacionPromocionModal');
        document.querySelectorAll('.btn-informacion-promocion').forEach(boton => boton.addEventListener('click', async () => {
            const cuerpo = document.getElementById('cuerpoInformacionPromocion');
            cuerpo.textContent = 'Cargando información...';
            bootstrap.Modal.getOrCreateInstance(modalPromocion).show();
            try {
                const respuesta = await fetch(`{{ url('/promociones') }}/${boton.dataset.id}/informacion`, { headers: { Accept: 'application/json' } });
                if (!respuesta.ok) throw new Error('No se pudo cargar la promoción.');
                const p = await respuesta.json();
                document.getElementById('tituloInformacionPromocion').textContent = p.nombre;
                cuerpo.innerHTML = `<p>${escaparHtmlServicio(p.descripcion || 'Sin descripción')}</p><p><strong>Inicio:</strong> ${escaparHtmlServicio(p.inicio)}<br><strong>Fin:</strong> ${escaparHtmlServicio(p.fin)}</p><p><strong>Precio:</strong> Bs. ${escaparHtmlServicio(p.precio)}<br><strong>Cantidad de clientes:</strong> ${p.cantidad_clientes}</p><strong>Servicios incluidos</strong><ul>${p.servicios.map(servicio => `<li>${escaparHtmlServicio(servicio)}</li>`).join('')}</ul>`;
            } catch (error) { cuerpo.textContent = error.message; }
        }));

        const modalInformacionServicio = document.getElementById('informacionServicioModal');
        const baseInformacionServicio = @json(url('/servicio'));
        document.body.appendChild(modalInformacionServicio);
        const escaparHtmlServicio = valor => String(valor ?? '').replace(/[&<>"']/g, caracter => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[caracter]));

        document.querySelectorAll('.btn-informacion-servicio').forEach(boton => boton.addEventListener('click', async () => {
            const cargando = document.getElementById('informacionServicioCargando');
            const contenido = document.getElementById('informacionServicioContenido');
            const error = document.getElementById('informacionServicioError');
            cargando.classList.remove('d-none'); contenido.classList.add('d-none'); error.classList.add('d-none');
            bootstrap.Modal.getOrCreateInstance(modalInformacionServicio).show();

            try {
                const respuesta = await fetch(`${baseInformacionServicio}/${boton.dataset.id}/informacion`, { headers: { Accept: 'application/json' } });
                if (!respuesta.ok) throw new Error('No se pudo obtener la información del servicio.');
                const datos = await respuesta.json();
                document.getElementById('informacionServicioTitulo').innerHTML = `<i class="bi bi-graph-up-arrow me-2"></i>${escaparHtmlServicio(datos.servicio)}`;
                document.getElementById('totalSolicitudes').textContent = datos.resumen.solicitudes;
                document.getElementById('totalClientes').textContent = datos.resumen.clientes_unicos;
                document.getElementById('totalFinalizadas').textContent = datos.resumen.finalizadas;
                document.getElementById('tasaFinalizacion').textContent = `${datos.resumen.tasa_finalizacion}%`;
                document.getElementById('primeraSolicitud').textContent = datos.resumen.primera_solicitud || 'Sin registros';
                document.getElementById('ultimaSolicitud').textContent = datos.resumen.ultima_solicitud || 'Sin registros';
                document.getElementById('totalPendientes').textContent = datos.resumen.pendientes;
                document.getElementById('totalCanceladas').textContent = datos.resumen.canceladas;
                document.getElementById('clienteFrecuente').innerHTML = datos.cliente_frecuente
                    ? `<i class="bi bi-trophy-fill text-warning me-2"></i><strong>Cliente frecuente:</strong> ${escaparHtmlServicio(datos.cliente_frecuente.nombre)} <span class="text-muted">(${datos.cliente_frecuente.solicitudes} solicitud${datos.cliente_frecuente.solicitudes === 1 ? '' : 'es'})</span>`
                    : '<i class="bi bi-info-circle me-2"></i>Aún no existen solicitudes para este servicio.';
                document.getElementById('listaClientesServicio').innerHTML = datos.clientes.length
                    ? datos.clientes.map(cliente => `<tr><td class="fw-semibold">${escaparHtmlServicio(cliente.nombre)}</td><td>${escaparHtmlServicio(cliente.telefono || 'No registrado')}</td><td class="text-center">${cliente.solicitudes}</td><td class="text-center">${cliente.finalizadas}</td><td>${escaparHtmlServicio(cliente.ultima_solicitud || '—')}</td></tr>`).join('')
                    : '<tr><td colspan="5" class="text-center text-muted py-4">No hay clientes que hayan solicitado este servicio.</td></tr>';
                contenido.classList.remove('d-none');
            } catch (excepcion) {
                error.textContent = excepcion.message; error.classList.remove('d-none');
            } finally { cargando.classList.add('d-none'); }
        }));
    </script>
    @endsection
@endif
