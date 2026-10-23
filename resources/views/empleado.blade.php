@extends('empleado_plantilla')

@section('content')

<!-- Saludo y Perfil -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div class="d-flex align-items-center">
        <div class="rounded-circle bg-danger text-white d-flex justify-content-center align-items-center fw-bold shadow-sm" style="width:50px;height:50px;font-size:18px;">
            {{ strtoupper(substr(session('username'), 0, 2)) }}
        </div>
        <div class="ms-3">
            <h4 class="m-0 text-danger fw-bold">Hola, {{ ucfirst(session('username')) }}</h4>
            <small class="text-muted">Panel del Personal - Miss Uñas Valentina</small>
        </div>
    </div>
    <a href="{{ route('reservas.create') }}" class="btn btn-danger rounded-pill shadow-sm px-3">
        <i class="bi bi-plus-lg me-1"></i> Nueva Cita / Reserva
    </a>
</div>

<!-- ========================================================= -->
<!-- SECCIÓN 1 (SUPERIOR): CITAS DE HOY -->
<!-- ========================================================= -->
<div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-danger border-4">
    <div class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="m-0 text-danger fw-bold">
                <i class="bi bi-calendar2-check-fill me-2"></i> Mis Citas de Hoy ({{ date('d/m/Y') }})
            </h5>
            <small class="text-muted">Atenciones y reservas programadas exclusivamente para la jornada de hoy</small>
        </div>
        <span class="badge bg-danger rounded-pill px-3 py-2">
            {{ $reservasHoy->count() }} {{ $reservasHoy->count() === 1 ? 'cita hoy' : 'citas hoy' }}
        </span>
    </div>
    <div class="card-body p-3">
        @forelse($reservasHoy as $reserva)
            <div class="card border rounded-3 mb-2 shadow-xs">
                <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-danger fs-6"><i class="bi bi-clock me-1"></i>{{ $reserva->hora }}</span>
                            <strong class="text-dark">{{ $reserva->cliente->nombre ?? 'Cliente Ocasional' }} {{ $reserva->cliente->apellido ?? '' }}</strong>
                            @if($reserva->cliente && $reserva->cliente->telefono)
                                <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $reserva->cliente->telefono }}</small>
                            @endif
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-tag text-danger me-1"></i>Servicio: <strong>{{ $reserva->servicio->nombre ?? 'N/A' }}</strong> &bull; Subtotal: <strong>Bs. {{ number_format((float)$reserva->subtotal, 2) }}</strong>
                        </small>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        @php
                            $badgeClass = match($reserva->estado) {
                                'Completada', 'Finalizado' => 'bg-success',
                                'En Atención', 'En Atencion' => 'bg-primary',
                                'Cancelada', 'Cancelado' => 'bg-secondary',
                                default => 'bg-warning text-dark',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }} rounded-pill px-3 py-2">
                            {{ $reserva->estado }}
                        </span>

                        @if($reserva->estado === 'Reservado' || $reserva->estado === 'Pendiente')
                            <form action="{{ route('reservas.cambiarEstado', $reserva->id_reserva) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="estado" value="En Atención">
                                <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    <i class="bi bi-play-fill me-1"></i> Iniciar Atención
                                </button>
                            </form>
                        @elseif($reserva->estado === 'En Atención' || $reserva->estado === 'En Atencion')
                            <form action="{{ route('reservas.cambiarEstado', $reserva->id_reserva) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="estado" value="Completada">
                                <button type="submit" class="btn btn-success btn-sm rounded-pill px-3">
                                    <i class="bi bi-check2-circle me-1"></i> Finalizar Servicio
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-4 text-muted">
                <i class="bi bi-calendar-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                <p class="mb-0 fw-semibold">No tienes citas programadas para el día de hoy.</p>
                <small class="text-muted">Puedes consultar tu historial o registrar una nueva reserva.</small>
            </div>
        @endforelse
    </div>
</div>

