@extends('adm_plantilla')

@section('content')

<!-- ==========================================================
     ENCABEZADO
     ========================================================== -->

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

    <div>

        <h4 class="m-0 text-danger fw-bold">

            <i class="fa-solid fa-users me-2"></i>

            Gestión de Usuarios

        </h4>

        <small class="text-muted">

            Administra las cuentas de acceso y roles del personal

        </small>

    </div>


    <button type="button"
            class="btn btn-danger rounded-pill shadow-sm"
            id="btnNuevo">

        <i class="fa-solid fa-plus me-1"></i>

        Nuevo Usuario

    </button>

</div>


<!-- ==========================================================
     TABLA DE USUARIOS
     ========================================================== -->

<div class="card border-0 shadow-sm rounded-4">

    <div class="card-body p-3">

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-danger">

                    <tr>

                        <th>#</th>

                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>

                        <th class="text-center">Acciones</th>

                    </tr>

                </thead>


                <tbody id="cuerpoTabla">

                    <tr>

                        <td colspan="5"
                            class="text-center py-4">

                            <span class="spinner-border spinner-border-sm text-danger me-2"></span>

                            Cargando usuarios...

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>



<!-- ==========================================================
     MODAL NUEVO / ACTUALIZAR USUARIO
     ========================================================== -->

