@extends('empleado_plantilla')

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <div class="rounded-circle bg-danger text-white d-flex justify-content-center align-items-center shadow-sm" style="width:55px;height:55px;">
        <i class="bi bi-person-gear fs-3"></i>
    </div>
    <div>
        <h4 class="m-0 text-danger fw-bold">Mi perfil</h4>
        <small class="text-muted">Actualiza tus datos personales y de acceso.</small>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('empleado.perfil.update') }}" enctype="multipart/form-data" class="row g-3">
            @csrf
            @method('PUT')

            <div class="col-12 d-flex align-items-center gap-3 pb-2 border-bottom">
                @if($usuario->foto)
                    <img src="{{ asset('empleados/' . $usuario->foto) }}" alt="Foto de perfil" class="rounded-circle border" width="80" height="80" style="object-fit:cover">
                @else
                    <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center fw-bold fs-4" style="width:80px;height:80px;">
                        {{ strtoupper(substr($usuario->username, 0, 2)) }}
                    </div>
                @endif
                <div>
                    <label for="foto" class="form-label fw-semibold mb-1">Foto de perfil</label>
                    <input id="foto" name="foto" type="file" class="form-control @error('foto') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
                    <div class="form-text">JPG, PNG o WEBP. Máximo 2 MB.</div>
                    @error('foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-md-6">
                <label for="username" class="form-label fw-semibold">Nombre de usuario</label>
                <input id="username" name="username" type="text" value="{{ old('username', $usuario->username) }}" class="form-control @error('username') is-invalid @enderror" minlength="3" maxlength="50" required>
                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label fw-semibold">Correo electrónico</label>
                <input id="email" name="email" type="email" value="{{ old('email', $usuario->email) }}" class="form-control @error('email') is-invalid @enderror" maxlength="100" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="telefono" class="form-label fw-semibold">Teléfono</label>
                <input id="telefono" name="telefono" type="tel" value="{{ old('telefono', $usuario->telefono) }}" class="form-control @error('telefono') is-invalid @enderror" maxlength="20" placeholder="Ej.: 71234567">
                @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="password" class="form-label fw-semibold">Nueva contraseña</label>
                <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" minlength="8" autocomplete="new-password" placeholder="Déjalo vacío para conservarla">
                <div class="form-text">Solo complétala si deseas cambiar tu contraseña.</div>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 pt-2">
                <button type="submit" class="btn btn-danger rounded-pill px-4"><i class="bi bi-check2-circle me-1"></i> Guardar cambios</button>
                <a href="{{ route('empleado.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4 ms-1">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
