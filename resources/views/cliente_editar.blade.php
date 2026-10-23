@php
    $layout = (strtolower(session('rol')) === 'administrador') ? 'adm_plantilla' : 'empleado_plantilla';
@endphp

@extends($layout)

@section('content')

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="m-0 text-danger fw-bold"><i class="bi bi-pencil-square me-2"></i> Editar Cliente</h4>
            <small class="text-muted">Modifica los datos del cliente #{{ $cliente->id_cliente }}</small>
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
            <form action="{{ route('cliente.actualizar', $cliente->id_cliente) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary">Nombre *</label>
                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $cliente->nombre) }}" minlength="2" maxlength="50" pattern="[A-Za-záéíóúÁÉÍÓÚñÑ ]+" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary">Apellido *</label>
                        <input type="text" name="apellido" class="form-control" value="{{ old('apellido', $cliente->apellido) }}" minlength="2" maxlength="100" pattern="[A-Za-záéíóúÁÉÍÓÚñÑ ]+" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary">Teléfono / Celular <small class="text-muted fw-normal">(Opcional)</small></label>
                        <input type="tel" name="telefono" class="form-control" value="{{ old('telefono', $cliente->telefono) }}" placeholder="Ej: 72541236" maxlength="20">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary">Correo Electrónico <small class="text-muted fw-normal">(Opcional)</small></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $cliente->email) }}" placeholder="ejemplo@gmail.com" maxlength="50">
                    </div>
                </div>

                <div class="text-center mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Actualizar Cliente
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