<div class="modal fade"
     id="modalUsuario"
     tabindex="-1"
     aria-labelledby="tituloModal"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content rounded-4 border-0 shadow">


            <!-- ==================================================
                 FORMULARIO
                 ================================================== -->

            <form id="formUsuario">


                <!-- ==================================================
                     HEADER
                     ================================================== -->

                <div class="modal-header bg-danger text-white">

                    <h5 class="modal-title fw-bold"
                        id="tituloModal">

                        Nuevo Usuario

                    </h5>


                    <button type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal"
                            aria-label="Cerrar">

                    </button>

                </div>



                <!-- ==================================================
                     CUERPO DEL FORMULARIO
                     ================================================== -->

                <div class="modal-body p-4">


                    <!-- ID DEL USUARIO -->

                    <input type="hidden"
                           id="id_usuario"
                           name="id_usuario">

                    <div class="mb-3">
                        <label for="foto" class="form-label fw-bold">Foto del usuario <span id="textoFotoObligatoria">*</span></label>
                        <div class="d-flex align-items-center gap-3">
                            <img id="vistaPreviaFoto" src="" alt="Vista previa" class="rounded-circle border d-none" width="72" height="72" style="object-fit: cover;">
                            <div class="flex-grow-1">
                                <input type="file" class="form-control" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" required>
                                <div class="form-text">JPG, PNG o WEBP. Tamaño máximo: 2 MB.</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="telefono" class="form-label fw-bold">Teléfono</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="Ej: 71234567" maxlength="20" pattern="[0-9+()\-\s]+">
                        <div class="form-text">Opcional. Use números y, si necesita, +, paréntesis o guiones.</div>
                    </div>

                    <div class="mb-3">
                        <label for="telefono_emergencia" class="form-label fw-bold">Número de emergencia</label>
                        <input type="tel" class="form-control" id="telefono_emergencia" name="telefono_emergencia" placeholder="Ej: 71234567" maxlength="20" pattern="[0-9+()\-\s]+">
                        <div class="form-text">Teléfono de contacto en caso de emergencia. Opcional.</div>
                    </div>



                    <!-- ==================================================
                         NOMBRE DE USUARIO
                         ================================================== -->

                    <div class="mb-3">

                        <label for="username"
                               class="form-label fw-bold">

                            Nombre de Usuario

                        </label>


                        <input type="text"
                               class="form-control"
                               id="username"
                               name="username"
                               placeholder="Ej: valentina"
                               minlength="3"
                               maxlength="50"
                               autocomplete="off"
                               pattern="[A-Za-z0-9_.-]+"
                               title="Use letras, números, punto, guion o guion bajo."
                               required>


                        <div class="form-text">

                            Use letras, números, punto, guion o guion bajo.

                        </div>


                        <div id="errorUsername"
                             class="text-danger small mt-1"
                             style="display:none;">

                            Use letras, números, punto, guion o guion bajo.

                        </div>

                    </div>



                    <!-- ==================================================
                         CORREO ELECTRÓNICO
                         ================================================== -->

                    <div class="mb-3">

                        <label for="email"
                               class="form-label fw-bold">

                            Correo Electrónico

                        </label>


                        <input type="email"
                               class="form-control"
                               id="email"
                               name="email"
                               placeholder="usuario@gmail.com"
                               maxlength="100"
                               autocomplete="off"
                               required>

                    </div>



                    <!-- ==================================================
                         CONTRASEÑA
                         ================================================== -->

                    <div class="mb-3">

                        <label for="password"
                               class="form-label fw-bold">

                            Contraseña

                            <small id="ayudaPass"
                                   class="text-muted fw-normal">

                            </small>

                        </label>


                        <input type="password"
                               class="form-control"
                               id="password"
                               name="password"
                               placeholder="Contraseña"
                               minlength="8"
                               maxlength="255"
                               autocomplete="new-password">


                    </div>



                    <!-- ==================================================
                         ROL
                         ================================================== -->

                    <div class="mb-3">

                        <label for="rol"
                               class="form-label fw-bold">

                            Rol de Acceso

                        </label>


                        <select class="form-select"
                                id="rol"
                                name="rol"
                                required>

                            <option value="">
                                Seleccione un rol...
                            </option>

                            <option value="Administrador">
                                Administrador
                            </option>

                            <option value="Empleado">
                                Empleado
                            </option>

                            <option value="Cliente">
                                Cliente
                            </option>

                        </select>

                    </div>



                    <div class="mb-3 d-none" id="grupoPago">

                        <label for="pago" class="form-label fw-bold">
                            Tipo de pago
                        </label>

                        <select class="form-select" id="pago" name="pago">
                            <option value="" selected hidden>Seleccione un tipo de pago</option>
                            <option value="Mensual">Mensual</option>
                            <option value="Quincenal">Quincenal</option>
                        </select>

                        <div class="form-text">Opcional; solo aplica a empleados.</div>

                    </div>

                    <div class="mb-3 d-none" id="grupoExperiencia">
                        <label for="experiencia" class="form-label fw-bold">Experiencia / porcentaje de pago</label>
                        <select class="form-select" id="experiencia" name="experiencia">
                            <option value="" selected hidden>Seleccione el porcentaje</option>
                            <option value="30">30%</option>
                            <option value="35">35%</option>
                            <option value="40">40%</option>
                            <option value="50">50%</option>
                        </select>
                        <div class="form-text">Este porcentaje se aplicará a los servicios concluidos al calcular su pago.</div>
                    </div>

                    <div class="mb-3 d-none" id="grupoEspecialidades">
                        <label class="form-label fw-bold">Servicios que realiza *</label>
                        <div class="border rounded-3 p-3" style="max-height: 190px; overflow-y: auto;">
                            @forelse($servicios as $servicio)
                                <div class="form-check mb-2">
                                    <input class="form-check-input especialidad-check" type="checkbox" name="especialidades[]" value="{{ $servicio->id_servicio }}" id="especialidad{{ $servicio->id_servicio }}">
                                    <label class="form-check-label" for="especialidad{{ $servicio->id_servicio }}">{{ $servicio->nombre }} <span class="text-muted">— Bs. {{ number_format($servicio->precio, 2) }}</span></label>
                                </div>
                            @empty
                                <span class="text-muted small">No hay servicios registrados.</span>
                            @endforelse
                        </div>
                        <div class="form-text">Seleccione al menos un servicio para cada administrador o empleado que atienda clientes.</div>
                    </div>


                </div>



                <!-- ==================================================
                     FOOTER
                     ================================================== -->

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary rounded-pill px-4"
                            data-bs-dismiss="modal">

                        <i class="fa-solid fa-xmark me-1"></i>

                        Cancelar

                    </button>


                    <button type="submit"
                            class="btn btn-danger rounded-pill px-4 fw-bold"
                            id="btnGuardar">

                        <i class="fa-solid fa-save me-1"></i>

                        Guardar

                    </button>

                </div>


            </form>

        </div>

    </div>

</div>

<div class="modal fade" id="modalInformacionUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><div class="modal-content rounded-4 border-0 shadow">
        <div class="modal-header bg-danger text-white"><h5 class="modal-title"><i class="fa-solid fa-circle-info me-2"></i>Información del usuario</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <div class="modal-body p-4" id="contenidoInformacionUsuario"></div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cerrar</button></div>
    </div></div>
