@extends('adm_plantilla')

@section('content')
@php($editando = isset($reserva))
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div><h4 class="m-0 text-danger fw-bold"><i class="bi bi-calendar2-plus me-2"></i>{{ $editando ? 'Editar reserva de promoción' : 'Nueva reserva de promoción' }}</h4><small class="text-muted">Elige una promoción, cliente y horario dentro de su vigencia.</small></div>
        <a href="{{ route('reservas.promociones.index') }}" class="btn btn-outline-secondary rounded-pill"><i class="bi bi-arrow-left me-1"></i>Volver a reservas</a>
    </div>
    @if($errors->any())<div class="alert alert-danger rounded-4"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <form action="{{ $editando ? route('reservas.promociones.update', $reserva->id_reserva_promocion) : route('reservas.promociones.store') }}" method="POST" class="card border-0 shadow-sm rounded-4">
        @csrf
        @if($editando) @method('PUT') @endif
        <div class="card-body p-4"><div class="row g-3">
            <div class="col-md-6">
                <label for="id_promocion" class="form-label fw-bold">Promoción *</label>
                <select class="form-select" id="id_promocion" name="id_promocion" required>
                    <option value="">Selecciona una promoción...</option>
                    @foreach($promociones as $promocion)
                        @php($disponibles = max(0, $promocion->cantidad_clientes - $promocion->reservas_activas + ($editando && (int)$reserva->id_promocion === (int)$promocion->id_promocion && $reserva->estado !== 'Cancelado' ? 1 : 0)))
                        <option value="{{ $promocion->id_promocion }}" data-inicio="{{ $promocion->fecha_hora_inicio->format('Y-m-d\TH:i') }}" data-fin="{{ $promocion->fecha_hora_fin->format('Y-m-d\TH:i') }}" data-precio="{{ $promocion->precio }}" data-servicios="{{ $promocion->servicios->pluck('nombre')->join(', ') }}" {{ $disponibles < 1 && (!$editando || (int)$reserva->id_promocion !== (int)$promocion->id_promocion) ? 'disabled' : '' }} {{ old('id_promocion', $reserva->id_promocion ?? null) == $promocion->id_promocion ? 'selected' : '' }}>
                            {{ $promocion->nombre }} — Bs. {{ number_format((float)$promocion->precio, 2) }} — {{ $disponibles }} cupos
                        </option>
                    @endforeach
                </select>
                @if($promociones->isEmpty())<small class="text-danger">No hay promociones disponibles con vigencia futura.</small>@endif
            </div>
            <div class="col-md-6"><label for="id_cliente" class="form-label fw-bold">Cliente *</label><select class="form-select" id="id_cliente" name="id_cliente" required><option value="">Selecciona un cliente...</option>@foreach($clientes as $cliente)<option value="{{ $cliente->id_cliente }}" @selected(old('id_cliente', $reserva->id_cliente ?? null) == $cliente->id_cliente)>{{ $cliente->nombre }} {{ $cliente->apellido }} ({{ $cliente->telefono ?? 'Sin teléfono' }})</option>@endforeach</select></div>
            <div class="col-12"><div id="detalle-promocion" class="alert alert-light border d-none mb-0"></div></div>
            <div class="col-md-6"><label for="fecha" class="form-label fw-bold">Fecha *</label><input type="date" class="form-control" id="fecha" name="fecha" value="{{ old('fecha', isset($reserva) ? $reserva->fecha->format('Y-m-d') : '') }}" required></div>
            <div class="col-md-6"><label for="hora" class="form-label fw-bold">Hora *</label><input type="time" class="form-control" id="hora" name="hora" value="{{ old('hora', isset($reserva) ? $reserva->hora?->format('H:i') : '') }}" required></div>
            <div class="col-md-6"><label for="precio_promocion" class="form-label fw-bold">Precio de la promoción</label><div class="input-group"><span class="input-group-text">Bs.</span><input id="precio_promocion" class="form-control" readonly value="0.00"></div></div>
            <div class="col-md-6"><label for="adelanto" class="form-label fw-bold">Adelanto (Bs.) *</label><input type="number" class="form-control" id="adelanto" name="adelanto" value="{{ old('adelanto', 0) }}" min="0" step="1" required><small class="text-muted">El adelanto no puede superar el precio promocional.</small></div>
        </div></div>
        <div class="card-footer bg-white border-0 text-center pb-4"><button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold" @disabled($promociones->isEmpty())><i class="bi bi-calendar-check me-1"></i>{{ $editando ? 'Guardar cambios' : 'Registrar reserva' }}</button><a href="{{ route('reservas.promociones.index') }}" class="btn btn-secondary rounded-pill px-4 ms-2">Cancelar</a></div>
    </form>
</div>
@endsection

@section('scripts')
<script>
const selectPromocion = document.getElementById('id_promocion');
const fechaReservaPromo = document.getElementById('fecha');
const horaReservaPromo = document.getElementById('hora');
const adelantoReservaPromo = document.getElementById('adelanto');
const detallePromo = document.getElementById('detalle-promocion');
const aplicarVigenciaPromo = () => {
    const opcion = selectPromocion.selectedOptions[0];
    if (!opcion || !opcion.dataset.inicio) {
        fechaReservaPromo.removeAttribute('min'); fechaReservaPromo.removeAttribute('max');
        document.getElementById('precio_promocion').value = '0.00'; detallePromo.classList.add('d-none');
        return;
    }
    const inicio = opcion.dataset.inicio;
    const fin = opcion.dataset.fin;
    fechaReservaPromo.min = inicio.slice(0, 10); fechaReservaPromo.max = fin.slice(0, 10);
    document.getElementById('precio_promocion').value = Number(opcion.dataset.precio).toFixed(2);
    if (!adelantoReservaPromo.dataset.editado) adelantoReservaPromo.value = (Number(opcion.dataset.precio) * 0.30).toFixed(2);
    detallePromo.innerHTML = `<strong>Vigencia:</strong> ${inicio.replace('T', ' ')} a ${fin.replace('T', ' ')}<br><strong>Servicios:</strong> ${String(opcion.dataset.servicios || '—').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]))}`;
    detallePromo.classList.remove('d-none'); validarFechaHoraPromo();
};
const validarFechaHoraPromo = () => {
    const opcion = selectPromocion.selectedOptions[0];
    if (!opcion || !opcion.dataset.inicio || !fechaReservaPromo.value || !horaReservaPromo.value) return;
    const elegida = new Date(`${fechaReservaPromo.value}T${horaReservaPromo.value}`);
    const inicio = new Date(opcion.dataset.inicio);
    const fin = new Date(opcion.dataset.fin);
    const valida = elegida >= inicio && elegida <= fin;
    const mensaje = valida ? '' : 'El horario debe estar dentro del inicio y fin de la promoción.';
    fechaReservaPromo.setCustomValidity(mensaje); horaReservaPromo.setCustomValidity(mensaje);
};
selectPromocion.addEventListener('change', aplicarVigenciaPromo);
fechaReservaPromo.addEventListener('change', validarFechaHoraPromo);
horaReservaPromo.addEventListener('change', validarFechaHoraPromo);
adelantoReservaPromo.addEventListener('input', () => adelantoReservaPromo.dataset.editado = '1');
aplicarVigenciaPromo();
</script>
@endsection
