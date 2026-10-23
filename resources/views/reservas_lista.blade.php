@php
    $layout = (strtolower(session('rol')) === 'administrador') ? 'adm_plantilla' : 'empleado_plantilla';
@endphp

@extends($layout)

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="m-0 text-danger fw-bold"><i class="bi bi-calendar-check-fill me-2"></i> Gestión de Reservas</h4>
        <small class="text-muted">Control de citas, horarios y servicios reservados por clientes</small>
    </div>
    <a href="{{ route('reservas.create') }}" class="btn btn-danger rounded-pill shadow-sm">
        <i class="bi bi-calendar-plus-fill me-1"></i> Nueva Reserva
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button></div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button></div>
@endif

<!-- Filtro único por periodo y estado -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('reservas.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label fw-bold small">Desde</label><input type="date" name="desde" class="form-control" value="{{ $fechaDesde }}"></div>
            <div class="col-md-3"><label class="form-label fw-bold small">Hasta</label><input type="date" name="hasta" class="form-control" value="{{ $fechaHasta }}"></div>
            <div class="col-md-3"><label class="form-label fw-bold small">Estado</label><select name="estado" class="form-select"><option value="todos" {{ $estadoFiltro === 'todos' ? 'selected' : '' }}>Todos</option><option value="pendientes" {{ $estadoFiltro === 'pendientes' ? 'selected' : '' }}>Pendientes</option><option value="completadas" {{ $estadoFiltro === 'completadas' ? 'selected' : '' }}>Completados</option><option value="canceladas" {{ $estadoFiltro === 'canceladas' ? 'selected' : '' }}>Cancelados</option></select></div>
            <div class="col-md-3 d-flex gap-2"><button class="btn btn-danger rounded-pill flex-fill"><i class="bi bi-funnel me-1"></i> Filtrar</button><a href="{{ route('reservas.index') }}" class="btn btn-outline-secondary rounded-pill">Hoy</a></div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-danger">
                    <tr>
                        <th class="rounded-start">#</th>
                        <th>Cliente</th>
                        <th>Servicio</th>
                        <th>Atendido por</th>
                        <th>Fecha y hora</th>
                        <th>Subtotal</th>
                        <th>Adelanto</th>
                        <th>Estado</th>
                        <th class="text-center rounded-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservas as $reserva)
                        <tr>
                            <td class="fw-bold text-danger">#{{ $reserva->id_reserva }}</td>
                            <td>
                                <span class="fw-bold"><i class="bi bi-person-fill text-danger me-1"></i> {{ $reserva->cliente->nombre ?? 'Cliente Ocasional' }} {{ $reserva->cliente->apellido ?? '' }}</span>
                                @if($reserva->cliente && $reserva->cliente->telefono)
                                    <small class="d-block text-muted"><i class="bi bi-telephone me-1"></i>{{ $reserva->cliente->telefono }}</small>
                                @endif
                            </td>
                            <td>
                                @forelse($reserva->servicios as $servicio)
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill">{{ $servicio->nombre }}</span>
                                @empty
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill">{{ $reserva->servicio->nombre ?? 'Servicio eliminado' }}</span>
                                @endforelse
                            </td>
                            <td>{{ $reserva->usuario->username ?? 'Sin asignar' }}</td>
                            <td><span class="d-block">{{ $reserva->fecha?->format('d/m/Y') ?? $reserva->fecha }}</span><span class="badge bg-light text-dark border">{{ substr((string) $reserva->getRawOriginal('hora'), 0, 5) }}</span></td>
                            <td class="fw-bold text-danger">Bs. {{ number_format((float)$reserva->subtotal, 2) }}</td>
                            <td class="fw-bold text-success">Bs. {{ number_format((float)$reserva->adelanto, 2) }}</td>
                            <td>
                                @php
                                    $badgeClass = match($reserva->estado) {
                                        'Completada', 'Finalizado' => 'bg-success',
                                        'En Atención', 'En Atencion' => 'bg-primary',
                                        'Cancelada', 'Cancelado' => 'bg-secondary',
                                        default => 'bg-warning text-dark',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} rounded-pill px-3 py-1">
                                    {{ $reserva->estado }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('reservas.comprobante', $reserva->id_reserva) }}" class="btn btn-outline-danger btn-sm rounded-pill px-2 me-1" title="Imprimir comprobante">
                                    <i class="bi bi-printer-fill"></i> Imprimir
                                </a>
                                @if(!in_array($reserva->estado, ['Completada', 'Finalizado', 'Cancelada', 'Cancelado'], true))
                                    <button type="button" class="btn btn-outline-info btn-sm rounded-pill px-2 me-1" data-bs-toggle="modal" data-bs-target="#modalTraspasoServicio" data-reserva-id="{{ $reserva->id_reserva }}" data-personal-id="{{ $reserva->id_usuario }}" data-reserva-label="#{{ $reserva->id_reserva }} · {{ $reserva->cliente->nombre ?? 'Cliente' }} {{ $reserva->cliente->apellido ?? '' }}" title="Traspasar un servicio a otro empleado">
                                        <i class="bi bi-arrow-left-right"></i> Traspaso
                                    </button>
                                @endif
                                <!-- Botón de cambio rápido de estado para pendientes -->
                                @if($reserva->estado === 'Reservado' || $reserva->estado === 'Pendiente')
                                    <form action="{{ route('reservas.cambiarEstado', $reserva->id_reserva) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="estado" value="En Atención">
                                        <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-2 me-1" title="Comenzar Atención">
                                            <i class="bi bi-play-fill"></i> Atender
                                        </button>
                                    </form>
                                @elseif($reserva->estado === 'En Atención' || $reserva->estado === 'En Atencion')
                                    <form action="{{ route('reservas.cambiarEstado', $reserva->id_reserva) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="estado" value="Completada">
                                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-2 me-1" title="Marcar como Completada">
                                            <i class="bi bi-check-lg"></i> Finalizar
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('reservas.edit', $reserva->id_reserva) }}" class="btn btn-outline-warning btn-sm rounded-pill px-2 me-1" title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </a>

                                @if(strtolower(session('rol')) === 'administrador')
                                    <form action="{{ route('reservas.destroy', $reserva->id_reserva) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar esta reserva?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2" title="Eliminar">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No hay reservas con los filtros seleccionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-none" aria-hidden="true">
    @foreach($reservas as $reserva)
        <template id="servicios-reserva-{{ $reserva->id_reserva }}">
            @forelse($reserva->servicios as $servicio)
                <option value="{{ $servicio->id_servicio }}">{{ $servicio->nombre }} — Bs. {{ number_format((float) $servicio->precio, 2) }}</option>
            @empty
                @if($reserva->servicio)
                    <option value="{{ $reserva->servicio->id_servicio }}">{{ $reserva->servicio->nombre }} — Bs. {{ number_format((float) $reserva->servicio->precio, 2) }}</option>
                @endif
            @endforelse
        </template>
    @endforeach
