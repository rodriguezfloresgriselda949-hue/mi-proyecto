<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Miss Uñas Valentina - Iniciar Sesión</title>

    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('style/style.css') }}">
</head>

<body>

    <!-- Imagen decorativa lateral -->
    <div class="imagen-lateral"></div>

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-4">
            <div class="col-md-5 col-lg-4">

                <div class="logo-container">
                    <img src="{{ asset('img/logo.jpeg') }}" alt="Miss Uñas Valentina">
                    <h2 class="titulo-salon">MISS UÑAS</h2>
                    <h4 class="subtitulo-salon">Valentina</h4>
                </div>

                <div id="formulario">
                    <h3><i class="bi bi-shield-lock-fill"></i> Acceso al Sistema</h3>

                    <!-- Alertas de sesión -->
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 p-2 small mb-3" role="alert">
                            <i class="bi bi-exclamation-circle-fill me-1"></i> {{ session('error') }}
                            <button type="button" class="btn-close p-2" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show rounded-3 p-2 small mb-3" role="alert">
                            <i class="bi bi-info-circle-fill me-1"></i> {{ session('info') }}
                            <button type="button" class="btn-close p-2" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('validar') }}">
                        @csrf

                        <div class="form-group mb-3">
                            <label><i class="bi bi-person-fill"></i> Usuario o Email</label>
                            <input
                                type="text"
                                name="usu"
                                class="form-control keep-case"
                                data-keep-case
                                autocomplete="username"
                                placeholder="Ingrese su usuario o correo"
                                value="{{ old('usu') }}"
                                maxlength="100"
                                required
                                autofocus>
                        </div>

                        <div class="form-group mb-3">
                            <label><i class="bi bi-key-fill"></i> Contraseña</label>
                            <input
                                type="password"
                                name="pwd"
                                class="form-control"
                                placeholder="Ingrese su contraseña"
                                maxlength="255"
                                required>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-ingresar">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Ingresar al Sistema
                            </button>
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('inicio') }}" class="text-secondary small text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i> Volver al Catálogo de Productos
                            </a>
                        </div>
                    </form>

                </div>

            </div>
        </div>
    </div>

    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/form-validation.js') }}"></script>
</body>
</html>
