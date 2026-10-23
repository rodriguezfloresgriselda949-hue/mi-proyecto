@extends('adm_plantilla')

@section('content')

@if(session('success'))<div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}@if(session('whatsapp_url')) <a href="{{ session('whatsapp_url') }}" target="_blank" rel="noopener" class="alert-link">Abrir WhatsApp</a>@endif<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if(session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<!-- Encabezado del Dashboard -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div class="d-flex align-items-center">
        <div class="rounded-4 bg-danger text-white d-flex justify-content-center align-items-center shadow-sm" style="width:55px;height:55px;">
            <i class="bi bi-person-workspace fs-3"></i>
        </div>
        <div class="ms-3">
            <h4 class="m-0 text-danger fw-bold">Panel Administrador</h4>
            <small class="text-muted">Vista general y métricas en vivo del salón</small>
        </div>
    </div>
    <span class="badge rounded-pill bg-danger fs-6 px-3 py-2 shadow-sm">
        <i class="bi bi-shield-check me-1"></i> Administrador
    </span>
</div>

<!-- Calendario de reservas -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
        <div><h5 class="m-0 text-danger fw-bold"><i class="bi bi-calendar3 me-2"></i>Calendario de actividad</h5><small class="text-muted">Selecciona un día para consultar reservas y ventas.</small></div>
        <div class="btn-group"><button type="button" id="calendarioAnterior" class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-left"></i></button><button type="button" id="calendarioSiguiente" class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-right"></i></button></div>
    </div>
    <div class="card-body"><div class="row g-4"><div class="col-lg-7"><h6 id="tituloCalendario" class="text-center fw-bold mb-3"></h6><div class="calendario-semana text-muted small fw-bold text-center"><span>Dom</span><span>Lun</span><span>Mar</span><span>Mié</span><span>Jue</span><span>Vie</span><span>Sáb</span></div><div id="calendarioDias" class="calendario-dias"></div><div class="d-flex gap-3 small mt-3"><span><i class="bi bi-square-fill text-success"></i> Día pasado</span><span><i class="bi bi-square-fill text-warning"></i> Sin actividad</span><span><i class="bi bi-square-fill text-primary"></i> Actividad futura</span></div></div><div class="col-lg-5"><div class="d-flex justify-content-between align-items-start gap-2 mb-3 flex-wrap"><h6 class="fw-bold text-danger mb-0"><i class="bi bi-clock-history me-1"></i>Agenda <span id="fechaAgendaTitulo">de hoy</span></h6><button type="button" class="btn btn-outline-danger btn-sm rounded-pill" id="abrirDisponibilidadAgenda"><i class="bi bi-calendar2-check me-1"></i>Ver disponibilidad</button></div><div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Hora</th><th>Reserva / disponibilidad</th></tr></thead><tbody id="agendaHorasBody">@for($hora = 9; $hora <= 19; $hora++) @php($franja = sprintf('%02d:00', $hora)) @php($reservasHora = $agendaHoy->get($franja, collect()))<tr><td class="fw-bold">{{ $franja }}</td><td>@forelse($reservasHora as $reservaHora)<span class="text-danger fw-semibold d-block">{{ $reservaHora->cliente->nombre ?? 'Cliente' }} {{ $reservaHora->cliente->apellido ?? '' }} <small class="text-muted">({{ $reservaHora->usuario->username ?? 'Sin asignar' }})</small></span>@empty<span class="text-success"><i class="bi bi-check-circle me-1"></i>Disponible</span>@endforelse</td></tr>@endfor</tbody></table></div><small class="text-muted">Horario referencial: 09:00 a 19:00.</small></div></div></div>
</div>

<!-- Tarjetas de Métricas -->
<div class="row g-3 mb-4">
    <!-- Ingresos del mes -->
    <div class="col-md-6 col-xl">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-2">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted fw-bold">Ingresos del Mes</small>
                    <span class="badge bg-success-subtle text-success rounded-circle p-2"><i class="bi bi-cash-stack fs-5"></i></span>
                </div>
                <h3 class="text-danger fw-bold m-0">Bs. {{ number_format($totalIngresosMes, 2) }}</h3>
                <small class="text-muted d-block mt-1">Ventas: Bs. {{ number_format($ingresosVentas, 2) }} &bull; Citas: Bs. {{ number_format($ingresosReservas, 2) }}</small>
            </div>
        </div>
    </div>

    <!-- Reservas por Día -->
    <div class="col-md-6 col-xl">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-2">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted fw-bold">Reservas del Día (Hoy)</small>
                    <span class="badge bg-warning-subtle text-warning rounded-circle p-2"><i class="bi bi-calendar2-day fs-5"></i></span>
                </div>
                <h3 class="text-danger fw-bold m-0">{{ $reservasHoyTotal }}</h3>
                <small class="text-muted d-block mt-1">
                    <span class="text-warning fw-bold">{{ $reservasHoyPendientes }} pendientes</span> &bull; 
                    <span class="text-success fw-bold">{{ $reservasHoyCompletadas }} finalizadas</span>
                </small>
            </div>
        </div>
    </div>

    <!-- Clientes Registrados -->
    <div class="col-md-6 col-xl">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-2">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted fw-bold">Clientes Registrados</small>
                    <span class="badge bg-info-subtle text-info rounded-circle p-2"><i class="bi bi-heart-fill fs-5"></i></span>
                </div>
                <h3 class="text-danger fw-bold m-0">{{ $totalClientes }}</h3>
                <small class="text-muted d-block mt-1">Clientes en base de datos</small>
            </div>
        </div>
    </div>

    <!-- Alertas de Stock -->
    <div class="col-md-6 col-xl">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-2">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted fw-bold">Inventario / Stock</small>
                    <span class="badge bg-danger-subtle text-danger rounded-circle p-2"><i class="bi bi-exclamation-triangle-fill fs-5"></i></span>
                </div>
                <h3 class="{{ $alertasStock > 0 ? 'text-danger' : 'text-success' }} fw-bold m-0">{{ $alertasStock }}</h3>
                <small class="text-danger fw-bold d-block mt-1">Productos con stock bajo (&le; 5)</small>
            </div>
        </div>
    </div>
    <!-- Reservas de productos pendientes -->
    <div class="col-md-6 col-xl">
        <button type="button" class="card border-0 shadow-sm rounded-4 h-100 p-2 w-100 text-start" data-bs-toggle="modal" data-bs-target="#modalReservasProductosPendientes">
            <span class="card-body">
                <span class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted fw-bold">Productos reservados pendientes</small>
                    <span class="badge bg-warning-subtle text-warning rounded-circle p-2"><i class="bi bi-box2-heart-fill fs-5"></i></span>
                </span>
                <span class="h3 text-danger fw-bold m-0 d-block">{{ $reservasProductosPendientes->count() }}</span>
                <small class="text-muted d-block mt-1">Grupos por cliente y fecha · Ver detalle</small>
            </span>
        </button>
    </div>
</div>

<div class="modal fade" id="modalReservasProductosPendientes" tabindex="-1" aria-labelledby="modalReservasProductosPendientesTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalReservasProductosPendientesTitulo"><i class="bi bi-box2-heart-fill me-2"></i>Reservas de productos pendientes</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                @forelse($reservasProductosPendientes as $grupo)
                    <section class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-light d-flex justify-content-between flex-wrap gap-2">
                            <div><strong>{{ $grupo['cliente'] }}</strong><small class="d-block text-muted">{{ $grupo['telefono'] ?: 'Sin teléfono registrado' }}</small></div>
                            <div class="text-md-end"><span class="fw-semibold">Recojo: {{ $grupo['fecha_recojo'] ? \Carbon\Carbon::parse($grupo['fecha_recojo'])->format('d/m/Y') : 'Sin fecha' }}</span><small class="d-block text-muted">Hora: {{ $grupo['horarios']->isNotEmpty() ? $grupo['horarios']->implode(', ') : 'Sin hora' }}</small></div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead><tr><th>Producto</th><th class="text-center">Cantidad</th><th class="text-end">Precio unitario</th><th class="text-end">Subtotal</th></tr></thead>
                                    <tbody>
                                        @foreach($grupo['items'] as $item)
                                            <tr><td>{{ $item['producto'] }}</td><td class="text-center">{{ $item['cantidad'] }}</td><td class="text-end">Bs. {{ number_format($item['precio'], 2) }}</td><td class="text-end">Bs. {{ number_format($item['subtotal'], 2) }}</td></tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot><tr><th colspan="3" class="text-end">Total</th><th class="text-end text-danger">Bs. {{ number_format($grupo['total'], 2) }}</th></tr></tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white d-flex justify-content-end gap-2 flex-wrap">
                            @if($grupo['whatsapp_url'])
                                <a href="{{ $grupo['whatsapp_url'] }}" target="_blank" rel="noopener" class="btn btn-outline-success"><i class="bi bi-whatsapp me-1"></i>Enviar detalle por WhatsApp</a>
                            @else
                                <button type="button" class="btn btn-outline-secondary" disabled title="El cliente no tiene teléfono registrado">Sin WhatsApp</button>
                            @endif
                            <form method="POST" action="{{ route('reservas.productos.confirmarGrupo') }}" onsubmit="return confirm('¿Confirmar todas las reservas de este cliente para esta fecha?')">
                                @csrf
                                @foreach($grupo['ids'] as $idReservaProducto)<input type="hidden" name="ids[]" value="{{ $idReservaProducto }}">@endforeach
                                <button type="submit" class="btn btn-danger"><i class="bi bi-check-circle me-1"></i>Confirmar grupo</button>
                            </form>
                        </div>
                    </section>
                @empty
                    <div class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No hay reservas de productos pendientes.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Accesos Rápidos de Gestión -->
<h5 class="mb-3 text-danger fw-bold"><i class="bi bi-grid-3x3-gap-fill me-2"></i> Módulos Principales</h5>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <a href="{{ route('ventas.create') }}" class="card border-0 shadow-sm rounded-4 text-decoration-none text-dark h-100 text-center p-3 hover-scale">
            <i class="bi bi-cart-plus-fill fs-1 text-danger"></i>
            <h6 class="mt-2 mb-0 fw-bold">Nueva Venta</h6>
        </a>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <a href="{{ route('reservas.create') }}" class="card border-0 shadow-sm rounded-4 text-decoration-none text-dark h-100 text-center p-3 hover-scale">
            <i class="bi bi-calendar-plus-fill fs-1 text-danger"></i>
            <h6 class="mt-2 mb-0 fw-bold">Nueva Reserva</h6>
        </a>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <a href="{{ route('cliente.crear') }}" class="card border-0 shadow-sm rounded-4 text-decoration-none text-dark h-100 text-center p-3 hover-scale">
            <i class="bi bi-person-plus-fill fs-1 text-danger"></i>
            <h6 class="mt-2 mb-0 fw-bold">Nuevo Cliente</h6>
        </a>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <a href="{{ route('productos.index') }}" class="card border-0 shadow-sm rounded-4 text-decoration-none text-dark h-100 text-center p-3 hover-scale">
            <i class="bi bi-box-seam-fill fs-1 text-danger"></i>
            <h6 class="mt-2 mb-0 fw-bold">Inventario</h6>
        </a>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <a href="{{ route('proveedores.index') }}" class="card border-0 shadow-sm rounded-4 text-decoration-none text-dark h-100 text-center p-3 hover-scale">
            <i class="bi bi-truck fs-1 text-danger"></i>
            <h6 class="mt-2 mb-0 fw-bold">Proveedores</h6>
        </a>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <a href="{{ route('reportes.index') }}" class="card border-0 shadow-sm rounded-4 text-decoration-none text-dark h-100 text-center p-3 hover-scale">
            <i class="bi bi-bar-chart-fill fs-1 text-danger"></i>
            <h6 class="mt-2 mb-0 fw-bold">Reportes</h6>
        </a>
    </div>
</div>

<!-- Tablas Recientes -->
<div class="row g-4">
    <!-- Últimas Reservas -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                <h6 class="m-0 text-danger fw-bold"><i class="bi bi-calendar-event me-2"></i> Últimas Reservas</h6>
                <a href="{{ route('reservas.index') }}" class="btn btn-sm btn-outline-danger rounded-pill">Ver todas</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Cliente</th>
                                <th>Servicio</th>
                                <th>Fecha/Hora</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimasReservas as $res)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ $res->cliente->nombre ?? 'N/A' }} {{ $res->cliente->apellido ?? '' }}</span>
                                    </td>
                                    <td>{{ $res->servicio->nombre ?? 'N/A' }}</td>
                                    <td>
                                        <small>{{ is_object($res->fecha) ? $res->fecha->format('d/m/Y') : $res->fecha }} {{ $res->hora }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $res->estado == 'Completada' || $res->estado == 'Finalizado' ? 'bg-success' : ($res->estado == 'Cancelada' ? 'bg-secondary' : 'bg-warning text-dark') }} rounded-pill">
                                            {{ $res->estado }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No hay reservas recientes.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimas Ventas -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                <h6 class="m-0 text-danger fw-bold"><i class="bi bi-cart-check me-2"></i> Últimas Ventas</h6>
                <a href="{{ route('ventas.index') }}" class="btn btn-sm btn-outline-danger rounded-pill">Ver todas</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th># Venta</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimasVentas as $v)
                                <tr>
                                    <td class="fw-bold">#{{ $v->id_venta }}</td>
                                    <td>{{ $v->cliente->nombre ?? 'Cliente Ocasional' }} {{ $v->cliente->apellido ?? '' }}</td>
                                    <td><small>{{ is_object($v->fecha) ? $v->fecha->format('d/m/Y') : $v->fecha }}</small></td>
                                    <td class="fw-bold text-success">Bs. {{ number_format($v->total, 2) }}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger py-1 px-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalVenta{{ $v->id_venta }}">
                                            <i class="bi bi-receipt me-1"></i> Ver Detalle
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No hay ventas registradas recientemente.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modales de Detalle de Venta Fuera de la Tabla -->
@foreach($ultimasVentas as $v)
<div class="modal fade" id="modalVenta{{ $v->id_venta }}" tabindex="-1" aria-labelledby="modalVentaLabel{{ $v->id_venta }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-danger text-white rounded-top-4">
                <h5 class="modal-title fw-bold" id="modalVentaLabel{{ $v->id_venta }}">
                    <i class="bi bi-receipt me-2"></i> Comprobante de Venta #{{ $v->id_venta }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-2 mb-3 pb-3 border-bottom">
                    <div class="col-6">
                        <small class="text-muted d-block">Cliente:</small>
                        <strong class="text-dark">{{ $v->cliente->nombre ?? 'Cliente Ocasional' }} {{ $v->cliente->apellido ?? '' }}</strong>
                        @if($v->cliente && $v->cliente->telefono)
                            <small class="d-block text-muted"><i class="bi bi-telephone me-1"></i>{{ $v->cliente->telefono }}</small>
                        @endif
                    </div>
                    <div class="col-6 text-end">
                        <small class="text-muted d-block">Atendido por:</small>
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill">{{ $v->usuario->username ?? 'Sistema' }}</span>
                        <small class="d-block text-muted mt-1"><i class="bi bi-calendar-event me-1"></i>{{ is_object($v->fecha) ? $v->fecha->format('d/m/Y') : $v->fecha }}</small>
                    </div>
                </div>

                <h6 class="fw-bold text-secondary mb-2">Productos Comprados:</h6>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-end">P. Unit</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($v->detalles as $detalle)
                                <tr>
                                    <td>
                                        <strong>{{ $detalle->producto->descripcion ?? 'Producto' }}</strong>
                                        @if($detalle->producto && $detalle->producto->destacado)
                                            <span class="badge text-bg-warning ms-1"><i class="bi bi-star-fill me-1"></i>{{ $detalle->producto->destacado }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $detalle->cantidad }}</td>
                                    <td class="text-end">Bs. {{ number_format($detalle->precioventa, 2) }}</td>
                                    <td class="text-end fw-bold">Bs. {{ number_format($detalle->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3 mt-3">
                    <span class="fw-bold text-secondary fs-5">Monto Total:</span>
                    <span class="fw-bold text-danger fs-4">Bs. {{ number_format($v->total, 2) }}</span>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <a href="{{ route('ventas.show', $v->id_venta) }}" class="btn btn-outline-danger rounded-pill px-4">
                    <i class="bi bi-printer me-1"></i> Ver Recibo Imprimible
                </a>
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
@endforeach

<div class="modal fade" id="modalCalendarioReservas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><div class="modal-content rounded-4 border-0 shadow">
        <div class="modal-header bg-danger text-white"><h5 class="modal-title"><i class="bi bi-calendar-check me-2"></i>Actividad del <span id="fechaCalendarioModal"></span></h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div id="filtrosActividadCalendario" class="row g-2 mb-3 d-none"><div class="col-md-4"><label for="filtroActividadCalendario" class="form-label fw-semibold">Mostrar</label><select class="form-select" id="filtroActividadCalendario"><option value="todo">Reservas y ventas</option><option value="reservas">Solo reservas</option><option value="ventas">Solo ventas</option></select></div><div class="col-md-4"><label for="filtroServicioCalendario" class="form-label fw-semibold">Servicio</label><select class="form-select" id="filtroServicioCalendario"><option value="">Todos los servicios</option></select></div><div class="col-md-4"><label for="filtroProductoCalendario" class="form-label fw-semibold">Producto</label><select class="form-select" id="filtroProductoCalendario"><option value="">Todos los productos</option></select></div></div><div id="contenidoCalendarioModal"></div></div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cerrar</button></div>
    </div></div>
</div>

<div class="modal fade" id="modalDisponibilidadAgenda" tabindex="-1" aria-labelledby="tituloModalDisponibilidad" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><div class="modal-content rounded-4 border-0 shadow">
        <div class="modal-header bg-danger text-white"><h5 class="modal-title" id="tituloModalDisponibilidad"><i class="bi bi-calendar2-check me-2"></i>Disponibilidad por hora · <span id="fechaModalDisponibilidad"></span></h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
        <div class="modal-body"><div class="table-responsive"><table class="table table-hover table-bordered align-middle"><thead class="table-light"><tr id="encabezadosDisponibilidadAgenda"><th>Hora</th>@forelse($empleadosActivos as $empleadoActivo)<th data-empleado-id="{{ $empleadoActivo->id_usuario }}">{{ $empleadoActivo->username }}</th>@empty<th>Empleados activos</th>@endforelse</tr></thead><tbody id="filasDisponibilidadAgenda"><tr><td colspan="{{ $empleadosActivos->count() + 1 }}" class="text-center text-muted">Selecciona una fecha para consultar.</td></tr></tbody></table></div><small class="text-muted">Horario de atención: 09:00 a 19:00.</small></div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cerrar</button></div>
    </div></div>
</div>

@endsection

@section('scripts')
<style>
    .calendario-semana,.calendario-dias{display:grid;grid-template-columns:repeat(7,1fr);gap:.35rem}.calendario-dia{min-height:72px;border:1px solid #eee;border-radius:.7rem;background:#fff;text-align:left;padding:.45rem;cursor:pointer;transition:.15s}.calendario-dia:hover{border-color:#dc3545}.calendario-dia.vacio{border:0;background:transparent;cursor:default}.calendario-dia.hoy{border:2px solid #dc3545}.calendario-numero{font-weight:bold}.calendario-punto{display:block;font-size:.65rem;margin-top:.25rem}.calendario-dia.disponible{background:#e7f1ff;border-color:#9ec5fe}.calendario-dia.lleno{background:#f8d7da;border-color:#f1aeb5}.calendario-dia.vacio-dia{background:#fff3cd;border-color:#ffda6a}.calendario-dia.pasado{background:#d1e7dd;border-color:#a3cfbb;color:#0f5132}@media(max-width:576px){.calendario-dia{min-height:52px;padding:.25rem}.calendario-punto{display:none}}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Los modales de ventas deben estar directamente en body para quedar
    // por encima del backdrop de Bootstrap y evitar que se oscurezca toda la vista.
    document.querySelectorAll('[id^="modalVenta"]').forEach(modalVenta => {
        document.body.appendChild(modalVenta);
    });
    const modalReservasProductosEl = document.getElementById('modalReservasProductosPendientes');
    if (modalReservasProductosEl) document.body.appendChild(modalReservasProductosEl);
    const dias = document.getElementById('calendarioDias'), titulo = document.getElementById('tituloCalendario');
    const modalEl = document.getElementById('modalCalendarioReservas');
    document.body.appendChild(modalEl);
    const modal = new bootstrap.Modal(modalEl), nombres = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    const disponibilidadModalEl = document.getElementById('modalDisponibilidadAgenda');
    document.body.appendChild(disponibilidadModalEl);
    const disponibilidadModal = new bootstrap.Modal(disponibilidadModalEl);
    let fechaAgendaSeleccionada = new Date();
    const filtroActividad = document.getElementById('filtroActividadCalendario');
    const filtroServicio = document.getElementById('filtroServicioCalendario');
    const filtroProducto = document.getElementById('filtroProductoCalendario');
    const filtrosActividad = document.getElementById('filtrosActividadCalendario');
    const agendaHorasBody = document.getElementById('agendaHorasBody');
    const fechaAgendaTitulo = document.getElementById('fechaAgendaTitulo');
    let actividadDia = {reservas: [], ventas: []};
    let fechaActividadPasada = false;
    let fechaVista = new Date();
    const hoy = new Date();
    const formatoApi = d => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    const escapar = texto => String(texto ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
    function renderizarAgenda(fecha, reservas) {
        fechaAgendaTitulo.textContent = fecha.toLocaleDateString('es-BO', {weekday:'long', day:'numeric', month:'long'});
        const porHora = new Map();
        reservas.forEach(reserva => {
            const hora = String(reserva.hora || '').slice(0, 5);
            const franja = `${hora.slice(0, 2)}:00`;
            if (!porHora.has(franja)) porHora.set(franja, []);
            porHora.get(franja).push(reserva);
        });
        agendaHorasBody.innerHTML = Array.from({length: 11}, (_, indice) => {
            const franja = `${String(indice + 9).padStart(2, '0')}:00`;
            const items = porHora.get(franja) || [];
            const contenido = items.length
                ? items.map(r => `<span class="text-danger fw-semibold d-block">${escapar(r.cliente)} <small class="text-muted">(${escapar(r.empleado || 'Sin asignar')})</small></span>`).join('')
                : '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Disponible</span>';
            return `<tr><td class="fw-bold">${franja}</td><td>${contenido}</td></tr>`;
        }).join('');
    }
    function renderizarDisponibilidad(reservas, fechaApi) {
        const empleados = [...document.querySelectorAll('#encabezadosDisponibilidadAgenda [data-empleado-id]')]
            .map(celda => ({id: celda.dataset.empleadoId, nombre: celda.textContent.trim()}));
        const reservasActivas = reservas.filter(reserva => !String(reserva.estado || '').toLowerCase().startsWith('cancel'));
        const puedeAgendar = fechaApi >= formatoApi(new Date());
        document.getElementById('filasDisponibilidadAgenda').innerHTML = Array.from({length: 11}, (_, indice) => {
            const franja = `${String(indice + 9).padStart(2, '0')}:00`;
            if (!empleados.length) return `<tr><td class="fw-semibold text-nowrap">${franja}</td><td class="text-muted">No hay empleados activos</td></tr>`;
            const celdasEmpleados = empleados.map(empleado => {
                const reservasEmpleado = reservasActivas.filter(reserva => String(reserva.id_empleado ?? '') === empleado.id && String(reserva.hora || '').slice(0, 2) === franja.slice(0, 2));
                return reservasEmpleado.length
                    ? `<td class="table-danger"><span class="badge text-bg-danger mb-1">Ocupado</span>${reservasEmpleado.map(reserva => `<div class="small fw-semibold">${escapar(reserva.cliente)}</div>`).join('')}</td>`
                    : `<td class="table-success-subtle">${puedeAgendar
                        ? `<a class="btn btn-success btn-sm rounded-pill" href="{{ route('reservas.create') }}?${new URLSearchParams({fecha: fechaApi, hora: franja, id_usuario: empleado.id, fijar_personal: 1})}"><i class="bi bi-plus-circle me-1"></i>Disponible</a>`
                        : '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Disponible</span>'}</td>`;
            }).join('');
            return `<tr><td class="fw-semibold text-nowrap">${franja}</td>${celdasEmpleados}</tr>`;
        }).join('');
    }
    async function abrirDisponibilidad(fecha) {
        fechaAgendaSeleccionada = fecha;
        const fechaApi = formatoApi(fecha);
        document.getElementById('fechaModalDisponibilidad').textContent = fecha.toLocaleDateString('es-BO', {weekday:'long', day:'numeric', month:'long', year:'numeric'});
        const columnasAgenda = document.querySelectorAll('#encabezadosDisponibilidadAgenda th').length;
        document.getElementById('filasDisponibilidadAgenda').innerHTML = `<tr><td colspan="${columnasAgenda}" class="text-center py-4"><span class="spinner-border spinner-border-sm text-danger me-2"></span>Cargando disponibilidad…</td></tr>`;
        disponibilidadModal.show();
        try {
            const respuesta = await fetch(`{{ route('admin.calendario.reservas') }}?fecha=${fechaApi}`, {headers:{Accept:'application/json'}});
            if (!respuesta.ok) throw new Error();
            const datos = await respuesta.json();
            renderizarDisponibilidad(datos.reservas || [], fechaApi);
        } catch {
            document.getElementById('filasDisponibilidadAgenda').innerHTML = `<tr><td colspan="${columnasAgenda}" class="text-center text-danger py-3">No se pudo cargar la disponibilidad.</td></tr>`;
        }
    }
    document.getElementById('abrirDisponibilidadAgenda').addEventListener('click', () => abrirDisponibilidad(fechaAgendaSeleccionada));
    const fechaDisponibilidadSolicitada = new URLSearchParams(window.location.search).get('fecha_disponibilidad');
    if (fechaDisponibilidadSolicitada && /^\d{4}-\d{2}-\d{2}$/.test(fechaDisponibilidadSolicitada)) {
        const [anio, mes, dia] = fechaDisponibilidadSolicitada.split('-').map(Number);
        const fechaSolicitada = new Date(anio, mes - 1, dia);
        if (formatoApi(fechaSolicitada) === fechaDisponibilidadSolicitada) abrirDisponibilidad(fechaSolicitada);
    }
    let totalesMes = {};
    async function cargarTotales() { const mes = `${fechaVista.getFullYear()}-${String(fechaVista.getMonth()+1).padStart(2,'0')}`; const respuesta = await fetch(`{{ route('admin.calendario.reservas') }}?mes=${mes}`, {headers:{Accept:'application/json'}}); totalesMes = respuesta.ok ? await respuesta.json() : {}; }
    function dibujar() {
        const anio = fechaVista.getFullYear(), mes = fechaVista.getMonth(), inicio = new Date(anio, mes, 1).getDay(), total = new Date(anio, mes + 1, 0).getDate();
        titulo.textContent = `${nombres[mes]} ${anio}`; dias.innerHTML = '';
        for(let i=0;i<inicio;i++) dias.insertAdjacentHTML('beforeend','<div class="calendario-dia vacio"></div>');
        for(let n=1;n<=total;n++) { const fecha = new Date(anio, mes, n), fechaApi = formatoApi(fecha), esHoy = fechaApi === formatoApi(hoy), fechaPasada = fecha < new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate()), datos = totalesMes[fechaApi] || {}, reservas = Number(datos.reservas || 0), ventas = Number(datos.ventas || 0), color = fechaPasada ? 'pasado' : ((reservas + ventas) === 0 ? 'vacio-dia' : ((reservas + ventas) >= 11 ? 'lleno' : 'disponible')); const boton = document.createElement('button'); boton.type='button'; boton.className=`calendario-dia ${color} ${esHoy ? 'hoy' : ''}`; boton.innerHTML=`<span class="calendario-numero">${n}</span><span class="calendario-punto">${reservas} res. · ${ventas} vent.</span>`; boton.addEventListener('click', () => abrirDia(fecha)); dias.appendChild(boton); }
    }
    function renderizarActividad() {
        const contenido = document.getElementById('contenidoCalendarioModal'), filtro = filtroActividad.value;
        const reservas = filtro === 'ventas' ? [] : actividadDia.reservas.filter(r => !filtroServicio.value || r.servicios.includes(filtroServicio.value));
        const ventas = filtro === 'reservas' ? [] : actividadDia.ventas.filter(v => !filtroProducto.value || v.productos.some(p => p.nombre === filtroProducto.value));
        const bloquesReservas = reservas.length ? `<h6 class="text-danger fw-bold mt-2">Reservas (${reservas.length})</h6><div class="list-group mb-3">${reservas.map(r => `<div class="list-group-item"><div class="d-flex justify-content-between gap-2"><strong>${escapar(r.cliente)}</strong><span class="badge text-bg-secondary">${escapar(r.hora)} · ${escapar(r.estado)}</span></div><div class="small text-muted">${r.servicios.map(escapar).join(', ')} · ${escapar(r.empleado)}</div><div class="small fw-semibold mt-1">Total: Bs. ${Number(r.total || 0).toFixed(2)}</div></div>`).join('')}</div>` : '';
        const bloquesVentas = ventas.length ? `<h6 class="text-success fw-bold mt-2">Ventas (${ventas.length})</h6><div class="list-group">${ventas.map(v => { const productos = v.productos.filter(p => !filtroProducto.value || p.nombre === filtroProducto.value); const detalle = productos.map(p => `${escapar(p.nombre)} × ${Number(p.cantidad)} · Bs. ${Number(p.precio || 0).toFixed(2)} = Bs. ${Number(p.subtotal || 0).toFixed(2)}`).join('<br>'); const total = filtroProducto.value ? productos.reduce((suma, p) => suma + Number(p.subtotal || 0), 0) : Number(v.total || 0); return `<div class="list-group-item"><div class="d-flex justify-content-between gap-2"><strong>${escapar(v.cliente)}</strong><span class="badge text-bg-success">Venta #${escapar(v.id)}</span></div><div class="small text-muted">${detalle || 'Sin detalle de productos'}</div><div class="small fw-semibold mt-1">${filtroProducto.value ? 'Subtotal filtrado' : 'Total'}: Bs. ${total.toFixed(2)}</div></div>`; }).join('')}</div>` : '';
        const hayItems = reservas.length || ventas.length;
        contenido.innerHTML = hayItems ? `${bloquesReservas}${bloquesVentas}` : '<p class="text-center text-muted py-3 mb-0">No hay actividad de este tipo para este día.</p>';
    }
    function renderizarReservasComoAntes(reservas) {
        const contenido = document.getElementById('contenidoCalendarioModal');
        if(!reservas.length) { contenido.innerHTML='<p class="text-center text-muted py-3 mb-0">No hay reservas para este día.</p>'; return; }
        const grupos = reservas.reduce((acum, reserva) => { (acum[reserva.empleado] ||= []).push(reserva); return acum; }, {});
        contenido.innerHTML = Object.entries(grupos).map(([empleado, items]) => `<div class="mb-3"><h6 class="text-danger border-bottom pb-2"><i class="bi bi-person-workspace me-1"></i>${escapar(empleado)}</h6>${items.map(r => `<div class="d-flex gap-3 mb-2"><span class="badge bg-dark align-self-start">${escapar(r.hora)}</span><div><strong>${escapar(r.cliente)}</strong><small class="d-block text-muted">${r.servicios.map(escapar).join(', ')} · ${escapar(r.estado)}</small></div></div>`).join('')}</div>`).join('');
    }
    async function abrirDia(fecha) {
        fechaAgendaSeleccionada = new Date(fecha.getFullYear(), fecha.getMonth(), fecha.getDate());
        const fechaApi = formatoApi(fecha); document.getElementById('fechaCalendarioModal').textContent = fecha.toLocaleDateString('es-BO');
        fechaActividadPasada = fecha < new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());
        const contenido = document.getElementById('contenidoCalendarioModal'); contenido.innerHTML='<div class="text-center py-4"><span class="spinner-border text-danger"></span></div>'; modal.show();
        filtrosActividad.classList.toggle('d-none', !fechaActividadPasada);
        fechaAgendaTitulo.textContent = fecha.toLocaleDateString('es-BO', {weekday:'long', day:'numeric', month:'long'});
        agendaHorasBody.innerHTML = '<tr><td colspan="2" class="text-center text-muted py-3"><span class="spinner-border spinner-border-sm text-danger me-2"></span>Cargando agenda…</td></tr>';
        try { const respuesta = await fetch(`{{ route('admin.calendario.reservas') }}?fecha=${fechaApi}`, {headers:{Accept:'application/json'}}); if(!respuesta.ok) throw new Error(); actividadDia = await respuesta.json();
            renderizarAgenda(fecha, actividadDia.reservas || []);
            if (!fechaActividadPasada) { renderizarReservasComoAntes(actividadDia.reservas); return; }
            filtroActividad.value = 'todo'; filtroServicio.value = ''; filtroProducto.value = '';
            const servicios = [...new Set(actividadDia.reservas.flatMap(r => r.servicios))].sort((a,b) => a.localeCompare(b, 'es'));
            const productos = [...new Set(actividadDia.ventas.flatMap(v => v.productos.map(p => p.nombre)))].sort((a,b) => a.localeCompare(b, 'es'));
            filtroServicio.innerHTML = '<option value="">Todos los servicios</option>' + servicios.map(s => `<option value="${escapar(s)}">${escapar(s)}</option>`).join('');
            filtroProducto.innerHTML = '<option value="">Todos los productos</option>' + productos.map(p => `<option value="${escapar(p)}">${escapar(p)}</option>`).join('');
            renderizarActividad();
        } catch { contenido.innerHTML='<p class="text-center text-danger py-3 mb-0">No se pudieron cargar las reservas.</p>'; agendaHorasBody.innerHTML='<tr><td colspan="2" class="text-center text-danger py-3">No se pudo cargar la agenda.</td></tr>'; }
    }
    filtroActividad.addEventListener('change', renderizarActividad);
    filtroServicio.addEventListener('change', renderizarActividad);
    filtroProducto.addEventListener('change', renderizarActividad);
    async function refrescarCalendario() { await cargarTotales(); dibujar(); document.getElementById('calendarioAnterior').disabled = false; }
    document.getElementById('calendarioAnterior').addEventListener('click', () => { fechaVista.setMonth(fechaVista.getMonth()-1); refrescarCalendario(); });
    document.getElementById('calendarioSiguiente').addEventListener('click', () => { fechaVista.setMonth(fechaVista.getMonth()+1); refrescarCalendario(); }); refrescarCalendario();
});
</script>
@if(session('whatsapp_url'))
    <script>window.open(@json(session('whatsapp_url')), '_blank', 'noopener');</script>
@endif
@endsection