</div>

<div class="modal fade" id="modalTraspasoServicio" tabindex="-1" aria-labelledby="tituloModalTraspasoServicio" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><form id="formTraspasoServicio" method="POST" action="{{ route('reservas.traspasar', 999999999) }}" class="modal-content rounded-4 border-0 shadow">
        @csrf
        <div class="modal-header bg-info text-white"><h5 class="modal-title fw-bold" id="tituloModalTraspasoServicio"><i class="bi bi-arrow-left-right me-2"></i>Traspasar servicio</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
        <div class="modal-body">
            <p class="text-muted" id="detalleReservaTraspaso">Selecciona el servicio y el empleado que lo atenderá.</p>
            <div class="mb-3"><label for="servicioTraspaso" class="form-label fw-semibold">Servicio de esta reserva *</label><select class="form-select" id="servicioTraspaso" name="id_servicio" required><option value="">Selecciona un servicio...</option></select></div>
            <div class="mb-2"><label for="empleadoTraspaso" class="form-label fw-semibold">Traspasar a *</label><select class="form-select" id="empleadoTraspaso" name="id_usuario_destino" required><option value="">Selecciona otro empleado...</option>@foreach($usuariosTraspaso as $usuarioTraspaso)<option value="{{ $usuarioTraspaso->id_usuario }}" data-especialidades="{{ $usuarioTraspaso->especialidades->pluck('id_servicio')->implode(',') }}">{{ $usuarioTraspaso->username }} ({{ $usuarioTraspaso->rol }})</option>@endforeach</select><small class="text-muted" id="ayudaEmpleadoTraspaso">Se mostrarán empleados distintos al asignado y habilitados para el servicio.</small></div>
            <div class="alert alert-light border small mb-0 mt-3">El empleado debe estar libre en el momento del traspaso. La nueva reserva usará la fecha y hora actuales y no tendrá adelanto; el adelanto permanece en la reserva original.</div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-info text-white rounded-pill"><i class="bi bi-check2-circle me-1"></i>Confirmar traspaso</button></div>
    </form></div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalTraspasoServicio');
    if (!modal) return;
    document.body.appendChild(modal);
    const form = document.getElementById('formTraspasoServicio');
    const servicioSelect = document.getElementById('servicioTraspaso');
    const empleadoSelect = document.getElementById('empleadoTraspaso');
    const ayudaEmpleado = document.getElementById('ayudaEmpleadoTraspaso');
    const plantillaAccion = form.action;
    const empleados = [...empleadoSelect.options].slice(1).map(opcion => opcion.cloneNode(true));

    function filtrarEmpleados(idServicio, idPersonalOrigen) {
        empleadoSelect.innerHTML = '<option value="">Selecciona otro empleado...</option>';
        const disponibles = empleados.filter(opcion => opcion.value !== idPersonalOrigen && (opcion.dataset.especialidades || '').split(',').includes(String(idServicio)));
        disponibles.forEach(opcion => empleadoSelect.add(opcion.cloneNode(true)));
        ayudaEmpleado.textContent = disponibles.length
            ? 'Se muestran empleados habilitados para este servicio; su horario se verificará al confirmar.'
            : 'No hay otro empleado habilitado para este servicio.';
    }

    document.querySelectorAll('[data-bs-target="#modalTraspasoServicio"]').forEach(boton => {
        boton.addEventListener('click', () => {
            const idReserva = boton.dataset.reservaId;
            const plantillaServicios = document.getElementById(`servicios-reserva-${idReserva}`);
            form.action = plantillaAccion.replace('/999999999/', `/${idReserva}/`);
            document.getElementById('detalleReservaTraspaso').textContent = `Reserva ${boton.dataset.reservaLabel}. La nueva reserva usará la fecha y hora actuales.`;
            servicioSelect.innerHTML = '<option value="">Selecciona un servicio...</option>' + (plantillaServicios?.innerHTML || '');
            empleadoSelect.innerHTML = '<option value="">Selecciona otro empleado...</option>';
            ayudaEmpleado.textContent = 'Primero selecciona el servicio que deseas traspasar.';
            servicioSelect.onchange = () => filtrarEmpleados(servicioSelect.value, boton.dataset.personalId);
        });
    });
});
</script>
@endsection