<!-- ========================================================= -->
<!-- SECCIÓN 2 (INFERIOR): FILTRO Y CONSULTA POR RANGO -->
<!-- ========================================================= -->
<h5 class="text-danger fw-bold mt-4 mb-3">
    <i class="bi bi-filter-square-fill me-2"></i> Consulta de Citas por Rango de Fechas
</h5>

<!-- Formulario Filtro -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form class="row g-2 align-items-end" method="GET" action="{{ route('empleado.dashboard') }}">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-secondary mb-1">Fecha Desde:</label>
                <input type="date" class="form-control" name="fecha_desde" value="{{ $fechaDesde }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold text-secondary mb-1">Fecha Hasta:</label>
                <input type="date" class="form-control" name="fecha_hasta" value="{{ $fechaHasta }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-danger rounded-pill w-100 fw-bold">
                    <i class="bi bi-search me-1"></i> Consultar Período
                </button>
                <a href="{{ route('empleado.dashboard') }}" class="btn btn-outline-secondary rounded-pill w-50" title="Restablecer">
                    Mes Actual
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tarjetas Resumen del Período -->
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card border-0 shadow-sm rounded-4 p-2">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted fw-bold">Citas en el Período</small>
                    <h3 class="text-danger fw-bold m-0">{{ $reservasPeriodo->count() }}</h3>
                </div>
                <div class="badge bg-danger-subtle text-danger rounded-circle p-3"><i class="bi bi-calendar-range fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm rounded-4 p-2">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted fw-bold">Pendientes / En Curso</small>
                    <h3 class="text-warning fw-bold m-0">{{ $reservasPendientes->count() }}</h3>
                </div>
                <div class="badge bg-warning-subtle text-warning rounded-circle p-3"><i class="bi bi-clock-history fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm rounded-4 p-2">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted fw-bold">Completadas</small>
                    <h3 class="text-success fw-bold m-0">{{ $reservasCompletadas->count() }}</h3>
                </div>
                <div class="badge bg-success-subtle text-success rounded-circle p-3"><i class="bi bi-check-circle fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-2">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div><small class="text-muted fw-bold">Total de Ventas</small><h3 class="text-primary fw-bold m-0">Bs. {{ number_format($totalVentasPeriodo, 2) }}</h3></div>
                <div class="badge bg-primary-subtle text-primary rounded-circle p-3"><i class="bi bi-cash-stack fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-2">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div><small class="text-muted fw-bold">Servicios Cobrados</small><h3 class="text-success fw-bold m-0">Bs. {{ number_format($totalServiciosPeriodo, 2) }}</h3></div>
                <div class="badge bg-success-subtle text-success rounded-circle p-3"><i class="bi bi-scissors fs-4"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
        <h6 class="text-primary fw-bold m-0"><i class="bi bi-cart-check me-1"></i> Mis Ventas ({{ $ventasPeriodo->count() }})</h6>
        <span class="badge bg-primary rounded-pill">Total: Bs. {{ number_format($totalVentasPeriodo, 2) }}</span>
    </div>
    <div class="card-body p-3">
        @forelse($ventasPeriodo as $venta)
            <div class="card border-0 bg-light rounded-3 mb-2"><div class="card-body py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div><span class="badge bg-secondary-subtle text-secondary me-2">{{ is_object($venta->fecha) ? $venta->fecha->format('d/m/Y') : $venta->fecha }}</span><strong>Venta #{{ $venta->id_venta }}</strong><small class="text-muted ms-2">{{ $venta->cliente->nombre ?? 'Cliente ocasional' }} {{ $venta->cliente->apellido ?? '' }}</small></div>
                <strong class="text-primary">Bs. {{ number_format((float) $venta->total, 2) }}</strong>
            </div></div>
        @empty
            <p class="text-muted text-center py-2 mb-0">No registraste ventas en el rango de fechas seleccionado.</p>
        @endforelse
    </div>
</div>