</div>



@endsection



@section('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {


    /* =========================================================
       ELEMENTOS
       ========================================================= */

    const modalElemento =
        document.getElementById('modalUsuario');

    const modalInformacionElemento = document.getElementById('modalInformacionUsuario');
    const modalInformacion = new bootstrap.Modal(modalInformacionElemento);

    const formulario =
        document.getElementById('formUsuario');

    const btnNuevo =
        document.getElementById('btnNuevo');

    const btnGuardar =
        document.getElementById('btnGuardar');

    const campoUsername =
        document.getElementById('username');

    const errorUsername =
        document.getElementById('errorUsername');

    const campoRol = document.getElementById('rol');
    const grupoPago = document.getElementById('grupoPago');
    const campoPago = document.getElementById('pago');
    const grupoExperiencia = document.getElementById('grupoExperiencia');
    const campoExperiencia = document.getElementById('experiencia');
    const campoFoto = document.getElementById('foto');
    const vistaPreviaFoto = document.getElementById('vistaPreviaFoto');
    const grupoEspecialidades = document.getElementById('grupoEspecialidades');
    const especialidades = [...document.querySelectorAll('.especialidad-check')];

    function actualizarCampoPago() {
        const esEmpleado = campoRol.value === 'Empleado';
        const requiereEspecialidades = campoRol.value === 'Empleado' || campoRol.value === 'Administrador';
        grupoPago.classList.toggle('d-none', !esEmpleado);
        grupoExperiencia.classList.toggle('d-none', !esEmpleado);
        grupoEspecialidades.classList.toggle('d-none', !requiereEspecialidades);
        campoPago.required = esEmpleado;
        campoExperiencia.required = esEmpleado;
        especialidades.forEach(item => item.required = false);

        if (!requiereEspecialidades) {
            campoPago.value = '';
            campoExperiencia.value = '';
            especialidades.forEach(item => item.checked = false);
        }
    }

    campoRol.addEventListener('change', actualizarCampoPago);



    /* =========================================================
       SOLUCIÓN DEL MODAL

       Sacamos el modal de cualquier contenedor que pueda
       tener overflow, scroll o transform.

       Esto evita que aparezca cortado como en tu captura.
       ========================================================= */

    if (modalElemento.parentElement !== document.body) {

        document.body.appendChild(modalElemento);

    }
    if (modalInformacionElemento.parentElement !== document.body) {
        document.body.appendChild(modalInformacionElemento);
    }



    /* =========================================================
       TOKEN CSRF
       ========================================================= */

    const metaToken =
        document.querySelector(
            'meta[name="csrf-token"]'
        );

    const token =
        metaToken
            ? metaToken.getAttribute('content')
            : '';



    /* =========================================================
       RUTAS
       ========================================================= */

    const rutas = {

        listar:
            "{{ route('usuarios.listar') }}",

        registrar:
            "{{ route('usuarios.registrar') }}",

        obtener: function (id) {

            return "{{ url('/usuarios') }}/" + id;

        },

        actualizar: function (id) {

            return "{{ url('/usuarios') }}/" + id;

        },

        eliminar: function (id) {

            return "{{ url('/usuarios') }}/" + id;

        }

    };



    /* =========================================================
       MODAL BOOTSTRAP
       ========================================================= */

    const modalUsuario =
        new bootstrap.Modal(
            modalElemento,
            {
                backdrop: true,
                keyboard: true
            }
        );



    /* =========================================================
       CARGAR USUARIOS AL ENTRAR
       ========================================================= */

    listarUsuarios();



    /* =========================================================
       BOTÓN NUEVO USUARIO
       ========================================================= */

    btnNuevo.addEventListener(
        'click',
        function () {

            abrirNuevo();

        }
    );



    /* =========================================================
       VALIDACIÓN DEL NOMBRE DE USUARIO

       NO PERMITE:
       123
       maria123
       maria@
       maria#
       maria_123

       SÍ PERMITE:
       maria
       valentina
       Ana Maria
       María
       José
       Ñ
       ========================================================= */

    campoUsername.addEventListener(
        'input',
        function () {


            /*
             * Eliminamos cualquier carácter que no sea:
             *
             * A-Z
             * a-z
             * letras con tilde
             * Ñ
             * Ü
             * espacios
             */

            this.value =
                this.value.replace(
                    /[^A-Za-zÁÉÍÓÚáéíóúÑñÜü ]/g,
                    ''
                );


            /*
             * Eliminamos espacios repetidos.
             */

            this.value =
                this.value.replace(
                    /\s{2,}/g,
                    ' '
                );


            validarNombreUsuario();

        }
    );



    /* =========================================================
       VALIDAR NOMBRE
       ========================================================= */

    function validarNombreUsuario() {


        const valor =
            campoUsername.value.trim();


        /*
         * Si está vacío
         */

        if (valor === '') {

            campoUsername.classList.remove(
                'is-invalid'
            );

            errorUsername.style.display =
                'none';

            return false;

        }


        /*
         * Validación final.
         */

        const expresion =
            /^[A-Za-z0-9_.-]+$/;


        if (
            !expresion.test(valor)
        ) {

            campoUsername.classList.add(
                'is-invalid'
            );

            errorUsername.style.display =
                'block';

            return false;

        }


        /*
         * Validamos longitud mínima.
         */

        if (valor.length < 3) {

            campoUsername.classList.add(
                'is-invalid'
            );

            errorUsername.innerText =
                'El nombre debe tener al menos 3 caracteres.';

            errorUsername.style.display =
                'block';

            return false;

        }


        /*
         * Todo correcto.
         */

        campoUsername.classList.remove(
            'is-invalid'
        );

        campoUsername.classList.add(
            'is-valid'
        );

        errorUsername.style.display =
            'none';

        errorUsername.innerText =
            'Use letras, números, punto, guion o guion bajo.';


        return true;

    }



    /* =========================================================
       CUANDO SALE DEL CAMPO
       ========================================================= */

    campoUsername.addEventListener(
        'blur',
        function () {

            validarNombreUsuario();

        }
    );



    /* =========================================================
       SUBMIT DEL FORMULARIO
       ========================================================= */

    formulario.addEventListener(
        'submit',
        function (e) {

            guardarUsuario(e);

        }
    );



    /* =========================================================
       ESCAPAR HTML
       ========================================================= */

    function escaparHtml(valor) {

        return String(
            valor ?? ''
        ).replace(
            /[&<>'"]/g,
            function (caracter) {

                return {

                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    "'": '&#039;',
                    '"': '&quot;'

                }[caracter];

            }
        );

    }



    /* =========================================================
       LISTAR USUARIOS
       ========================================================= */

    function listarUsuarios() {


        fetch(
            rutas.listar,
            {

                method: 'GET',

                headers: {

                    'Accept':
                        'application/json'

                }

            }
        )

        .then(function (response) {


            if (!response.ok) {

                throw new Error(
                    'Error HTTP: ' +
                    response.status
                );

            }


            return response.json();

        })

        .then(function (respuesta) {


            /*
             * Tu Controller normalmente devuelve:
             *
             * {
             *    exito: true,
             *    datos: [...]
             * }
             */

            if (respuesta.exito) {

                pintarTabla(
                    respuesta.datos || []
                );

            }

            else {

                mostrarError(
                    respuesta.mensaje ||
                    'No se pudieron cargar los usuarios.'
                );

            }

        })

        .catch(function (error) {


            console.error(
                'Error al listar usuarios:',
                error
            );


            mostrarError(
                'No se pudieron cargar los usuarios.'
            );

        });

    }



    /* =========================================================
       PINTAR TABLA
       ========================================================= */

    function pintarTabla(usuarios) {


        const cuerpo =
            document.getElementById(
                'cuerpoTabla'
            );


        cuerpo.innerHTML = '';



        if (
            !usuarios ||
            usuarios.length === 0
        ) {


            cuerpo.innerHTML = `

                <tr>

                    <td colspan="5"
                        class="text-center text-muted py-4">

                        <i class="fa-solid fa-users-slash me-2"></i>

                        No hay usuarios registrados.

                    </td>

                </tr>

            `;


            return;

        }



        usuarios.forEach(
            function (usuario, index) {


                const id =
                    Number(
                        usuario.id_usuario
                    );



                /* =================================================
                   ROL
                   ================================================= */

                let claseRol =
                    'bg-secondary';


                if (
                    usuario.rol ===
                    'Administrador'
                ) {

                    claseRol =
                        'bg-danger';

                }

                else if (
                    usuario.rol ===
                    'Empleado'
                ) {

                    claseRol =
                        'bg-secondary';

                }

                else if (
                    usuario.rol ===
                    'Cliente'
                ) {

                    claseRol =
                        'bg-info text-dark';

                }



                /* =================================================
                   ESTADO
                   ================================================= */

                const estado =
                    String(
                        usuario.estado || ''
                    ).toLowerCase();


                const activo =
                    estado === 'activo';


                const claseEstado =
                    activo
                        ? 'bg-success'
                        : 'bg-warning text-dark';



                const textoEstado =
                    usuario.estado ||
                    'Sin estado';



                /* =================================================
                   FILA
                   ================================================= */

                cuerpo.innerHTML += `

                    <tr>

                        <td class="fw-bold">

                            ${index + 1}

                        </td>

                        <td>

                            <i class="fa-solid fa-user text-danger me-1"></i>

                            ${escaparHtml(
                                usuario.username
                            )}

                        </td>

                        <td>

                            <span class="badge ${claseRol} rounded-pill">

                                ${escaparHtml(
                                    usuario.rol
                                )}

                            </span>

                        </td>


                        <td>

                            <span class="badge ${claseEstado} rounded-pill">

                                ${escaparHtml(
                                    textoEstado
                                )}

                            </span>

                        </td>


                        <td class="text-center">

                            <button type="button"
                                    class="btn btn-outline-primary btn-sm rounded-pill px-2 me-1"
                                    onclick="verInformacionUsuario(${id})"
                                    title="Más información">
                                <i class="fa-solid fa-circle-info"></i>
                            </button>

                            <button type="button"
                                    class="btn btn-outline-warning btn-sm rounded-pill px-2 me-1"
                                    onclick="editarUsuario(${id})">
                                <i class="fa-solid fa-pen"></i>

                            </button>

                            <button type="button"
                                    class="btn btn-outline-danger btn-sm rounded-pill px-2"
                                    onclick="eliminarUsuario(${id})">
                                <i class="fa-solid fa-trash"></i>

                            </button>

                        </td>

                    </tr>

                `;

            }
        );

    }

    window.verInformacionUsuario = function (id) {
        const contenido = document.getElementById('contenidoInformacionUsuario');
        contenido.innerHTML = '<div class="text-center py-4"><span class="spinner-border text-danger"></span></div>';
        modalInformacion.show();

        fetch(rutas.obtener(id), { headers: { 'Accept': 'application/json' } })
            .then(response => response.ok ? response.json() : Promise.reject())
            .then(respuesta => {
                if (!respuesta.exito) throw new Error();
                const usuario = respuesta.datos;
                const servicios = (usuario.especialidades || []).map(servicio => escaparHtml(servicio.nombre)).join(', ') || 'Sin especialidades registradas';
                const foto = usuario.foto
                    ? `<img src="{{ asset('empleados') }}/${encodeURIComponent(usuario.foto)}" alt="Foto" class="rounded-circle border" width="90" height="90" style="object-fit:cover">`
                    : '<div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center" style="width:90px;height:90px"><i class="fa-solid fa-user fs-2"></i></div>';
                contenido.innerHTML = `<div class="text-center mb-4">${foto}<h5 class="mt-2 mb-0">${escaparHtml(usuario.username)}</h5><span class="badge bg-secondary mt-1">${escaparHtml(usuario.rol)}</span></div><dl class="row mb-0"><dt class="col-sm-4">Correo</dt><dd class="col-sm-8">${escaparHtml(usuario.email)}</dd><dt class="col-sm-4">Teléfono</dt><dd class="col-sm-8">${escaparHtml(usuario.telefono || 'No registrado')}</dd><dt class="col-sm-4">Número de emergencia</dt><dd class="col-sm-8">${escaparHtml(usuario.telefono_emergencia || 'No registrado')}</dd><dt class="col-sm-4">Estado</dt><dd class="col-sm-8">${escaparHtml(usuario.estado || 'Sin estado')}</dd><dt class="col-sm-4">Pago</dt><dd class="col-sm-8">${escaparHtml(usuario.pago || 'No aplica')}</dd><dt class="col-sm-4">Experiencia</dt><dd class="col-sm-8">${usuario.experiencia ? escaparHtml(usuario.experiencia) + '%' : 'No aplica'}</dd><dt class="col-sm-4">Servicios</dt><dd class="col-sm-8">${servicios}</dd></dl>`;
            })
            .catch(() => contenido.innerHTML = '<p class="text-center text-danger mb-0">No se pudo cargar la información del usuario.</p>');
    };



    /* =========================================================
       ABRIR NUEVO USUARIO
       ========================================================= */

    function abrirNuevo() {


        formulario.reset();



        document.getElementById(
            'id_usuario'
        ).value = '';



        document.getElementById(
            'tituloModal'
        ).innerText =
            'Nuevo Usuario';



        /*
         * La contraseña es obligatoria
         * cuando se registra.
         */

        document.getElementById(
            'password'
        ).required = true;

        campoFoto.required = true;
        document.getElementById('textoFotoObligatoria').classList.remove('d-none');
        vistaPreviaFoto.src = '';
        vistaPreviaFoto.classList.add('d-none');



        document.getElementById(
            'ayudaPass'
        ).innerText = '';



        actualizarCampoPago();



        campoUsername.classList.remove(
            'is-valid',
            'is-invalid'
        );


        errorUsername.style.display =
            'none';



        btnGuardar.disabled =
            false;



        btnGuardar.innerHTML = `

            <i class="fa-solid fa-save me-1"></i>

            Guardar

        `;



        modalUsuario.show();

    }



    /* =========================================================
       EDITAR USUARIO
       ========================================================= */

    window.editarUsuario =
        function (id) {


            if (!id) {

                alert(
                    'ID de usuario inválido.'
                );

                return;

            }



            fetch(
                rutas.obtener(id),
                {

                    method: 'GET',

                    headers: {

                        'Accept':
                            'application/json'

                    }

                }
            )

            .then(function (response) {


                if (!response.ok) {

                    throw new Error(
                        'Error HTTP: ' +
                        response.status
                    );

                }


                return response.json();

            })

            .then(function (respuesta) {


                if (!respuesta.exito) {

                    alert(
                        respuesta.mensaje ||
                        'No se pudo obtener el usuario.'
                    );

                    return;

                }



                const usuario =
                    respuesta.datos;



                /*
                 * ID
                 */

                document.getElementById(
                    'id_usuario'
                ).value =
                    usuario.id_usuario;



                /*
                 * Nombre
                 */

                document.getElementById(
                    'username'
                ).value =
                    usuario.username || '';



                /*
                 * Email
                 */

                document.getElementById(
                    'email'
                ).value =
                    usuario.email || '';

                document.getElementById('telefono').value = usuario.telefono || '';
                document.getElementById('telefono_emergencia').value = usuario.telefono_emergencia || '';



                /*
                 * Rol
                 */

                document.getElementById(
                    'rol'
                ).value =
                    usuario.rol || '';



                document.getElementById('pago').value = usuario.pago || '';
                document.getElementById('experiencia').value = usuario.experiencia || '';
                const serviciosSeleccionados = (usuario.especialidades || []).map(servicio => String(servicio.id_servicio));
                especialidades.forEach(item => item.checked = serviciosSeleccionados.includes(item.value));
                actualizarCampoPago();



                /*
                 * No mostramos la contraseña.
                 */

                document.getElementById(
                    'password'
                ).value = '';



                /*
                 * La contraseña NO es obligatoria
                 * al actualizar.
                 */

                document.getElementById(
                    'password'
                ).required =
                    false;

                campoFoto.required = false;
                document.getElementById('textoFotoObligatoria').classList.add('d-none');
                campoFoto.value = '';
                if (usuario.foto) {
                    vistaPreviaFoto.src = `{{ asset('empleados') }}/${encodeURIComponent(usuario.foto)}`;
                    vistaPreviaFoto.classList.remove('d-none');
                } else {
                    vistaPreviaFoto.src = '';
                    vistaPreviaFoto.classList.add('d-none');
                }



                document.getElementById(
                    'ayudaPass'
                ).innerText =
                    '(Dejar vacío para conservar la actual)';



                /*
                 * Título
                 */

                document.getElementById(
                    'tituloModal'
                ).innerText =
                    'Actualizar Usuario';



                /*
                 * Botón
                 */

                btnGuardar.disabled =
                    false;



                btnGuardar.innerHTML = `

                    <i class="fa-solid fa-save me-1"></i>

                    Actualizar

                `;



                /*
                 * Validar nombre cargado.
                 */

                validarNombreUsuario();



                /*
                 * Mostrar modal.
                 */

                modalUsuario.show();

            })

            .catch(function (error) {


                console.error(
                    'Error al obtener usuario:',
                    error
                );


                alert(
                    'No se pudo obtener la información del usuario.'
                );

            });

        };



    /* =========================================================
       GUARDAR

       SIRVE PARA:
       - INSERTAR
       - ACTUALIZAR
       ========================================================= */

    function guardarUsuario(e) {


        e.preventDefault();



        /*
         * Primero validamos el nombre.
         */

        if (
            !validarNombreUsuario()
        ) {

            alert(
                'Use letras, números, punto, guion o guion bajo.'
            );


            campoUsername.focus();


            return;

        }



        /*
         * Validar los demás campos HTML.
         */

        if (
            !formulario.reportValidity()
        ) {

            return;

        }

        if ((campoRol.value === 'Empleado' || campoRol.value === 'Administrador') && !especialidades.some(item => item.checked)) {
            alert('Seleccione al menos un servicio que realiza la persona.');
            return;
        }



        /*
         * Obtener ID.
         */

        const id =
            document.getElementById(
                'id_usuario'
            ).value;



        /*
         * Si existe ID = actualizar.
         *
         * Si no existe ID = insertar.
         */

        const actualizar =
            id !== '';



        /*
         * Datos del formulario.
         */

        const datos = new FormData(formulario);
        if (actualizar) datos.append('_method', 'PUT');



        /*
         * URL.
         */

        const url =
            actualizar
                ? rutas.actualizar(id)
                : rutas.registrar;



        /*
         * Método.
         */

        const metodo = 'POST';



        /*
         * Desactivar botón.
         */

        btnGuardar.disabled =
            true;



        btnGuardar.innerHTML = `

            <span class="spinner-border spinner-border-sm me-1"></span>

            Guardando...

        `;



        /*
         * ENVIAR AL CONTROLLER
         */

        fetch(
            url,
            {

                method: metodo,

                headers: {

                    'Accept':
                        'application/json',

                    'X-CSRF-TOKEN':
                        token

                },

                body: datos

            }
        )

        .then(function (response) {


            return response.json()
                .then(function (data) {

                    return {

                        ok:
                            response.ok,

                        data:
                            data

                    };

                });

        })

        .then(function (resultado) {


            const respuesta =
                resultado.data;



            /*
             * Si hubo error.
             */

            if (
                !resultado.ok ||
                !respuesta.exito
            ) {


                alert(
                    respuesta.mensaje ||
                    'No se pudo guardar el usuario.'
                );


                return;

            }



            /*
             * Éxito.
             */

            alert(
                respuesta.mensaje ||
                (
                    actualizar
                        ? 'Usuario actualizado correctamente.'
                        : 'Usuario registrado correctamente.'
                )
            );



            /*
             * Cerrar modal.
             */

            modalUsuario.hide();



            /*
             * Limpiar formulario.
             */

            formulario.reset();



            document.getElementById(
                'id_usuario'
            ).value = '';



            /*
             * Recargar tabla.
             */

            listarUsuarios();

        })

        .catch(function (error) {


            console.error(
                'Error al guardar:',
                error
            );


            alert(
                'Ocurrió un error al comunicarse con el servidor.'
            );

        })

        .finally(function () {


            btnGuardar.disabled =
                false;



            /*
             * Restaurar texto del botón.
             */

            btnGuardar.innerHTML = `

                <i class="fa-solid fa-save me-1"></i>

                ${actualizar
                    ? 'Actualizar'
                    : 'Guardar'}

            `;

        });

    }



    /* =========================================================
       ELIMINAR USUARIO
       ========================================================= */

    window.eliminarUsuario =
        function (id) {


            if (!id) {

                alert(
                    'ID de usuario inválido.'
                );

                return;

            }



            /*
             * Confirmación.
             */

            const confirmar =
                confirm(
                    '¿Está seguro de eliminar este usuario?'
                );



            if (!confirmar) {

                return;

            }



            /*
             * Enviar DELETE.
             */

            fetch(
                rutas.eliminar(id),
                {

                    method: 'DELETE',

                    headers: {

                        'Content-Type':
                            'application/json',

                        'Accept':
                            'application/json',

                        'X-CSRF-TOKEN':
                            token

                    }

                }
            )

            .then(function (response) {


                return response.json();

            })

            .then(function (respuesta) {


                alert(
                    respuesta.mensaje ||
                    'Operación realizada.'
                );



                if (
                    respuesta.exito
                ) {

                    listarUsuarios();

                }

            })

            .catch(function (error) {


                console.error(
                    'Error al eliminar:',
                    error
                );


                alert(
                    'No se pudo eliminar el usuario.'
                );

            });

        };



    /* =========================================================
       MOSTRAR ERROR EN TABLA
       ========================================================= */

    function mostrarError(mensaje) {


        document.getElementById(
            'cuerpoTabla'
        ).innerHTML = `

            <tr>

                <td colspan="10"
                    class="text-center text-danger py-4">

                    <i class="fa-solid fa-triangle-exclamation me-1"></i>

                    ${escaparHtml(mensaje)}

                </td>

            </tr>

        `;

    }



    /* =========================================================
       LIMPIAR VALIDACIONES AL CERRAR MODAL
       ========================================================= */

    modalElemento.addEventListener(
        'hidden.bs.modal',
        function () {


            campoUsername.classList.remove(
                'is-valid',
                'is-invalid'
            );


            errorUsername.style.display =
                'none';

        }
    );

    campoFoto.addEventListener('change', function () {
        const archivo = campoFoto.files[0];
        if (!archivo) return;
        vistaPreviaFoto.src = URL.createObjectURL(archivo);
        vistaPreviaFoto.classList.remove('d-none');
    });


});

