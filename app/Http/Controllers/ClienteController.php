<?php

namespace App\Http\Controllers;

use App\Models\ClienteModel;
use App\Models\ReservasModel;
use App\Models\VentaModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClienteController extends Controller
{
    // Mostrar formulario
    public function create()
    {
        return view('cliente_frm');
    }

    // Guardar cliente
    public function store(Request $request)
    {
        $request->validate([
            'nombre'   => ['required', 'string', 'min:2', 'max:50'],
            'apellido' => ['required', 'string', 'min:2', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email'    => ['nullable', 'email', 'max:50'],
        ], [
            'nombre.required'   => 'El nombre del cliente es obligatorio.',
            'nombre.min'        => 'El nombre debe tener al menos 2 caracteres.',
            'apellido.required' => 'El apellido del cliente es obligatorio.',
            'apellido.min'      => 'El apellido debe tener al menos 2 caracteres.',
            'email.email'       => 'Debe proporcionar un correo electrónico válido.',
            'email.max'         => 'El correo no puede exceder los 50 caracteres.',
        ]);

        $cliente = ClienteModel::create([
            'nombre'   => Str::upper(trim($request->nombre)),
            'apellido' => Str::upper(trim($request->apellido)),
            'telefono' => $request->filled('telefono') ? trim($request->telefono) : null,
            'email'    => $request->filled('email') ? trim($request->email) : null,
        ]);

        if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success'    => true,
                'id_cliente' => $cliente->id_cliente,
                'nombre'     => $cliente->nombre,
                'apellido'   => $cliente->apellido,
                'telefono'   => $cliente->telefono ?? 'Sin teléfono'
            ], 201);
        }

        return redirect()
            ->route('cliente.lista')
            ->with('success', 'Cliente registrado correctamente.');
    }

    // Mostrar lista
    public function index()
    {
        $clientes = ClienteModel::withCount([
            'reservas',
            'ventas',
            'reservasProductos',
        ])->orderBy('nombre')->orderBy('apellido')->get();
        return view('cliente_lista', compact('clientes'));
    }

    /** Devuelve el historial de servicios y compras del cliente para el modal administrativo. */
    public function historial($id)
    {
        $cliente = ClienteModel::findOrFail($id);

        $servicios = ReservasModel::with(['servicio', 'servicios', 'usuario'])
            ->where('id_cliente', $cliente->id_cliente)
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->get()
            ->map(function (ReservasModel $reserva) {
                $nombresServicios = $reserva->servicios->pluck('nombre')->values();
                if ($nombresServicios->isEmpty() && $reserva->servicio) {
                    $nombresServicios->push($reserva->servicio->nombre);
                }

                return [
                    'fecha' => optional($reserva->fecha)->format('d/m/Y'),
                    'hora' => substr((string) $reserva->getRawOriginal('hora'), 0, 5),
                    'servicios' => $nombresServicios,
                    'empleado' => $reserva->usuario->username ?? 'Sin asignar',
                    'estado' => $reserva->estado,
                    'total' => (float) $reserva->subtotal,
                ];
            });

        $compras = VentaModel::with('detalles.producto')
            ->where('id_cliente', $cliente->id_cliente)
            ->orderByDesc('fecha')
            ->orderByDesc('id_venta')
            ->get()
            ->map(function (VentaModel $venta) {
                return [
                    'fecha' => optional($venta->fecha)->format('d/m/Y'),
                    'productos' => $venta->detalles->map(fn ($detalle) => [
                        'nombre' => $detalle->producto->descripcion ?? 'Producto eliminado',
                        'cantidad' => (int) $detalle->cantidad,
                    ])->values(),
                    'total' => (float) $venta->total,
                ];
            });

        return response()->json([
            'cliente' => trim($cliente->nombre . ' ' . $cliente->apellido),
            'servicios' => $servicios,
            'compras' => $compras,
        ]);
    }

    // Mostrar formulario de edición
    public function edit($id)
    {
        $cliente = ClienteModel::findOrFail($id);
        return view('cliente_editar', compact('cliente'));
    }

    // Actualizar cliente
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre'   => ['required', 'string', 'min:2', 'max:50'],
            'apellido' => ['required', 'string', 'min:2', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email'    => ['nullable', 'email', 'max:50'],
        ], [
            'nombre.required'   => 'El nombre del cliente es obligatorio.',
            'nombre.min'        => 'El nombre debe tener al menos 2 caracteres.',
            'apellido.required' => 'El apellido del cliente es obligatorio.',
            'apellido.min'      => 'El apellido debe tener al menos 2 caracteres.',
            'email.email'       => 'Debe proporcionar un correo electrónico válido.',
            'email.max'         => 'El correo no puede exceder los 50 caracteres.',
        ]);

        $cliente = ClienteModel::findOrFail($id);

        $cliente->update([
            'nombre'   => Str::upper(trim($request->nombre)),
            'apellido' => Str::upper(trim($request->apellido)),
            'telefono' => $request->filled('telefono') ? trim($request->telefono) : null,
            'email'    => $request->filled('email') ? trim($request->email) : null,
        ]);

        return redirect()
            ->route('cliente.lista')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    // Eliminar cliente
    public function destroy($id)
    {
        $cliente = ClienteModel::findOrFail($id);
        $tieneRegistros = $cliente->reservas()->exists()
            || $cliente->ventas()->exists()
            || $cliente->reservasProductos()->exists();

        if ($tieneRegistros) {
            return redirect()
                ->route('cliente.lista')
                ->with('error', 'No se puede eliminar el cliente porque tiene reservas, ventas u otros registros asociados.');
        }

        try {
            $cliente->delete();

            return redirect()
                ->route('cliente.lista')
                ->with('success', 'Cliente eliminado correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()
                ->route('cliente.lista')
                ->with('error', 'No se puede eliminar el cliente porque tiene reservas o ventas asociadas.');
        }
    }
}