<!-- Grupo 1: Citas Pendientes en el Período -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
        <h6 class="text-warning fw-bold m-0">
            <i class="bi bi-hourglass-split me-1"></i> Citas Pendientes y En Espera ({{ $reservasPendientes->count() }})
        </h6>
    </div>
    <div class="card-body p-3">
        @forelse($reservasPendientes as $reserva)
            <div class="card border rounded-3 mb-2">
                <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <span class="badge bg-light text-dark border me-2">{{ is_object($reserva->fecha) ? $reserva->fecha->format('d/m/Y') : $reserva->fecha }} &bull; {{ $reserva->hora }}</span>
                        <strong>{{ $reserva->cliente->nombre ?? 'Cliente Ocasional' }} {{ $reserva->cliente->apellido ?? '' }}</strong>
                        <small class="text-muted ms-2">&bull; Servicio: {{ $reserva->servicio->nombre ?? 'N/A' }} (Bs. {{ number_format((float)$reserva->subtotal, 2) }})</small>
                    </div>
                    <div>
                        <span class="badge bg-warning text-dark rounded-pill me-2">{{ $reserva->estado }}</span>
                        @if($reserva->estado === 'Reservado' || $reserva->estado === 'Pendiente')
                            <form action="{{ route('reservas.cambiarEstado', $reserva->id_reserva) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="estado" value="En Atención">
                                <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill">Atender</button>
                            </form>
                        @elseif($reserva->estado === 'En Atención' || $reserva->estado === 'En Atencion')
                            <form action="{{ route('reservas.cambiarEstado', $reserva->id_reserva) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="estado" value="Completada">
                                <button type="submit" class="btn btn-success btn-sm rounded-pill">Finalizar</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted text-center py-2 mb-0">No tienes citas pendientes en el rango de fechas seleccionado.</p>
        @endforelse
    </div>
</div>

<!-- Grupo 2: Citas Completadas en el Período -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
        <h6 class="text-success fw-bold m-0">
            <i class="bi bi-check2-all me-1"></i> Citas Completadas ({{ $reservasCompletadas->count() }})
        </h6>
    </div>
    <div class="card-body p-3">
        @forelse($reservasCompletadas as $reserva)
            <div class="card border-0 bg-light rounded-3 mb-2">
                <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <span class="badge bg-secondary-subtle text-secondary me-2">{{ is_object($reserva->fecha) ? $reserva->fecha->format('d/m/Y') : $reserva->fecha }} &bull; {{ $reserva->hora }}</span>
                        <strong>{{ $reserva->cliente->nombre ?? 'Cliente Ocasional' }} {{ $reserva->cliente->apellido ?? '' }}</strong>
                        <small class="text-muted ms-2">&bull; {{ $reserva->servicio->nombre ?? 'N/A' }}</small>
                    </div>
                    <div>
                        <span class="text-success fw-bold me-2">Bs. {{ number_format((float)$reserva->subtotal, 2) }}</span>
                        <span class="badge bg-success rounded-pill">{{ $reserva->estado }}</span>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted text-center py-2 mb-0">No hay citas completadas en el rango de fechas seleccionado.</p>
        @endforelse
    </div>
</div>

<!-- Grupo 3: Citas Canceladas en el Período -->
@if($reservasCanceladas->count() > 0)
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 pt-3 pb-2">
        <h6 class="text-secondary fw-bold m-0">
            <i class="bi bi-x-octagon me-1"></i> Citas Canceladas ({{ $reservasCanceladas->count() }})
        </h6>
    </div>
    <div class="card-body p-3">
        @foreach($reservasCanceladas as $reserva)
            <div class="card border-0 bg-light rounded-3 mb-2 text-muted">
                <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-secondary me-2">{{ is_object($reserva->fecha) ? $reserva->fecha->format('d/m/Y') : $reserva->fecha }} &bull; {{ $reserva->hora }}</span>
                        <span>{{ $reserva->cliente->nombre ?? 'Cliente Ocasional' }} {{ $reserva->cliente->apellido ?? '' }}</span>
                        <small class="ms-2">({{ $reserva->servicio->nombre ?? 'N/A' }})</small>
                    </div>
                    <span class="badge bg-secondary rounded-pill">{{ $reserva->estado }}</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

@endsection