</script>



<!-- ==========================================================
     ESTILOS
     ========================================================== -->

<style>


/* ==========================================================
   MODAL
   ========================================================== */

#modalUsuario {

    z-index: 9999 !important;

}



/* ==========================================================
   VENTANA DEL MODAL
   ========================================================== */

#modalUsuario .modal-dialog {

    width: 100%;

    max-width: 520px;

    margin: 1rem auto;

}



/* ==========================================================
   CONTENIDO DEL MODAL
   ========================================================== */

#modalUsuario .modal-content {

    max-height: calc(100vh - 2rem);

    overflow: hidden;

    border: none;

}



/* ==========================================================
   CUERPO DEL MODAL

   Permite desplazamiento cuando el formulario
   no entra completo en la pantalla.
   ========================================================== */

#modalUsuario .modal-body {

    overflow-y: auto;

    max-height: calc(100vh - 190px);

}



/* ==========================================================
   HEADER
   ========================================================== */

#modalUsuario .modal-header {

    background: #dc3545;

    color: white;

}



/* ==========================================================
   INPUTS
   ========================================================== */

#modalUsuario .form-control,
#modalUsuario .form-select {

    min-height: 44px;

    border-radius: 10px;

}



/* ==========================================================
   INPUTS AL SELECCIONAR
   ========================================================== */

#modalUsuario .form-control:focus,
#modalUsuario .form-select:focus {

    border-color: #dc3545;

    box-shadow:
        0 0 0 0.2rem
        rgba(220, 53, 69, .15);

}



/* ==========================================================
   INPUT CORRECTO
   ========================================================== */

#modalUsuario .form-control.is-valid {

    border-color: #198754;

}



/* ==========================================================
   INPUT INCORRECTO
   ========================================================== */

#modalUsuario .form-control.is-invalid {

    border-color: #dc3545;

}



/* ==========================================================
   FOOTER
   ========================================================== */

#modalUsuario .modal-footer {

    background: #fff;

}



/* ==========================================================
   BOTONES
   ========================================================== */

#modalUsuario .btn {

    min-height: 40px;

}



/* ==========================================================
   PANTALLAS PEQUEÑAS
   ========================================================== */

@media (max-width: 576px) {


    #modalUsuario .modal-dialog {

        margin: .5rem;

        max-width: none;

    }


    #modalUsuario .modal-body {

        max-height: calc(100vh - 180px);

    }


}

</style>

@endsection
