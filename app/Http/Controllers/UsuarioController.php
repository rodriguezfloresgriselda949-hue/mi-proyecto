<?php

namespace App\Http\Controllers;

use App\Models\UsuarioModel;
use App\Models\ClienteModel;
use App\Models\ServicioModel;
use App\Models\ReservasModel;
use App\Models\ProductoModel;
use App\Models\VentaModel;
use App\Models\ReservaProductoModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UsuarioController extends Controller
{
    // ==========================================================
    // LOGIN
    // ==========================================================

    public function index()
    {
        if (session()->has('idusuario')) {

            if ($this->esAdministrador(session('rol'))) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('empleado.dashboard');
        }

        return view('index');
    }


    public function volver()
    {
        if (!session()->has('idusuario')) {
            return redirect()->route('login');
        }

        if ($this->esAdministrador(session('rol'))) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('empleado.dashboard');
    }


    public function validar(Request $request)
    {
        $request->validate([
            'usu' => 'required|string|max:100',
            'pwd' => 'required|string|max:255',
        ], [
            'usu.required' => 'El campo usuario es obligatorio.',
            'pwd.required' => 'El campo contraseña es obligatorio.',
        ]);


        $usuario = UsuarioModel::where(
            'username',
            $request->usu
        )
        ->orWhere(
            'email',
            $request->usu
        )
        ->first();


        if (!$usuario) {
            return back()->with(
                'error',
                'El usuario ingresado no existe en el sistema.'
            );
        }


        if (strtolower($usuario->estado) !== 'activo') {
            return back()->with(
                'error',
                'Tu cuenta está inactiva. Contacta al administrador.'
            );
        }


        if (strtolower($usuario->rol) === 'cliente') {
            return back()->with(
                'error',
                'Las cuentas de cliente no tienen acceso al panel interno.'
            );
        }


        $passwordValido = false;


        if ($usuario->password === $request->pwd) {

            $passwordValido = true;

            $usuario->password =
                Hash::make($request->pwd);

            $usuario->save();

        } else {

            $info =
                password_get_info($usuario->password);

            if (!empty($info['algo'])) {

                if (
                    Hash::check(
                        $request->pwd,
                        $usuario->password
                    )
                ) {

                    $passwordValido = true;

                }

            }

        }


        if (!$passwordValido) {

            return back()->with(
                'error',
                'Contraseña incorrecta. Inténtalo de nuevo.'
            );

        }


        $request->session()->regenerate();


        session([
            'idusuario' => $usuario->id_usuario,
            'username'  => $usuario->username,
            'email'     => $usuario->email,
            'rol'       => $usuario->rol,
            'estado'    => $usuario->estado,
        ]);


        if ($this->esAdministrador($usuario->rol)) {

            return redirect()
                ->route('admin.dashboard')
                ->with(
                    'success',
                    '¡Bienvenido, ' .
                    $usuario->username .
                    '!'
                );

        }


        return redirect()
            ->route('empleado.dashboard')
            ->with(
                'success',
                '¡Bienvenido, ' .
                $usuario->username .
                '!'
            );
    }


    // ==========================================================
    // CERRAR SESIÓN
    // ==========================================================

    public function cerrar()
    {
        request()->session()->invalidate();

        request()->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with(
                'info',
                'Sesión cerrada correctamente.'
            );
    }


    // ==========================================================
    // DASHBOARD ADMINISTRADOR
    // ==========================================================

    public function adminDashboard()
    {
        ReservasController::cancelarReservasVencidas();
        $inicioMes = Carbon::now()->startOfMonth()->toDateString();
        $finMes = Carbon::now()->endOfMonth()->toDateString();

        // Ingresos de Ventas del mes y total recaudado
        $ingresosVentas = (float) VentaModel::whereBetween('fecha', [$inicioMes, $finMes])->sum('total');
        $totalVentasHistorico = (float) VentaModel::sum('total');

        // Ingresos de Servicios (reservas completadas) del mes y total histórico
        $ingresosReservas = (float) ReservasModel::whereBetween('fecha', [$inicioMes, $finMes])
            ->whereIn('estado', ['Completada', 'Finalizado'])
            ->sum(DB::raw('CAST(subtotal AS DECIMAL(10,2))'));
        $totalServiciosHistorico = (float) ReservasModel::whereIn('estado', ['Completada', 'Finalizado'])
            ->sum(DB::raw('CAST(subtotal AS DECIMAL(10,2))'));

        $totalIngresosMes = $ingresosVentas + $ingresosReservas;
        $totalRecaudadoGeneral = $totalVentasHistorico + $totalServiciosHistorico;

        $totalClientes = ClienteModel::count();

        // Reservas por día (citas programadas para la fecha actual)
        $reservasHoyTotal = ReservasModel::whereDate('fecha', Carbon::today())->count();
        $reservasHoyPendientes = ReservasModel::whereDate('fecha', Carbon::today())
            ->whereIn('estado', ['Pendiente', 'Reservado', 'En Atención'])
            ->count();
        $reservasHoyCompletadas = ReservasModel::whereDate('fecha', Carbon::today())
            ->whereIn('estado', ['Completada', 'Finalizado'])
            ->count();

        $alertasStock = ProductoModel::where('stock', '<=', 5)->count();

        $reservasProductosPendientes = ReservaProductoModel::with(['cliente', 'producto'])
            ->where('estado', 'reserva')
            ->orderBy('id_reserva_producto')
            ->get()
            ->groupBy(fn ($reserva) => $reserva->id_cliente . '|' . ($reserva->fecha_recojo ?: 'sin-fecha'))
            ->map(fn ($grupo) => $this->prepararGrupoReservaProducto($grupo))
            ->values();

        $ultimasReservas = ReservasModel::with(['cliente', 'servicio', 'usuario'])
            ->orderBy('id_reserva', 'desc')
            ->limit(5)
            ->get();

        $ultimasVentas = VentaModel::with(['cliente', 'usuario', 'detalles.producto'])
            ->orderBy('id_venta', 'desc')
            ->limit(5)
            ->get();

        $agendaHoy = ReservasModel::with(['cliente', 'usuario'])
            ->whereDate('fecha', Carbon::today())
            ->whereNotIn('estado', ['Cancelada', 'Cancelado'])
            ->orderBy('hora')->get()->groupBy(fn ($reserva) => substr((string) $reserva->getRawOriginal('hora'), 0, 5));
        $empleadosActivos = UsuarioModel::whereRaw('LOWER(rol) = ?', ['empleado'])
            ->whereRaw('LOWER(estado) = ?', ['activo'])
            ->orderBy('username')
            ->get(['id_usuario', 'username']);

        return view(
            'adm',
            compact(
                'totalIngresosMes',
                'ingresosVentas',
                'ingresosReservas',
                'totalRecaudadoGeneral',
                'totalVentasHistorico',
                'totalClientes',
                'reservasHoyTotal',
                'reservasHoyPendientes',
                'reservasHoyCompletadas',
                'alertasStock',
                'reservasProductosPendientes',
                'ultimasReservas',
                'ultimasVentas',
                'agendaHoy',
                'empleadosActivos'
            )
        );
    }

    private function prepararGrupoReservaProducto($reservas): array
    {
        $primera = $reservas->first();
        $items = $reservas->map(function ($reserva) {
            $precio = (float) $reserva->producto->precioventa;
            $cantidad = (int) $reserva->cantidad;
            return [
                'id' => $reserva->id_reserva_producto,
                'producto' => $reserva->producto->descripcion,
                'cantidad' => $cantidad,
                'precio' => $precio,
                'subtotal' => $precio * $cantidad,
            ];
        })->values();
        $total = $items->sum('subtotal');
        $lineas = $items->map(fn ($item) => '- ' . $item['producto'] . ': ' . $item['cantidad']
            . ' x Bs. ' . number_format($item['precio'], 2, '.', '')
            . ' = Bs. ' . number_format($item['subtotal'], 2, '.', ''))->implode("\n");
        $telefono = preg_replace('/\D+/', '', (string) $primera->cliente->telefono);
        if ($telefono !== '' && !str_starts_with($telefono, '591')) {
            $telefono = '591' . $telefono;
        }
        $mensaje = 'Hola ' . $primera->cliente->nombre . ', te compartimos el detalle de tus productos reservados:'
            . "\n" . $lineas . "\nTotal: Bs. " . number_format($total, 2, '.', '')
            . '. Fecha de recojo: ' . ($primera->fecha_recojo ?: 'por coordinar')
            . ($primera->hora_recojo ? ' a las ' . substr((string) $primera->hora_recojo, 0, 5) : '')
            . '. Por favor, confirma si estás de acuerdo.';

        return [
            'id_cliente' => $primera->id_cliente,
            'cliente' => trim($primera->cliente->nombre . ' ' . $primera->cliente->apellido),
            'telefono' => $primera->cliente->telefono,
            'fecha_recojo' => $primera->fecha_recojo,
            'hora_recojo' => $primera->hora_recojo,
            'horarios' => $reservas->pluck('hora_recojo')->filter()->map(fn ($hora) => substr((string) $hora, 0, 5))->unique()->values(),
            'items' => $items,
            'ids' => $reservas->pluck('id_reserva_producto')->values(),
            'total' => $total,
            'whatsapp_url' => $telefono !== '' ? 'https://wa.me/' . $telefono . '?text=' . urlencode($mensaje) : null,
        ];
    }

    /** Reservas de una fecha para el calendario del administrador. */
    public function reservasPorDiaCalendario(Request $request)
    {
        if ($request->filled('mes')) {
            $datos = $request->validate(['mes' => ['required', 'date_format:Y-m']]);
            $reservas = ReservasModel::selectRaw('DATE(fecha) as fecha, COUNT(*) as total')
                ->where('fecha', 'like', $datos['mes'] . '%')
                ->groupBy('fecha')->pluck('total', 'fecha');
            $ventas = VentaModel::selectRaw('DATE(fecha) as fecha, COUNT(*) as total')
                ->where('fecha', 'like', $datos['mes'] . '%')
                ->groupBy('fecha')->pluck('total', 'fecha');

            return response()->json($reservas->keys()->merge($ventas->keys())->unique()->mapWithKeys(
                fn ($fecha) => [$fecha => [
                    'reservas' => (int) ($reservas[$fecha] ?? 0),
                    'ventas' => (int) ($ventas[$fecha] ?? 0),
                ]]
            ));
        }
        $datos = $request->validate(['fecha' => ['required', 'date_format:Y-m-d']]);
        $reservas = ReservasModel::with(['usuario', 'cliente', 'servicio', 'servicios'])
            ->whereDate('fecha', $datos['fecha'])
            ->orderBy('hora')
            ->get()
            ->map(function (ReservasModel $reserva) {
                return [
                    'hora' => substr((string) $reserva->getRawOriginal('hora'), 0, 5),
                    'id_empleado' => $reserva->id_usuario,
                    'empleado' => $reserva->usuario->username ?? 'Sin asignar',
                    'cliente' => trim(($reserva->cliente->nombre ?? 'Cliente') . ' ' . ($reserva->cliente->apellido ?? '')),
                    'servicios' => $reserva->servicios->pluck('nombre')->values()->all() ?: [($reserva->servicio->nombre ?? 'Servicio')],
                    'estado' => $reserva->estado,
                    'total' => (float) $reserva->subtotal,
                ];
            })->values();

        $ventas = VentaModel::with(['cliente', 'detalles.producto'])
            ->whereDate('fecha', $datos['fecha'])
            ->orderBy('id_venta')
            ->get()
            ->map(function (VentaModel $venta) {
                return [
                    'id' => $venta->id_venta,
                    'hora' => substr((string) $venta->getRawOriginal('hora'), 0, 5),
                    'cliente' => trim(($venta->cliente->nombre ?? 'Cliente') . ' ' . ($venta->cliente->apellido ?? '')),
                    'productos' => $venta->detalles->map(fn ($detalle) => [
                        'nombre' => $detalle->producto->descripcion ?? 'Producto eliminado',
                        'cantidad' => (int) $detalle->cantidad,
                        'precio' => (float) $detalle->precioventa,
                        'subtotal' => (float) $detalle->cantidad * (float) $detalle->precioventa,
                    ])->values(),
                    'total' => (float) $venta->total,
                ];
            })->values();

        return response()->json(['reservas' => $reservas, 'ventas' => $ventas]);
    }


    // ==========================================================
    // DASHBOARD EMPLEADO
    // ==========================================================

    public function empleadoDashboard(Request $request)
    {
        $idUsuario = session('idusuario');

        // Reservas exclusivas del día actual
        $reservasHoy = ReservasModel::with(['cliente', 'servicio', 'servicios', 'usuario'])
            ->where('id_usuario', $idUsuario)
            ->whereDate('fecha', Carbon::today())
            ->orderBy('hora')
            ->get();

        // Rango de fechas para el historial (por defecto mes actual)
        $fechaDesde = $request->input('fecha_desde', Carbon::now()->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', Carbon::now()->endOfMonth()->toDateString());
        if ($fechaDesde > $fechaHasta) {
            [$fechaDesde, $fechaHasta] = [$fechaHasta, $fechaDesde];
        }

        $reservasPeriodo = ReservasModel::with(['cliente', 'servicio', 'servicios', 'usuario'])
            ->where('id_usuario', $idUsuario)
            ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->get();

        // Separar reservas por estado
        $reservasPendientes = $reservasPeriodo->whereIn('estado', ['Pendiente', 'Reservado', 'En Atención', 'En Atencion']);
        $reservasCompletadas = $reservasPeriodo->whereIn('estado', ['Completada', 'Finalizado']);
        $reservasCanceladas = $reservasPeriodo->whereIn('estado', ['Cancelada', 'Cancelado']);
        $totalServiciosPeriodo = (float) $reservasCompletadas->sum(fn ($reserva) => (float) $reserva->subtotal);
        $ventasPeriodo = VentaModel::with('cliente')
            ->where('id_usuario', $idUsuario)
            ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
            ->orderBy('fecha', 'desc')
            ->orderBy('id_venta', 'desc')
            ->get();
        $totalVentasPeriodo = (float) $ventasPeriodo->sum('total');

        return view(
            'empleado',
            compact(
                'reservasHoy',
                'reservasPeriodo',
                'reservasPendientes',
                'reservasCompletadas',
                'reservasCanceladas',
                'totalServiciosPeriodo',
                'ventasPeriodo',
                'totalVentasPeriodo',
                'fechaDesde',
                'fechaHasta'
            )
        );
    }

    // ==========================================================
    // PERFIL DEL EMPLEADO
    // ==========================================================

    /** Muestra únicamente los datos propios que el empleado puede administrar. */
    public function perfilEmpleado()
    {
        $usuario = UsuarioModel::findOrFail(session('idusuario'));

        return view('perfil_empleado', compact('usuario'));
    }

    /** Actualiza datos personales sin permitir cambios de rol, pago, experiencia o especialidades. */
    public function actualizarPerfilEmpleado(Request $request)
    {
        $usuario = UsuarioModel::findOrFail(session('idusuario'));

        $datos = $request->validate([
            'username' => [
                'required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z0-9_.-]+$/',
                'unique:usuario,username,' . $usuario->id_usuario . ',id_usuario',
            ],
            'email' => ['required', 'email:rfc', 'max:100', 'unique:usuario,email,' . $usuario->id_usuario . ',id_usuario'],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+()\-\s]+$/'],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.unique' => 'El nombre de usuario ya está en uso.',
            'username.regex' => 'El usuario solo puede contener letras, números, punto, guion y guion bajo.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingrese un correo electrónico válido.',
            'email.unique' => 'El correo electrónico ya está registrado.',
            'telefono.regex' => 'El teléfono solo puede contener números, +, paréntesis o guiones.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
        ]);

        $usuario->username = trim($datos['username']);
        $usuario->email = trim($datos['email']);
        $usuario->telefono = filled($datos['telefono'] ?? null) ? trim($datos['telefono']) : null;

        if (!empty($datos['password'])) {
            $usuario->password = Hash::make($datos['password']);
        }

        if ($request->hasFile('foto')) {
            $fotoAnterior = $usuario->foto;
            $usuario->foto = $this->guardarFoto($request);
            $this->eliminarFoto($fotoAnterior);
        }

        $usuario->save();
        session(['username' => $usuario->username, 'email' => $usuario->email]);

        return redirect()->route('empleado.perfil')->with('success', 'Tu perfil fue actualizado correctamente.');
    }


    private function esAdministrador(?string $rol): bool
    {
        return in_array(strtolower(trim((string) $rol)), ['administrador', 'admin'], true);
    }

    // ==========================================================
    // CAMBIAR ESTADO RESERVA
    // ==========================================================

    public function cambiarEstadoReserva(
        Request $request,
        $id
    ) {

        $reserva =
            ReservasModel::findOrFail($id);


        if (
            strtolower(session('rol'))
            !== 'administrador'
            &&
            (int)$reserva->id_usuario
            !==
            (int)session('idusuario')
        ) {

            abort(
                403,
                'Solo puede actualizar reservas que tiene asignadas.'
            );
        }


        $request->validate([
            'estado' => [
                'required',
                'in:En Atención,Completada'
            ]
        ]);


        $nuevoEstado =
            $request->input('estado');


        $reserva->estado =
            $nuevoEstado;

        $reserva->save();


        return back()->with(
            'success',
            'Estado de la reserva actualizado a ' .
            $nuevoEstado
        );
    }


    // ==========================================================
    // CRUD USUARIOS
    // ==========================================================

    // Mostrar vista

    public function usuarios()
    {
        $servicios = ServicioModel::orderBy('nombre')->get();
        return view('usu_frm', compact('servicios'));
    }


    // ==========================================================
    // LISTAR
    // ==========================================================

    public function listar()
    {
        try {

            $usuarios =
                UsuarioModel::with('especialidades:id_servicio,nombre')->orderBy('username')->get();


            return response()->json([
                'exito' => true,
                'datos' => $usuarios
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'exito' => false,
                'mensaje' =>
                    'Error al listar usuarios: ' .
                    $e->getMessage()
            ], 500);
        }
    }


    // ==========================================================
    // OBTENER UNO
    // ==========================================================

    public function obtener($id)
    {
        try {

            $usuario =
                UsuarioModel::with('especialidades:id_servicio,nombre')->where(
                    'id_usuario',
                    $id
                )->first();


            if (!$usuario) {

                return response()->json([
                    'exito' => false,
                    'mensaje' =>
                        'Usuario no encontrado'
                ], 404);
            }


            return response()->json([
                'exito' => true,
                'datos' => $usuario
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'exito' => false,
                'mensaje' =>
                    'Error al obtener usuario: ' .
                    $e->getMessage()
            ], 500);
        }
    }


    // ==========================================================
    // INSERTAR USUARIO
    // ==========================================================

    public function registrar(Request $request)
    {
        try {

            $validator =
                Validator::make(
                    $request->all(),
                    [
                        'username' => [
                            'required',
                            'string',
                            'min:3',
                            'max:50',
                            'regex:/^[A-Za-z0-9_.-]+$/',
                            'unique:usuario,username'
                        ],

                        'email' => [
                            'required',
                            'email:rfc',
                            'max:100',
                            'unique:usuario,email'
                        ],

                        'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+()\-\s]+$/'],
                        'telefono_emergencia' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+()\-\s]+$/'],

                        'password' => [
                            'required',
                            'string',
                            'min:8',
                            'max:255'
                        ],

                        'rol' => [
                            'required',
                            'in:Administrador,Empleado,Cliente'
                        ],

                        'pago' => [
                            'required_if:rol,Empleado',
                            'nullable',
                            'in:Mensual,Quincenal'
                        ],

                        'experiencia' => ['required_if:rol,Empleado', 'nullable', 'integer', 'in:30,35,40,50'],
                        'especialidades' => ['required_unless:rol,Cliente', 'nullable', 'array', 'min:1'],
                        'especialidades.*' => ['integer', 'distinct', 'exists:servicios,id_servicio'],

                        'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

                    ],
                    [
                        'username.required' =>
                            'El nombre de usuario es obligatorio.',

                        'username.unique' =>
                            'El nombre de usuario ya está en uso.',

                        'username.regex' =>
                            'El usuario solo puede contener letras, números, punto, guion y guion bajo.',

                        'email.required' =>
                            'El correo electrónico es obligatorio.',

                        'email.email' =>
                            'Ingrese un correo electrónico válido.',

                        'email.unique' =>
                            'El correo electrónico ya está registrado.',

                        'password.required' =>
                            'La contraseña es obligatoria.',

                        'password.min' =>
                            'La contraseña debe tener al menos 8 caracteres.',

                        'rol.required' =>
                            'Debe seleccionar un rol.',

                    ]
                );


            if ($validator->fails()) {

                return response()->json([
                    'exito' => false,
                    'mensaje' =>
                        $validator
                        ->errors()
                        ->first()
                ], 422);
            }


            $usuario =
                UsuarioModel::create([
                    'username' =>
                        $request->username,

                    'email' =>
                        $request->email,

                    'telefono' => $request->filled('telefono') ? trim($request->telefono) : null,
                    'telefono_emergencia' => $request->filled('telefono_emergencia') ? trim($request->telefono_emergencia) : null,

                    'password' =>
                        Hash::make(
                            $request->password
                        ),

                    'rol' =>
                        $request->rol,

                    // El tipo de pago solo corresponde al personal.
                    'pago' => $request->rol === 'Empleado'
                        ? $request->pago
                        : null,

                    'experiencia' => $request->rol === 'Empleado' ? $request->experiencia : null,

                    'foto' => $this->guardarFoto($request),

                    // Toda cuenta nueva inicia habilitada; su estado puede cambiarse al editarla.
                    'estado' => 'activo',
                ]);

            $usuario->especialidades()->sync($request->rol !== 'Cliente' ? $request->input('especialidades', []) : []);


            return response()->json([
                'exito' => true,
                'mensaje' =>
                    'Usuario registrado correctamente',
                'datos' => $usuario
            ], 201);


        } catch (\Exception $e) {

            return response()->json([
                'exito' => false,
                'mensaje' =>
                    'Error al registrar usuario: ' .
                    $e->getMessage()
            ], 500);
        }
    }


    // ==========================================================
    // ACTUALIZAR USUARIO
    // ==========================================================

    public function actualizar(
        Request $request,
        $id
    ) {

        try {

            $usuario =
                UsuarioModel::where(
                    'id_usuario',
                    $id
                )->first();


            if (!$usuario) {

                return response()->json([
                    'exito' => false,
                    'mensaje' =>
                        'Usuario no encontrado'
                ], 404);
            }


            $validator =
                Validator::make(
                    $request->all(),
                    [
                        'username' => [
                            'required',
                            'string',
                            'min:3',
                            'max:50',
                            'regex:/^[A-Za-z0-9_.-]+$/',
                            'unique:usuario,username,' .
                            $id .
                            ',id_usuario'
                        ],

                        'email' => [
                            'required',
                            'email:rfc',
                            'max:100',
                            'unique:usuario,email,' .
                            $id .
                            ',id_usuario'
                        ],

                        'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+()\-\s]+$/'],
                        'telefono_emergencia' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+()\-\s]+$/'],

                        'password' => [
                            'nullable',
                            'string',
                            'min:8',
                            'max:255'
                        ],

                        'rol' => [
                            'required',
                            'in:Administrador,Empleado,Cliente'
                        ],

                        'pago' => [
                            'required_if:rol,Empleado',
                            'nullable',
                            'in:Mensual,Quincenal'
                        ],

                        'experiencia' => ['required_if:rol,Empleado', 'nullable', 'integer', 'in:30,35,40,50'],
                        'especialidades' => ['required_unless:rol,Cliente', 'nullable', 'array', 'min:1'],
                        'especialidades.*' => ['integer', 'distinct', 'exists:servicios,id_servicio'],

                        'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                    ],
                    [
                        'username.required' =>
                            'El nombre de usuario es obligatorio.',

                        'username.unique' =>
                            'El nombre de usuario ya está en uso.',

                        'email.required' =>
                            'El correo electrónico es obligatorio.',

                        'email.email' =>
                            'Ingrese un correo electrónico válido.',

                        'email.unique' =>
                            'El correo electrónico ya está registrado.',

                        'password.min' =>
                            'La nueva contraseña debe tener al menos 8 caracteres.',

                        'rol.required' =>
                            'Debe seleccionar un rol.',

                    ]
                );


            if ($validator->fails()) {

                return response()->json([
                    'exito' => false,
                    'mensaje' =>
                        $validator
                        ->errors()
                        ->first()
                ], 422);
            }


            // Actualizar datos

            $usuario->username =
                $request->username;

            $usuario->email =
                $request->email;

            $usuario->telefono = $request->filled('telefono') ? trim($request->telefono) : null;
            $usuario->telefono_emergencia = $request->filled('telefono_emergencia') ? trim($request->telefono_emergencia) : null;

            $usuario->rol =
                $request->rol;

            $usuario->pago = $request->rol === 'Empleado'
                ? $request->pago
                : null;

            $usuario->experiencia = $request->rol === 'Empleado'
                ? $request->experiencia
                : null;

            // El estado ya no se administra desde el formulario: toda cuenta queda activa.
            $usuario->estado = 'activo';


            // Cambiar contraseña solamente
            // si el usuario escribió una nueva

            if ($request->filled('password')) {

                $usuario->password =
                    Hash::make(
                        $request->password
                    );
            }

            if ($request->hasFile('foto')) {
                $fotoAnterior = $usuario->foto;
                $usuario->foto = $this->guardarFoto($request);
                $usuario->save();
                $this->eliminarFoto($fotoAnterior);
            }


            $usuario->save();
            $usuario->especialidades()->sync($request->rol !== 'Cliente' ? $request->input('especialidades', []) : []);


            return response()->json([
                'exito' => true,
                'mensaje' =>
                    'Usuario actualizado correctamente',
                'datos' => $usuario
            ]);


        } catch (\Exception $e) {

            return response()->json([
                'exito' => false,
                'mensaje' =>
                    'Error al actualizar usuario: ' .
                    $e->getMessage()
            ], 500);
        }
    }


    // ==========================================================
    // ELIMINAR
    // ==========================================================

    public function eliminar($id)
    {
        try {

            $usuario =
                UsuarioModel::where(
                    'id_usuario',
                    $id
                )->first();


            if (!$usuario) {

                return response()->json([
                    'exito' => false,
                    'mensaje' =>
                        'Usuario no encontrado'
                ], 404);
            }


            if (
                (int)$usuario->id_usuario
                ===
                (int)session('idusuario')
            ) {

                return response()->json([
                    'exito' => false,
                    'mensaje' =>
                        'No puedes eliminar tu propio usuario en sesión.'
                ]);
            }


            $foto = $usuario->foto;
            $usuario->delete();
            $this->eliminarFoto($foto);


            return response()->json([
                'exito' => true,
                'mensaje' =>
                    'Usuario eliminado correctamente'
            ]);


        } catch (\Illuminate\Database\QueryException $e) {

            return response()->json([
                'exito' => false,
                'mensaje' =>
                    'No se puede eliminar el usuario porque tiene registros o reservas asociadas.'
            ], 409);

        } catch (\Exception $e) {

            return response()->json([
                'exito' => false,
                'mensaje' =>
                    'Error al eliminar usuario: ' .
                    $e->getMessage()
            ], 500);
        }
    }

    /** Guarda la imagen con un nombre no predecible fuera del nombre original. */
    private function guardarFoto(Request $request): string
    {
        $directorio = public_path('empleados');
        File::ensureDirectoryExists($directorio);

        $archivo = $request->file('foto');
        $nombre = Str::uuid() . '.' . $archivo->getClientOriginalExtension();
        $archivo->move($directorio, $nombre);

        return $nombre;
    }

    private function eliminarFoto(?string $foto): void
    {
        if ($foto) {
            File::delete(public_path('empleados/' . basename($foto)));
        }
    }
}
