@php
    $layout = (strtolower(session('rol')) === 'administrador') ? 'adm_plantilla' : 'empleado_plantilla';
@endphp

@extends($layout)

@section('content')

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="m-0 text-danger fw-bold"><i class="bi bi-pencil-square me-2"></i> Editar Servicio</h4>
            <small class="text-muted">Modifica los detalles y precios del servicio #{{ $servicio->id_servicio }}</small>
        </div>
        <a href="{{ route('servicio.lista') }}" class="btn btn-outline-secondary rounded-pill">
            <i class="bi bi-arrow-left me-1"></i> Volver al Catálogo
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
            <form action="{{ route('servicio.actualizar', $servicio->id_servicio) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nombre" class="form-label fw-bold text-secondary">Nombre del Servicio *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre', $servicio->nombre) }}" minlength="2" maxlength="50" required>
                    </div>

                    <div class="col-md-6">
                        <label for="precio" class="form-label fw-bold text-secondary">Precio (Bs.) *</label>
                        <input type="number" step="1" class="form-control" id="precio" name="precio" value="{{ old('precio', $servicio->precio) }}" min="0" max="999999.99" required>
                    </div>

                    <div class="col-md-12">
                        <label for="descripcion" class="form-label fw-bold text-secondary">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" maxlength="100">{{ old('descripcion', \Illuminate\Support\Str::lower($servicio->descripcion ?? '')) }}</textarea>
                    </div>
                    <div class="col-md-12">
                        <label for="imagen" class="form-label fw-bold text-secondary">Imagen promocional</label>
                        @if($servicio->imagen && file_exists(public_path('servicios/' . $servicio->imagen)))
                            <div class="mb-2"><img src="{{ asset('servicios/' . $servicio->imagen) }}" alt="Imagen de {{ $servicio->nombre }}" class="rounded-3 border" style="width:160px;height:110px;object-fit:cover"></div>
                        @endif
                        <input type="file" class="form-control" id="imagen" name="imagen" accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">Sube otra imagen solo si deseas reemplazar la actual. JPG, PNG o WEBP, máximo 5 MB.</div>
                    </div>
                </div>

                <div class="text-center mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Actualizar Servicio
                    </button>
                    <a href="{{ route('servicio.lista') }}" class="btn btn-secondary rounded-pill px-4 ms-2">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
