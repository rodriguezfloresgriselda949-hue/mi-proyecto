<?php

namespace App\Http\Controllers;

use App\Models\ServicioModel;
use App\Models\ReservasModel;
use App\Models\PromocionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ServicioController extends Controller
{
    private function reglasServicio(): array
    {
        return [
            'nombre' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s.,&()\-]+$/u'],
            'descripcion' => ['nullable', 'string', 'max:100'],
            'precio' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    // Mostrar formulario para crear servicio
    public function create()
    {
        return view('servicio_frm');
    }


    // Guardar servicio
    public function store(Request $request)
    {
        $datos = $request->validate($this->reglasServicio());
        $imagen = $this->guardarImagen($request);


        ServicioModel::create([
            'nombre' => Str::upper(trim($datos['nombre'])),
            'descripcion' => isset($datos['descripcion']) ? Str::lower(trim($datos['descripcion'])) : null,
            'precio' => $datos['precio'],
            'imagen' => $imagen,
        ]);


        return redirect()
            ->route('servicio.lista')
            ->with('success', 'Servicio registrado correctamente.');
    }


    // Mostrar lista de servicios
    public function index()
    {
        $servicios = ServicioModel::orderBy('nombre')->get();
        $promociones = PromocionModel::with('servicios')->orderByDesc('fecha_hora_inicio')->get();

        return view('servicio_lista', compact('servicios', 'promociones'));
    }

    /**
     * Devuelve el resumen de solicitudes de un servicio para el modal de información.
     */
    public function informacion($id)
    {
        $servicio = ServicioModel::findOrFail($id);

        // La tabla pivote permite contabilizar correctamente las reservas que tienen
        // más de un servicio seleccionado.
        $reservas = ReservasModel::query()
            ->select('reservas.*')
            ->join('reserva_servicio', 'reserva_servicio.id_reserva', '=', 'reservas.id_reserva')
            ->where('reserva_servicio.id_servicio', $servicio->id_servicio)
            ->with('cliente')
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->get();

        $estadosFinalizados = ['Completada', 'Finalizado'];
        $estadosCancelados = ['Cancelada', 'Cancelado'];
        $total = $reservas->count();
        $finalizadas = $reservas->whereIn('estado', $estadosFinalizados)->count();
        $canceladas = $reservas->whereIn('estado', $estadosCancelados)->count();

        $clientes = $reservas
            ->filter(fn ($reserva) => $reserva->cliente)
            ->groupBy('id_cliente')
            ->map(function ($solicitudes) use ($estadosFinalizados) {
                $cliente = $solicitudes->first()->cliente;
                $ultimaSolicitud = $solicitudes->sortByDesc(fn ($reserva) => ($reserva->fecha?->format('Y-m-d') ?? '') . ' ' . ($reserva->hora?->format('H:i:s') ?? ''))->first();

                return [
                    'nombre' => trim($cliente->nombre . ' ' . $cliente->apellido),
                    'telefono' => $cliente->telefono,
                    'solicitudes' => $solicitudes->count(),
                    'finalizadas' => $solicitudes->whereIn('estado', $estadosFinalizados)->count(),
                    'ultima_solicitud' => $ultimaSolicitud->fecha?->format('d/m/Y'),
                ];
            })
            ->sortByDesc('solicitudes')
            ->values();

        $primeraSolicitud = $reservas->sortBy(fn ($reserva) => ($reserva->fecha?->format('Y-m-d') ?? '') . ' ' . ($reserva->hora?->format('H:i:s') ?? ''))->first();
        $ultimaSolicitud = $reservas->first();

        return response()->json([
            'servicio' => $servicio->nombre,
            'resumen' => [
                'solicitudes' => $total,
                'clientes_unicos' => $clientes->count(),
                'finalizadas' => $finalizadas,
                'pendientes' => $total - $finalizadas - $canceladas,
                'canceladas' => $canceladas,
                'tasa_finalizacion' => $total ? round(($finalizadas / $total) * 100) : 0,
                'primera_solicitud' => $primeraSolicitud?->fecha?->format('d/m/Y'),
                'ultima_solicitud' => $ultimaSolicitud?->fecha?->format('d/m/Y'),
            ],
            'cliente_frecuente' => $clientes->first(),
            'clientes' => $clientes,
        ]);
    }


    // Mostrar formulario para editar
    public function edit($id)
    {
        $servicio = ServicioModel::findOrFail($id);

        return view('servicio_editar', compact('servicio'));
    }


    // Actualizar servicio
    public function update(Request $request, $id)
    {
        $datos = $request->validate($this->reglasServicio());


        $servicio = ServicioModel::findOrFail($id);
        $imagen = $this->guardarImagen($request);


        $servicio->update([
            'nombre' => Str::upper(trim($datos['nombre'])),
            'descripcion' => isset($datos['descripcion']) ? Str::lower(trim($datos['descripcion'])) : null,
            'precio' => $datos['precio'],
        ]);

        if ($imagen) {
            if ($servicio->imagen && File::exists(public_path('servicios/' . $servicio->imagen))) {
                File::delete(public_path('servicios/' . $servicio->imagen));
            }
            $servicio->update(['imagen' => $imagen]);
        }


        return redirect()
            ->route('servicio.lista')
            ->with('success', 'Servicio actualizado correctamente.');
    }


    // Eliminar servicio
    public function destroy($id)
    {
        $servicio = ServicioModel::findOrFail($id);

        if ($servicio->imagen && File::exists(public_path('servicios/' . $servicio->imagen))) {
            File::delete(public_path('servicios/' . $servicio->imagen));
        }

        $servicio->delete();

        return redirect()
            ->route('servicio.lista')
            ->with('success', 'Servicio eliminado correctamente.');
    }

    private function guardarImagen(Request $request): ?string
    {
        if (!$request->hasFile('imagen')) {
            return null;
        }

        $directorio = public_path('servicios');
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }

        $archivo = $request->file('imagen');
        $nombre = Str::uuid() . '.' . $archivo->getClientOriginalExtension();
        $archivo->move($directorio, $nombre);

        return $nombre;
    }
}
