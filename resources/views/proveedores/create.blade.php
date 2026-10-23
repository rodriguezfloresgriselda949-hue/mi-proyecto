@extends('adm_plantilla')

@section('content')

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="m-0 text-danger fw-bold"><i class="bi bi-truck me-2"></i> Registrar Nuevo Proveedor</h4>
            <small class="text-muted">Ingresa los datos de contacto del distribuidor o proveedor</small>
        </div>
        <a href="{{ route('proveedores.index') }}" class="btn btn-outline-secondary rounded-pill">
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
            <form action="{{ route('proveedores.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary">Nombre *</label>
                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" placeholder="Ej: Carlos" minlength="2" maxlength="100" pattern="[A-Za-záéíóúÁÉÍÓÚñÑ ]+" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary">Apellido *</label>
                        <input type="text" name="apellido" class="form-control" value="{{ old('apellido') }}" placeholder="Ej: Mamani" minlength="2" maxlength="100" pattern="[A-Za-záéíóúÁÉÍÓÚñÑ ]+" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary">Nombre de la empresa *</label>
                        <input type="text" name="nombre_empresa" class="form-control" value="{{ old('nombre_empresa') }}" placeholder="Ej: Distribuidora Belleza Total" minlength="2" maxlength="150" pattern="[A-Za-záéíóúÁÉÍÓÚñÑ0-9 .,()&amp;-]+" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary">Teléfono / Celular</label>
                        <input type="tel" name="telefono" class="form-control" value="{{ old('telefono') }}" placeholder="Ej: 72457896" minlength="6" maxlength="20" pattern="[0-9+\- ]+">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary">Correo Electrónico</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="proveedor@gmail.com" maxlength="100">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold text-secondary">Dirección / Ubicación</label>
                        <textarea name="direccion" class="form-control" rows="2" maxlength="255" placeholder="Ciudad, calle o zona del proveedor">{{ old('direccion') }}</textarea>
                    </div>
                </div>

                <div class="text-center mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm">
                        <i class="bi bi-save me-1"></i> Guardar Proveedor
                    </button>
                    <a href="{{ route('proveedores.index') }}" class="btn btn-secondary rounded-pill px-4 ms-2">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
