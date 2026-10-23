@php
    $layout = (strtolower(session('rol')) === 'administrador') ? 'adm_plantilla' : 'empleado_plantilla';
@endphp

@extends($layout)

@section('content')

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="m-0 text-danger fw-bold"><i class="bi bi-person-plus-fill me-2"></i> Registrar Nuevo Cliente</h4>
            <small class="text-muted">Ingresa los datos del cliente para asociarlo a reservas y ventas</small>
        </div>
        <a href="{{ route('cliente.lista') }}" class="btn btn-outline-secondary rounded-pill">
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
            <form action="{{ route('cliente.guardar') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nombre" class="form-label fw-bold text-secondary">Nombre *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre') }}" placeholder="Ej: Ana" minlength="2" maxlength="50" pattern="[A-Za-záéíóúÁÉÍÓÚñÑ ]+" required>
                    </div>

                    <div class="col-md-6">
                        <label for="apellido" class="form-label fw-bold text-secondary">Apellido *</label>
                        <input type="text" class="form-control" id="apellido" name="apellido" value="{{ old('apellido') }}" placeholder="Ej: López" minlength="2" maxlength="100" pattern="[A-Za-záéíóúÁÉÍÓÚñÑ ]+" required>
                    </div>

                    <div class="col-md-6">
                        <label for="telefono" class="form-label fw-bold text-secondary">Teléfono / Celular <small class="text-muted fw-normal">(Opcional)</small></label>
                        <input type="tel" class="form-control" id="telefono" name="telefono" value="{{ old('telefono') }}" placeholder="Ej: 72541236" maxlength="20">
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label fw-bold text-secondary">Correo Electrónico <small class="text-muted fw-normal">(Opcional)</small></label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="ejemplo@gmail.com" maxlength="50">
                    </div>
                </div>

                <div class="text-center mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm">
                        <i class="bi bi-save me-1"></i> Guardar Cliente
                    </button>
                    <a href="{{ route('cliente.lista') }}" class="btn btn-secondary rounded-pill px-4 ms-2">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
