@extends('adm_plantilla')

@section('content')
@php($editando = isset($promocion))
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="m-0 text-danger fw-bold"><i class="bi bi-tag me-2"></i>{{ $editando ? 'Editar promoción' : 'Nueva promoción' }}</h4>
            <small class="text-muted">Selecciona los servicios y define el precio especial y la vigencia.</small>
        </div>
        <a href="{{ route('servicio.lista') }}" class="btn btn-outline-secondary rounded-pill"><i class="bi bi-arrow-left me-1"></i>Volver a servicios</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger rounded-4"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ $editando ? route('promocion.actualizar', $promocion->id_promocion) : route('promocion.guardar') }}" method="POST" enctype="multipart/form-data" class="card border-0 shadow-sm rounded-4">
        @csrf
        @if($editando) @method('PUT') @endif
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-bold">Nombre de la promoción *</label><input class="form-control" name="nombre" maxlength="100" required value="{{ old('nombre', $promocion->nombre ?? '') }}" placeholder="Ej. Paquete de manicure y pedicure"></div>
                <div class="col-md-6"><label class="form-label fw-bold">Imagen</label><input class="form-control" type="file" name="imagen" accept="image/jpeg,image/png,image/webp"><div class="form-text">JPG, PNG o WEBP, máximo 5 MB.</div>
                    @if($editando && $promocion->imagen)<img class="mt-2 rounded-3" style="width:100px;height:75px;object-fit:cover" src="{{ asset('promociones/'.$promocion->imagen) }}" alt="Imagen actual">@endif
                </div>
                <div class="col-12"><label class="form-label fw-bold">Descripción</label><textarea class="form-control" name="descripcion" rows="3" maxlength="2000">{{ old('descripcion', isset($promocion) && $promocion->descripcion ? \Illuminate\Support\Str::lower($promocion->descripcion) : '') }}</textarea></div>
                <div class="col-md-6"><label class="form-label fw-bold">Fecha y hora de inicio *</label><input class="form-control" type="datetime-local" name="fecha_hora_inicio" required value="{{ old('fecha_hora_inicio', isset($promocion) ? $promocion->fecha_hora_inicio->format('Y-m-d\TH:i') : '') }}"></div>
                <div class="col-md-6"><label class="form-label fw-bold">Fecha y hora de fin *</label><input class="form-control" type="datetime-local" name="fecha_hora_fin" required value="{{ old('fecha_hora_fin', isset($promocion) ? $promocion->fecha_hora_fin->format('Y-m-d\TH:i') : '') }}"></div>
                <div class="col-md-4"><label class="form-label fw-bold">Precio de los servicios seleccionados</label><div class="input-group"><span class="input-group-text">Bs.</span><input id="total-servicios" class="form-control" readonly value="0.00"></div><div class="form-text">Suma normal de los servicios elegidos.</div></div>
                <div class="col-md-4"><label class="form-label fw-bold">Precio de promoción (Bs.) *</label><input class="form-control" type="number" name="precio" step="1" min="0" max="999999.99" required value="{{ old('precio', $promocion->precio ?? '') }}"></div>
                <div class="col-md-4"><label class="form-label fw-bold">Cantidad de clientes *</label><input class="form-control" type="number" name="cantidad_clientes" min="1" max="1000000" required value="{{ old('cantidad_clientes', $promocion->cantidad_clientes ?? 1) }}"></div>
                <div class="col-12">
                    <label class="form-label fw-bold">Servicios incluidos *</label>
                    <div class="border rounded-3 p-3" style="max-height:300px;overflow:auto">
                        @php($seleccionados = old('id_servicios', isset($promocion) ? $promocion->servicios->pluck('id_servicio')->all() : []))
                        @forelse($servicios as $servicio)
                            <label class="d-flex align-items-center justify-content-between gap-3 py-2 border-bottom">
                                <span><input class="form-check-input me-2 servicio-promocion" type="checkbox" name="id_servicios[]" value="{{ $servicio->id_servicio }}" data-precio="{{ $servicio->precio }}" @checked(in_array($servicio->id_servicio, $seleccionados))><strong>{{ $servicio->nombre }}</strong></span>
                                <span class="text-danger fw-semibold">Bs. {{ number_format((float)$servicio->precio, 2) }}</span>
                            </label>
                        @empty
                            <p class="text-muted mb-0">Registra servicios antes de crear promociones.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white border-0 text-center pb-4">
            <button class="btn btn-danger rounded-pill px-5 fw-bold" type="submit"><i class="bi bi-save me-1"></i>{{ $editando ? 'Guardar cambios' : 'Crear promoción' }}</button>
            <a class="btn btn-secondary rounded-pill px-4 ms-2" href="{{ route('servicio.lista') }}">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    const checksServiciosPromocion = document.querySelectorAll('.servicio-promocion');
    const totalServiciosPromocion = document.getElementById('total-servicios');
    const actualizarTotalServiciosPromocion = () => {
        const total = [...checksServiciosPromocion].filter(check => check.checked).reduce((suma, check) => suma + Number(check.dataset.precio || 0), 0);
        totalServiciosPromocion.value = total.toFixed(2);
    };
    checksServiciosPromocion.forEach(check => check.addEventListener('change', actualizarTotalServiciosPromocion));
    actualizarTotalServiciosPromocion();
</script>
@endsection
