<?php

namespace App\Http\Controllers;

use App\Models\ReservasModel;
use App\Models\ClienteModel;
use App\Models\ServicioModel;
use App\Models\UsuarioModel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReservasController extends Controller
{
    private const ESTADOS = ['Reservado', 'Pendiente', 'En Atención', 'En Atencion', 'Completada', 'Finalizado', 'Cancelada', 'Cancelado'];

    private function reglasReserva(bool $esNueva = false): array
    {
        $reglaFecha = ['required', 'date_format:Y-m-d'];
        if ($esNueva) {
            $reglaFecha[] = 'after_or_equal:today';
        }

        return [
            'id_cliente' => ['required', 'integer', 'exists:clientes,id_cliente'],
            'id_servicios' => ['required', 'array', 'min:1'],
            'id_servicios.*' => ['integer', 'distinct', 'exists:servicios,id_servicio'],
            'id_usuario' => ['nullable', 'integer', Rule::exists('usuario', 'id_usuario')->where(fn ($query) => $query->whereRaw('LOWER(estado) = ?', ['activo'])->whereRaw('LOWER(rol) IN (?, ?)', ['administrador', 'empleado']))],
            'fecha' => $reglaFecha,
            'hora' => ['required', 'date_format:H:i', Rule::in(array_map(fn ($hora) => sprintf('%02d:00', $hora), range(9, 19)))],
            'adelanto' => ['required', 'numeric', 'min:0'],
            'estado' => ['required', 'in:' . implode(',', self::ESTADOS)],
        ];
    }

    // Mostrar lista de reservas
    public function index(Request $request)
    {
        self::cancelarReservasVencidas();
        $estadoFiltro = $request->get('estado', 'pendientes');
        $fechaDesde = $request->get('desde', Carbon::today()->toDateString());
        $fechaHasta = $request->get('hasta', Carbon::today()->toDateString());
        if ($fechaDesde > $fechaHasta) {
            [$fechaDesde, $fechaHasta] = [$fechaHasta, $fechaDesde];
        }
        $query = ReservasModel::with([
            'cliente',
            'servicio', 'servicios',
            'usuario'
        ]);

        if (strtolower(session('rol')) !== 'administrador') {
            $query->where('id_usuario', session('idusuario'));
        }

        $query->whereBetween('fecha', [$fechaDesde, $fechaHasta]);

        if ($estadoFiltro === 'pendientes') {
            $query->whereIn('estado', ['Pendiente', 'Reservado', 'En Atención', 'En Atencion']);
        } elseif ($estadoFiltro === 'completadas') {
            $query->whereIn('estado', ['Completada', 'Finalizado']);
        } elseif ($estadoFiltro === 'canceladas') {
            $query->whereIn('estado', ['Cancelada', 'Cancelado']);
        }

        $reservas = $query->orderBy('fecha', 'desc')->orderBy('hora', 'desc')->get();

        $usuariosTraspaso = UsuarioModel::with('especialidades:id_servicio,nombre')
            ->whereRaw('LOWER(estado) = ?', ['activo'])
            ->whereRaw('LOWER(rol) IN (?, ?)', ['administrador', 'empleado'])
            ->orderBy('username')
            ->get();

        return view('reservas_lista', compact(
            'reservas',
            'estadoFiltro',
            'fechaDesde',
            'fechaHasta',
            'usuariosTraspaso'
        ));
    }

    public function comprobante($id)
    {
        $reserva = ReservasModel::with(['cliente', 'servicio', 'servicios', 'usuario'])->findOrFail($id);
        $this->asegurarAccesoReserva($reserva);

        return view('reservas.comprobante', compact('reserva'));
    }

    // Mostrar formulario
    public function create(Request $request)
    {
        $clientes = ClienteModel::orderBy('nombre')->orderBy('apellido')->get();
        $servicios = ServicioModel::orderBy('nombre')->get();
        $usuarios = UsuarioModel::with('especialidades:id_servicio,nombre')
            ->whereRaw('LOWER(estado) = ?', ['activo'])
            ->whereRaw('LOWER(rol) IN (?, ?)', ['administrador', 'empleado']);
        if (strtolower(session('rol')) !== 'administrador') {
            $usuarios->where('id_usuario', session('idusuario'));
        }
        $usuarios = $usuarios->get();
        $fechaSolicitada = $request->query('fecha');
        $fechaPrefill = Carbon::today()->toDateString();
        if (is_string($fechaSolicitada) && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $fechaSolicitada, $partesFecha)
            && checkdate((int) $partesFecha[2], (int) $partesFecha[3], (int) $partesFecha[1])) {
            $fechaPrefill = $fechaSolicitada;
        }
        $horaSolicitada = $request->query('hora');
        $horaPrefill = is_string($horaSolicitada) && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $horaSolicitada)
            ? $horaSolicitada
            : '';
        $usuarioSolicitado = (int) $request->query('id_usuario');
        $idUsuarioPrefill = $usuarios->firstWhere('id_usuario', $usuarioSolicitado)?->id_usuario
            ?? $usuarios->firstWhere('id_usuario', (int) session('idusuario'))?->id_usuario
            ?? $usuarios->first()?->id_usuario;
        $personalFijo = ($request->boolean('fijar_personal') || old('fijar_personal'))
            && $usuarios->contains(fn ($usuario) => (int) $usuario->id_usuario === $usuarioSolicitado);

        return view('reservas_frm', compact('clientes', 'servicios', 'usuarios', 'fechaPrefill', 'horaPrefill', 'idUsuarioPrefill', 'personalFijo'));
    }

    public function horasDisponibles(Request $request)
    {
        $datos = $request->validate([
            'fecha' => ['required', 'date_format:Y-m-d'],
            'id_usuario' => ['required', 'integer', Rule::exists('usuario', 'id_usuario')->where(fn ($query) => $query->whereRaw('LOWER(estado) = ?', ['activo'])->whereRaw('LOWER(rol) IN (?, ?)', ['administrador', 'empleado']))],
            'id_reserva' => ['nullable', 'integer', 'exists:reservas,id_reserva'],
        ]);
        $idUsuario = strtolower(session('rol')) === 'administrador'
            ? (int) $datos['id_usuario']
            : (int) session('idusuario');
        if ($idUsuario !== (int) $datos['id_usuario']) abort(403);

        $idReservaActual = null;
        if (!empty($datos['id_reserva'])) {
            $reservaActual = ReservasModel::findOrFail($datos['id_reserva']);
            if (strtolower(session('rol')) !== 'administrador' && (int) $reservaActual->id_usuario !== (int) session('idusuario')) {
                abort(403);
            }
            if ((int) $reservaActual->id_usuario === $idUsuario && $reservaActual->fecha->format('Y-m-d') === $datos['fecha']) {
                $idReservaActual = $reservaActual->id_reserva;
            }
        }

        $horasOcupadas = ReservasModel::where('id_usuario', $idUsuario)
            ->whereDate('fecha', $datos['fecha'])
            ->whereNotIn('estado', ['Cancelada', 'Cancelado'])
            ->when($idReservaActual, fn ($query) => $query->where('id_reserva', '!=', $idReservaActual))
            ->get(['hora'])
            ->map(fn ($reserva) => substr((string) $reserva->getRawOriginal('hora'), 0, 2))
            ->unique()
            ->values();

        $horas = collect(range(9, 19))->map(fn ($hora) => sprintf('%02d:00', $hora));
        return response()->json([
            'horas_disponibles' => $horas->reject(fn ($hora) => $horasOcupadas->contains(substr($hora, 0, 2)))->values(),
        ]);
    }

    // Guardar reserva
    public function store(Request $request)
    {
        $datos = $request->validate($this->reglasReserva(true));
        $servicios = ServicioModel::whereIn('id_servicio', $datos['id_servicios'])->get();
        $idUsuario = strtolower(session('rol')) === 'administrador' && !empty($datos['id_usuario'])
            ? $datos['id_usuario']
            : session('idusuario');
        $this->verificarEspecialidades($idUsuario, $servicios->pluck('id_servicio')->all());
        $this->verificarDisponibilidad($idUsuario, $datos['fecha'], $datos['hora']);

        $reserva = ReservasModel::create([
            'id_cliente'  => $datos['id_cliente'],
            // Se conserva el primer servicio para los registros históricos.
            'id_servicio' => $servicios->first()->id_servicio,
            'id_usuario'  => $idUsuario,
            'fecha'       => $datos['fecha'],
            'hora'        => $datos['hora'],
            'subtotal'    => $servicios->sum('precio'),
            'adelanto'    => $datos['adelanto'],
            'estado'      => $datos['estado'],
        ]);
        $reserva->servicios()->sync($servicios->pluck('id_servicio'));

        return redirect()->route('reservas.index')->with('success', 'Reserva registrada correctamente.');
    }

    // Mostrar formulario de edición
    public function edit($id)
    {
        $reserva = ReservasModel::with('servicios')->findOrFail($id);
        $this->asegurarAccesoReserva($reserva);
        $clientes = ClienteModel::orderBy('nombre')->orderBy('apellido')->get();
        $servicios = ServicioModel::orderBy('nombre')->get();
        $usuarios = UsuarioModel::with('especialidades:id_servicio,nombre')
            ->whereRaw('LOWER(estado) = ?', ['activo'])
            ->whereRaw('LOWER(rol) IN (?, ?)', ['administrador', 'empleado']);
        if (strtolower(session('rol')) !== 'administrador') {
            $usuarios->where('id_usuario', session('idusuario'));
        }
        $usuarios = $usuarios->get();

        return view('reservas_editar', compact('reserva', 'clientes', 'servicios', 'usuarios'));
    }

    // Actualizar reserva
    public function update(Request $request, $id)
    {
        $datos = $request->validate($this->reglasReserva());

        $reserva = ReservasModel::findOrFail($id);
        $this->asegurarAccesoReserva($reserva);
        $servicios = ServicioModel::whereIn('id_servicio', $datos['id_servicios'])->get();
        $idUsuario = strtolower(session('rol')) === 'administrador' && !empty($datos['id_usuario'])
            ? $datos['id_usuario']
            : $reserva->id_usuario;
        $this->verificarEspecialidades($idUsuario, $servicios->pluck('id_servicio')->all());
        $this->verificarDisponibilidad($idUsuario, $datos['fecha'], $datos['hora'], $reserva->id_reserva);

        $reserva->update([
            'id_cliente'  => $datos['id_cliente'],
            'id_servicio' => $servicios->first()->id_servicio,
            'id_usuario'  => $idUsuario,
            'fecha'       => $datos['fecha'],
            'hora'        => $datos['hora'],
            'subtotal'    => $servicios->sum('precio'),
            'adelanto'    => $datos['adelanto'],
            'estado'      => $datos['estado'],
        ]);
        $reserva->servicios()->sync($servicios->pluck('id_servicio'));

        return redirect()->route('reservas.index')->with('success', 'Reserva actualizada correctamente.');
    }

    // Eliminar reserva
    public function destroy($id)
    {
        $reserva = ReservasModel::findOrFail($id);
        $this->asegurarAccesoReserva($reserva);
        $reserva->delete();

        return redirect()->route('reservas.index')->with('success', 'Reserva eliminada correctamente.');
    }

    public function traspasarServicio(Request $request, $id)
    {
        $datos = $request->validate([
            'id_servicio' => ['required', 'integer', 'exists:servicios,id_servicio'],
            'id_usuario_destino' => ['required', 'integer', Rule::exists('usuario', 'id_usuario')->where(fn ($query) => $query->whereRaw('LOWER(estado) = ?', ['activo'])->whereRaw('LOWER(rol) IN (?, ?)', ['administrador', 'empleado']))],
        ]);

        DB::transaction(function () use ($id, $datos) {
            $reserva = ReservasModel::with(['servicios', 'servicio'])
                ->lockForUpdate()
                ->findOrFail($id);
            $this->asegurarAccesoReserva($reserva);

            if (in_array($reserva->estado, ['Completada', 'Finalizado', 'Cancelada', 'Cancelado'], true)) {
                throw ValidationException::withMessages(['traspaso' => 'Solo se pueden traspasar servicios de una reserva pendiente o en atención.']);
            }

            $serviciosReserva = $reserva->servicios;
            if ($serviciosReserva->isEmpty() && $reserva->servicio) {
                $serviciosReserva = collect([$reserva->servicio]);
            }
            $servicio = $serviciosReserva->firstWhere('id_servicio', (int) $datos['id_servicio']);
            if (!$servicio) {
                throw ValidationException::withMessages(['id_servicio' => 'El servicio seleccionado no pertenece a esta reserva.']);
            }

            if ($serviciosReserva->count() === 1) {
                throw ValidationException::withMessages([
                    'id_servicio' => 'Para conservar el adelanto en la reserva original, debe quedar al menos otro servicio en ella.',
                ]);
            }

            $idDestino = (int) $datos['id_usuario_destino'];
            if ($idDestino === (int) $reserva->id_usuario) {
                throw ValidationException::withMessages(['id_usuario_destino' => 'Seleccione un empleado distinto al asignado actualmente.']);
            }

            $this->verificarEspecialidades($idDestino, [(int) $servicio->id_servicio]);
            $momentoTraspaso = Carbon::now();
            $this->verificarDisponibilidad(
                $idDestino,
                $momentoTraspaso->toDateString(),
                $momentoTraspaso->format('H:i')
            );

            $subtotalOriginal = (float) $reserva->subtotal;
            $precioTotalServicios = (float) $serviciosReserva->sum(fn ($item) => (float) $item->precio);
            $proporcionServicio = $precioTotalServicios > 0
                ? (float) $servicio->precio / $precioTotalServicios
                : 1 / $serviciosReserva->count();
            $subtotalTraspasado = round($subtotalOriginal * $proporcionServicio, 2);
            $subtotalRestante = round($subtotalOriginal - $subtotalTraspasado, 2);
            $serviciosRestantes = $serviciosReserva
                ->reject(fn ($item) => (int) $item->id_servicio === (int) $servicio->id_servicio)
                ->values();

            $nuevaReserva = ReservasModel::create([
                'id_cliente' => $reserva->id_cliente,
                'id_servicio' => $servicio->id_servicio,
                'id_usuario' => $idDestino,
                'fecha' => $momentoTraspaso->toDateString(),
                'hora' => $momentoTraspaso->format('H:i:s'),
                'subtotal' => $subtotalTraspasado,
                'adelanto' => 0,
                'estado' => 'Reservado',
            ]);
            $nuevaReserva->servicios()->sync([(int) $servicio->id_servicio]);

            $reserva->update([
                'id_servicio' => $serviciosRestantes->first()->id_servicio,
                'subtotal' => $subtotalRestante,
            ]);
            $reserva->servicios()->sync($serviciosRestantes->pluck('id_servicio')->all());
        });

        return back()->with('success', 'El servicio se traspasó correctamente al otro empleado.');
    }

    private function verificarDisponibilidad(int $idUsuario, string $fecha, string $hora, ?int $idReservaActual = null): void
    {
        $horaInicio = sprintf('%02d:00:00', (int) substr($hora, 0, 2));
        $horaFin = sprintf('%02d:00:00', (int) substr($hora, 0, 2) + 1);
        $consulta = ReservasModel::where('id_usuario', $idUsuario)
            ->whereDate('fecha', $fecha)
            ->whereTime('hora', '>=', $horaInicio)
            ->whereTime('hora', '<', $horaFin)
            ->whereNotIn('estado', ['Cancelada', 'Cancelado']);

        if ($idReservaActual) {
            $consulta->where('id_reserva', '!=', $idReservaActual);
        }

        if ($consulta->exists()) {
            throw ValidationException::withMessages([
                'hora' => 'El personal seleccionado ya tiene una reserva asignada para esa fecha y hora.',
            ]);
        }
    }

    private function verificarEspecialidades(int $idUsuario, array $servicios): void
    {
        $usuario = UsuarioModel::with('especialidades:id_servicio,nombre')->find($idUsuario);
        $cantidad = $usuario
            ? $usuario->especialidades->pluck('id_servicio')->intersect($servicios)->count()
            : 0;

        if ((int) $cantidad !== count($servicios)) {
            throw ValidationException::withMessages([
                'id_servicios' => 'El personal seleccionado no está habilitado para realizar todos los servicios elegidos.',
            ]);
        }
    }

    private function asegurarAccesoReserva(ReservasModel $reserva): void
    {
        if (strtolower(session('rol')) !== 'administrador' && (int) $reserva->id_usuario !== (int) session('idusuario')) {
            abort(403, 'Solo puede gestionar reservas que tiene asignadas.');
        }
    }

    /** Cancela citas no atendidas cinco minutos después de su hora programada. */
    public static function cancelarReservasVencidas(): int
    {
        $ahora = Carbon::now();
        return ReservasModel::whereIn('estado', ['Pendiente', 'Reservado'])
            ->where(function ($query) use ($ahora) {
                $query->whereDate('fecha', '<', $ahora->toDateString())
                    ->orWhere(function ($hoy) use ($ahora) {
                        $hoy->whereDate('fecha', $ahora->toDateString())
                            ->whereTime('hora', '<=', $ahora->copy()->subMinutes(5)->format('H:i:s'));
                    });
            })
            ->update(['estado' => 'Cancelado']);
    }
}
