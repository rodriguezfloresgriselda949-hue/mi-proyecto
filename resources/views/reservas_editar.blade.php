@php
    $layout = (strtolower(session('rol')) === 'administrador') ? 'adm_plantilla' : 'empleado_plantilla';
@endphp

@extends($layout)

@section('content')

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="m-0 text-danger fw-bold"><i class="bi bi-pencil-square me-2"></i> Editar Reserva #{{ $reserva->id_reserva }}</h4>
            <small class="text-muted">Modifica la fecha, hora, estado o servicio asignado</small>
        </div>
        <a href="{{ route('reservas.index') }}" class="btn btn-outline-secondary rounded-pill">
            <i class="bi bi-arrow-left me-1"></i> Volver a la lista
        </a>
    </div>

    @if (isset($errors) && $errors->any())
        <div class="alert alert-danger rounded-4 shadow-sm mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('reservas.update', $reserva->id_reserva) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <!-- Cliente -->
                    <div class="col-md-6">
                        <label for="id_cliente" class="form-label fw-bold text-secondary">Cliente *</label>
                        <select class="form-select" id="id_cliente" name="id_cliente" required>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id_cliente }}" {{ old('id_cliente', $reserva->id_cliente) == $cliente->id_cliente ? 'selected' : '' }}>
                                    {{ $cliente->nombre }} {{ $cliente->apellido }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Servicios -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary">Servicios *</label>
                        @php($serviciosSeleccionados = old('id_servicios', $reserva->servicios->pluck('id_servicio')->all() ?: [$reserva->id_servicio]))
                        <div class="border rounded-3 p-2" style="max-height: 180px; overflow-y: auto;">
                            @foreach($servicios as $servicio)
                                <div class="form-check py-1 servicio-opcion" data-servicio="{{ $servicio->id_servicio }}">
                                    <input class="form-check-input servicio-check" type="checkbox" name="id_servicios[]" value="{{ $servicio->id_servicio }}" data-precio="{{ $servicio->precio }}" id="servicio{{ $servicio->id_servicio }}" {{ in_array($servicio->id_servicio, $serviciosSeleccionados) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="servicio{{ $servicio->id_servicio }}">{{ $servicio->nombre }} <span class="text-muted">— Bs. {{ number_format($servicio->precio, 2) }}</span></label>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted" id="ayudaServicios">Total: <strong id="totalServicios">Bs. 0.00</strong></small>
                    </div>

                    <!-- Atendido Por -->
                    <div class="col-md-6">
                        <label for="id_usuario" class="form-label fw-bold text-secondary">Personal Asignado</label>
                        <select class="form-select" id="id_usuario" name="id_usuario">
                            @foreach($usuarios as $u)
                                <option value="{{ $u->id_usuario }}" {{ old('id_usuario', $reserva->id_usuario) == $u->id_usuario ? 'selected' : '' }}>
                                    {{ $u->username }} ({{ $u->rol }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Estado -->
                    <div class="col-md-6">
                        <label for="estado" class="form-label fw-bold text-secondary">Estado *</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="Reservado" {{ old('estado', $reserva->estado) == 'Reservado' ? 'selected' : '' }}>Reservado</option>
                            <option value="Pendiente" {{ old('estado', $reserva->estado) == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="En Atención" {{ old('estado', $reserva->estado) == 'En Atención' ? 'selected' : '' }}>En Atención</option>
                            <option value="En Atencion" {{ old('estado', $reserva->estado) == 'En Atencion' ? 'selected' : '' }}>En Atención (registro anterior)</option>
                            <option value="Completada" {{ old('estado', $reserva->estado) == 'Completada' ? 'selected' : '' }}>Completada</option>
                            <option value="Finalizado" {{ old('estado', $reserva->estado) == 'Finalizado' ? 'selected' : '' }}>Finalizado (registro anterior)</option>
                            <option value="Cancelada" {{ old('estado', $reserva->estado) == 'Cancelada' ? 'selected' : '' }}>Cancelada</option>
                            <option value="Cancelado" {{ old('estado', $reserva->estado) == 'Cancelado' ? 'selected' : '' }}>Cancelado (registro anterior)</option>
                        </select>
                    </div>

                    <!-- Fecha -->
                    <div class="col-md-6">
                        <label for="fecha" class="form-label fw-bold text-secondary">Fecha *</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" value="{{ old('fecha', is_object($reserva->fecha) ? $reserva->fecha->format('Y-m-d') : $reserva->fecha) }}" required>
                    </div>

                    <!-- Hora -->
                    <div class="col-md-6">
                        <label for="hora" class="form-label fw-bold text-secondary">Hora *</label>
                        <select class="form-select" id="hora" name="hora" required>
                            <option value="">Cargando horarios disponibles...</option>
                        </select>
                        <small class="text-muted" id="ayudaHorasDisponibles">Se mostrarán únicamente las horas libres para el personal y fecha elegidos.</small>
                    </div>

                    <div class="col-md-6">
                        <label for="adelanto" class="form-label fw-bold text-secondary">Adelanto (Bs.) *</label>
                        <input type="number" class="form-control" id="adelanto" name="adelanto" value="{{ old('adelanto', $reserva->adelanto ?? 0) }}" min="0" step="1" required>
                        <small class="text-muted">Recomendado: 30% del total. Puede ajustarlo.</small>
                    </div>
                </div>

                <div class="text-center mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Actualizar Reserva
                    </button>
                    <a href="{{ route('reservas.index') }}" class="btn btn-secondary rounded-pill px-4 ms-2">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const especialidadesPorUsuario = @json($usuarios->mapWithKeys(fn ($usuario) => [$usuario->id_usuario => $usuario->especialidades->pluck('id_servicio')->values()]));
const selectPersonal = document.getElementById('id_usuario');
const selectHora = document.getElementById('hora');
const fechaReserva = document.getElementById('fecha');
const ayudaHorasDisponibles = document.getElementById('ayudaHorasDisponibles');
const horaInicial = @json(old('hora', $reserva->hora?->format('H:i') ?? ''));
const urlHorasDisponibles = @json(route('reservas.horasDisponibles'));
const idReservaActual = @json($reserva->id_reserva);
const horasJornada = Array.from({length: 11}, (_, indice) => `${String(indice + 9).padStart(2, '0')}:00`);
let solicitudHoras = 0;
const mostrarHoras = (horas, horaSeleccionada = '') => {
    selectHora.innerHTML = '<option value="">Seleccione una hora...</option>';
    horas.forEach(hora => selectHora.add(new Option(hora, hora)));
    if (horaSeleccionada && horas.includes(horaSeleccionada)) selectHora.value = horaSeleccionada;
    selectHora.disabled = false;
};
const cargarHorasDisponibles = async () => {
    const numeroSolicitud = ++solicitudHoras;
    const fecha = fechaReserva.value;
    const idUsuario = selectPersonal.value;
    const horaSeleccionada = selectHora.value || horaInicial;
    mostrarHoras(horasJornada, horaSeleccionada);
    if (!fecha || !idUsuario) {
        return;
    }
    try {
        const parametros = new URLSearchParams({fecha, id_usuario: idUsuario, id_reserva: idReservaActual});
        const respuesta = await fetch(`${urlHorasDisponibles}?${parametros}`, {headers: {Accept: 'application/json'}});
        if (!respuesta.ok) throw new Error();
        const datos = await respuesta.json();
        if (numeroSolicitud !== solicitudHoras) return;
        const horas = datos.horas_disponibles || [];
        mostrarHoras(horas, selectHora.value || horaSeleccionada);
        ayudaHorasDisponibles.textContent = horas.length ? 'Las horas ocupadas para este personal no se muestran.' : 'No hay horas disponibles para esta fecha y personal.';
    } catch {
        if (numeroSolicitud !== solicitudHoras) return;
        mostrarHoras(horasJornada, selectHora.value || horaSeleccionada);
        ayudaHorasDisponibles.textContent = 'No se pudo consultar la disponibilidad. Puede elegir una hora; se verificará al guardar.';
    }
};
const filtrarServiciosPorPersonal = () => {
    const permitidos = (especialidadesPorUsuario[selectPersonal.value] || []).map(Number);
    document.querySelectorAll('.servicio-opcion').forEach(opcion => {
        const visible = permitidos.includes(Number(opcion.dataset.servicio));
        opcion.classList.toggle('d-none', !visible);
        const check = opcion.querySelector('.servicio-check');
        if (!visible) check.checked = false;
    });
    document.getElementById('ayudaServicios').firstChild.textContent = permitidos.length ? 'Servicios disponibles para este personal. Total: ' : 'El personal seleccionado no tiene servicios registrados. Total: ';
    actualizarTotalServicios();
};
const actualizarTotalServicios = () => {
    const total = [...document.querySelectorAll('.servicio-check:checked')].reduce((s, item) => s + Number(item.dataset.precio), 0);
    document.getElementById('totalServicios').textContent = `Bs. ${total.toFixed(2)}`;
    const adelanto = document.getElementById('adelanto');
    if (!adelanto.dataset.editado) adelanto.value = (total * 0.30).toFixed(2);
};
document.querySelectorAll('.servicio-check').forEach(item => item.addEventListener('change', actualizarTotalServicios));
selectPersonal.addEventListener('change', filtrarServiciosPorPersonal);
selectPersonal.addEventListener('change', cargarHorasDisponibles);
fechaReserva.addEventListener('change', cargarHorasDisponibles);
filtrarServiciosPorPersonal();
if (horaInicial) selectHora.dataset.horaInicial = horaInicial;
cargarHorasDisponibles();
document.getElementById('adelanto').addEventListener('input', event => event.target.dataset.editado = '1');
actualizarTotalServicios();
</script>
@endsection
