<?php

namespace App\Http\Controllers;

use App\Models\ClienteModel;
use App\Models\DetalleVentaModel;
use App\Models\ProductoModel;
use App\Models\ReservaProductoModel;
use App\Models\VentaModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservaProductoController extends Controller
{
    private const ESTADOS = ['reserva', 'confirmado', 'pagado', 'entregado'];

    /** Guarda una reserva pública y crea el cliente únicamente si aún no existe. */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'productos' => ['required', 'array', 'min:1'],
            'productos.*.id_producto' => ['required', 'integer', 'distinct', 'exists:productos,id_producto'],
            'productos.*.cantidad' => ['required', 'integer', 'min:1'],
            'metodo_pago' => ['required', 'in:qr,efectivo'],
            'fecha_recojo' => ['required', 'date', 'after_or_equal:today'],
            'hora_recojo' => ['required', 'date_format:H:i'],
            'id_cliente' => ['nullable', 'integer', 'exists:clientes,id_cliente'],
            'nombre' => ['required_without:id_cliente', 'nullable', 'string', 'max:50'],
            'apellido' => ['required_without:id_cliente', 'nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:50'],
        ]);

        try {
            $idCliente = null;
            DB::transaction(function () use ($datos, &$idCliente) {
                $idCliente = $datos['id_cliente'] ?? ClienteModel::create([
                    'nombre' => trim($datos['nombre']),
                    'apellido' => trim($datos['apellido']),
                    'telefono' => $datos['telefono'] ?? null,
                    'email' => $datos['email'] ?? null,
                ])->id_cliente;

                foreach ($datos['productos'] as $item) {
                    $producto = ProductoModel::whereKey($item['id_producto'])->lockForUpdate()->firstOrFail();
                    if ($producto->stock < $item['cantidad']) {
                        throw new \RuntimeException("Solo hay {$producto->stock} unidad(es) disponible(s) de {$producto->descripcion}.");
                    }

                    ReservaProductoModel::create([
                        'id_cliente' => $idCliente,
                        'id_producto' => $producto->id_producto,
                        'cantidad' => $item['cantidad'],
                        'metodo_pago' => $datos['metodo_pago'],
                        'estado' => 'reserva',
                        'fecha' => now()->toDateString(),
                        'fecha_recojo' => $datos['fecha_recojo'],
                        'hora_recojo' => $datos['hora_recojo'],
                    ]);
                }
            });

            $cliente = ClienteModel::findOrFail($idCliente);
            $lineas = collect($datos['productos'])->map(function ($item) {
                $producto = ProductoModel::find($item['id_producto']);
                return '- ' . ($producto->descripcion ?? 'Producto') . ' × ' . $item['cantidad'];
            })->implode("\n");
            $mensaje = "Hola, soy {$cliente->nombre} {$cliente->apellido}. Acabo de enviar una reserva para recoger el {$datos['fecha_recojo']} a las {$datos['hora_recojo']}.\n{$lineas}\nMétodo de pago: {$datos['metodo_pago']}. Por favor, confirmen mi reserva y el precio. Gracias.";
            $whatsappUrl = 'https://wa.me/59174156411?text=' . urlencode($mensaje);

            return redirect()->route('inicio')->with('success', 'Tu reserva fue registrada. WhatsApp abrirá el mensaje para solicitar la confirmación.')->with('whatsapp_url', $whatsappUrl);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'No fue posible registrar la reserva. Intenta nuevamente.')->withInput();
        }
    }

    /** Búsqueda para completar el formulario de reserva con un cliente existente. */
    public function buscarClientes(Request $request)
    {
        $buscar = trim((string) $request->query('buscar', ''));
        if (mb_strlen($buscar) < 2) {
            return response()->json([]);
        }

        return response()->json(
            ClienteModel::query()
                ->where(function ($query) use ($buscar) {
                    $query->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('apellido', 'like', "%{$buscar}%");
                })
                ->orderBy('nombre')
                ->limit(10)
                ->get(['id_cliente', 'nombre', 'apellido', 'telefono', 'email'])
        );
    }

    public function index(Request $request)
    {
        $filtros = $request->validate([
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date'],
            'estado' => ['nullable', 'in:' . implode(',', self::ESTADOS)],
        ]);
        if (!empty($filtros['fecha_desde']) && !empty($filtros['fecha_hasta']) && $filtros['fecha_desde'] > $filtros['fecha_hasta']) {
            [$filtros['fecha_desde'], $filtros['fecha_hasta']] = [$filtros['fecha_hasta'], $filtros['fecha_desde']];
        }

        $reservas = ReservaProductoModel::with(['cliente', 'producto', 'venta'])
            ->orderByDesc('id_reserva_producto')
            ->get();

        $grupos = $reservas
            ->groupBy(fn ($reserva) => $reserva->id_cliente . '|' . ($reserva->fecha_recojo ?: 'sin-fecha'))
            ->map(function ($items) {
                $primera = $items->first();
                $detalle = $items->map(function ($reserva) {
                    $precio = (float) $reserva->producto->precioventa;
                    $cantidad = (int) $reserva->cantidad;
                    return [
                        'id' => $reserva->id_reserva_producto,
                        'producto' => $reserva->producto->descripcion,
                        'cantidad' => $cantidad,
                        'precio' => $precio,
                        'subtotal' => $precio * $cantidad,
                        'estado' => $reserva->estado,
                        'id_venta' => $reserva->id_venta,
                    ];
                })->values();
                $estados = $items->pluck('estado')->unique()->values();
                $total = $detalle->sum('subtotal');
                $lineas = $detalle->map(fn ($item) => '- ' . $item['producto'] . ': ' . $item['cantidad']
                    . ' x Bs. ' . number_format($item['precio'], 2, '.', '')
                    . ' = Bs. ' . number_format($item['subtotal'], 2, '.', ''))->implode("\n");
                $telefono = preg_replace('/\D+/', '', (string) $primera->cliente->telefono);
                if ($telefono !== '' && !str_starts_with($telefono, '591')) $telefono = '591' . $telefono;
                $mensaje = 'Hola ' . $primera->cliente->nombre . ', te compartimos la confirmación y el detalle de tus productos reservados:'
                    . "\n" . $lineas . "\nTotal: Bs. " . number_format($total, 2, '.', '') . '.';

                return [
                    'cliente' => trim($primera->cliente->nombre . ' ' . $primera->cliente->apellido),
                    'telefono' => $primera->cliente->telefono,
                    'fecha' => $primera->fecha,
                    'fechas' => $items->pluck('fecha')->map(fn ($fecha) => \Carbon\Carbon::parse($fecha)->toDateString())->unique()->values(),
                    'fecha_recojo' => $primera->fecha_recojo,
                    'horarios' => $items->pluck('hora_recojo')->filter()->map(fn ($hora) => substr((string) $hora, 0, 5))->unique()->values(),
                    'items' => $detalle,
                    'ids' => $items->pluck('id_reserva_producto')->values(),
                    'total' => $total,
                    'estado' => $estados->count() === 1 ? $estados->first() : 'mixto',
                    'estados' => $estados,
                    'incluye_entregado' => $estados->contains('entregado'),
                    'whatsapp_url' => $telefono !== '' ? 'https://wa.me/' . $telefono . '?text=' . urlencode($mensaje) : null,
                ];
            })->filter(function ($grupo) use ($filtros) {
                if (!empty($filtros['estado']) && !$grupo['estados']->contains($filtros['estado'])) return false;
                return $grupo['fechas']->contains(function ($fecha) use ($filtros) {
                    return (empty($filtros['fecha_desde']) || $fecha >= $filtros['fecha_desde'])
                        && (empty($filtros['fecha_hasta']) || $fecha <= $filtros['fecha_hasta']);
                });
            })->values();

        return view('reservas_productos.index', compact('grupos', 'filtros'));
    }

    public function actualizarEstadoGrupo(Request $request)
    {
        $datos = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct', 'exists:reserva_producto,id_reserva_producto'],
            'estado' => ['required', 'in:' . implode(',', self::ESTADOS)],
        ]);

        try {
            DB::transaction(function () use ($datos) {
                $reservas = ReservaProductoModel::whereIn('id_reserva_producto', $datos['ids'])
                    ->orderBy('id_reserva_producto')->lockForUpdate()->get();
                if ($reservas->count() !== count($datos['ids'])) {
                    throw new \RuntimeException('No se encontró el grupo completo. Actualiza la página e inténtalo nuevamente.');
                }

                $clavesGrupo = $reservas->map(fn ($r) => $r->id_cliente . '|' . ($r->fecha_recojo ?: 'sin-fecha'))->unique();
                if ($clavesGrupo->count() !== 1) {
                    throw new \RuntimeException('Las reservas deben pertenecer al mismo cliente y fecha de recojo.');
                }
                $primera = $reservas->first();
                $queryGrupo = ReservaProductoModel::where('id_cliente', $primera->id_cliente)
                    ->when($primera->fecha_recojo, fn ($q) => $q->whereDate('fecha_recojo', $primera->fecha_recojo), fn ($q) => $q->whereNull('fecha_recojo'))
                    ->orderBy('id_reserva_producto')->lockForUpdate();
                $idsGrupo = $queryGrupo->pluck('id_reserva_producto')->map(fn ($id) => (int) $id)->all();
                $idsEnviados = collect($datos['ids'])->map(fn ($id) => (int) $id)->sort()->values()->all();
                if ($idsGrupo !== $idsEnviados) {
                    throw new \RuntimeException('El grupo está incompleto. Actualiza la página antes de cambiar su estado.');
                }
                if ($reservas->contains(fn ($r) => $r->estado === 'entregado')) {
                    throw new \RuntimeException('El grupo incluye productos entregados y no se puede cambiar en bloque.');
                }

                if ($datos['estado'] !== 'entregado') {
                    $reservas->each(fn ($reserva) => $reserva->update(['estado' => $datos['estado']]));
                    return;
                }

                $productos = ProductoModel::whereIn('id_producto', $reservas->pluck('id_producto')->unique())
                    ->orderBy('id_producto')->lockForUpdate()->get()->keyBy('id_producto');
                $cantidadesPorProducto = $reservas->groupBy('id_producto')->map(fn ($items) => $items->sum('cantidad'));
                foreach ($cantidadesPorProducto as $idProducto => $cantidad) {
                    $producto = $productos->get($idProducto);
                    if (!$producto || $producto->stock < $cantidad) {
                        throw new \RuntimeException('No hay stock suficiente para entregar todos los productos del grupo.');
                    }
                }

                foreach ($reservas as $reserva) {
                    $producto = $productos->get($reserva->id_producto);
                    $venta = VentaModel::create([
                        'id_cliente' => $reserva->id_cliente,
                        'id_usuario' => session('idusuario'),
                        'fecha' => now()->toDateString(),
                        'total' => $producto->precioventa * $reserva->cantidad,
                    ]);
                    DetalleVentaModel::create([
                        'id_venta' => $venta->id_venta,
                        'id_producto' => $producto->id_producto,
                        'cantidad' => $reserva->cantidad,
                        'precioventa' => $producto->precioventa,
                    ]);
                    $reserva->update(['estado' => 'entregado', 'id_venta' => $venta->id_venta]);
                }
                foreach ($cantidadesPorProducto as $idProducto => $cantidad) {
                    $productos->get($idProducto)->decrement('stock', $cantidad);
                }
            });

            $respuesta = redirect()->route('reservas.productos.index', $request->only(['fecha_desde', 'fecha_hasta', 'estado']))
                ->with('success', 'El estado se actualizó para todo el grupo de productos reservados.');
            if ($datos['estado'] === 'confirmado') {
                $reservas = ReservaProductoModel::with(['cliente', 'producto'])->whereIn('id_reserva_producto', $datos['ids'])->get();
                $primera = $reservas->first();
                $telefono = preg_replace('/\D+/', '', (string) $primera->cliente->telefono);
                if ($telefono !== '') {
                    if (!str_starts_with($telefono, '591')) $telefono = '591' . $telefono;
                    $lineas = $reservas->map(function ($reserva) {
                        $precio = (float) $reserva->producto->precioventa;
                        $subtotal = $precio * (int) $reserva->cantidad;
                        return '- ' . $reserva->producto->descripcion . ': ' . $reserva->cantidad . ' x Bs. '
                            . number_format($precio, 2, '.', '') . ' = Bs. ' . number_format($subtotal, 2, '.', '');
                    })->implode("\n");
                    $total = $reservas->sum(fn ($r) => (float) $r->producto->precioventa * (int) $r->cantidad);
                    $mensaje = 'Hola ' . $primera->cliente->nombre . ', confirmamos tu reserva:' . "\n" . $lineas
                        . "\nTotal: Bs. " . number_format($total, 2, '.', '') . '.';
                    $respuesta->with('whatsapp_url', 'https://wa.me/' . $telefono . '?text=' . urlencode($mensaje));
                } else {
                    $respuesta->with('warning', 'El grupo quedó confirmado, pero el cliente no tiene teléfono registrado para WhatsApp.');
                }
            }

            return $respuesta;
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'No se pudo actualizar el estado del grupo.');
        }
    }

    public function confirmarGrupo(Request $request)
    {
        $datos = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct', 'exists:reserva_producto,id_reserva_producto'],
        ]);

        try {
            DB::transaction(function () use ($datos) {
                $reservas = ReservaProductoModel::whereIn('id_reserva_producto', $datos['ids'])
                    ->lockForUpdate()
                    ->get();

                if ($reservas->count() !== count($datos['ids']) || $reservas->contains(fn ($r) => $r->estado !== 'reserva')) {
                    throw new \RuntimeException('El grupo cambió o alguna reserva ya no está pendiente. Actualiza el panel e inténtalo nuevamente.');
                }

                $grupos = $reservas->map(fn ($r) => $r->id_cliente . '|' . ($r->fecha_recojo ?: 'sin-fecha'))->unique();
                if ($grupos->count() !== 1) {
                    throw new \RuntimeException('Las reservas seleccionadas no corresponden al mismo cliente y fecha de recojo.');
                }

                $primera = $reservas->first();
                $pendientesDelGrupo = ReservaProductoModel::where('estado', 'reserva')
                    ->where('id_cliente', $primera->id_cliente)
                    ->when($primera->fecha_recojo, fn ($q) => $q->whereDate('fecha_recojo', $primera->fecha_recojo), fn ($q) => $q->whereNull('fecha_recojo'))
                    ->orderBy('id_reserva_producto')
                    ->pluck('id_reserva_producto')
                    ->map(fn ($id) => (int) $id)
                    ->all();
                $idsEnviados = collect($datos['ids'])->map(fn ($id) => (int) $id)->sort()->values()->all();
                if ($pendientesDelGrupo !== $idsEnviados) {
                    throw new \RuntimeException('El grupo está incompleto. Actualiza el panel antes de confirmarlo.');
                }

                $reservas->each(fn ($reserva) => $reserva->update(['estado' => 'confirmado']));
            });

            $reservas = ReservaProductoModel::with(['cliente', 'producto'])
                ->whereIn('id_reserva_producto', $datos['ids'])
                ->orderBy('id_reserva_producto')
                ->get();
            $cliente = $reservas->first()->cliente;
            $lineas = $reservas->map(function ($reserva) {
                $precio = (float) $reserva->producto->precioventa;
                $subtotal = $precio * (int) $reserva->cantidad;
                return '- ' . $reserva->producto->descripcion . ': ' . $reserva->cantidad
                    . ' x Bs. ' . number_format($precio, 2, '.', '')
                    . ' = Bs. ' . number_format($subtotal, 2, '.', '');
            });
            $total = $reservas->sum(fn ($reserva) => (float) $reserva->producto->precioventa * (int) $reserva->cantidad);
            $mensaje = 'Hola ' . $cliente->nombre . ', confirmamos tu reserva:' . "\n"
                . $lineas->implode("\n") . "\nTotal: Bs. " . number_format($total, 2, '.', '') . '.';
            $telefono = preg_replace('/\D+/', '', (string) $cliente->telefono);
            $redirect = redirect()->route('admin.dashboard')
                ->with('success', 'Todas las reservas del grupo se marcaron como confirmadas.');

            if ($telefono !== '') {
                if (!str_starts_with($telefono, '591')) $telefono = '591' . $telefono;
                $redirect->with('whatsapp_url', 'https://wa.me/' . $telefono . '?text=' . urlencode($mensaje));
            } else {
                $redirect->with('warning', 'El grupo fue confirmado, pero el cliente no tiene teléfono para WhatsApp.');
            }

            return $redirect;
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'No se pudo confirmar el grupo de reservas.');
        }
    }

    public function actualizarEstado(Request $request, $id)
    {
        $datos = $request->validate([
            'estado' => ['required', 'in:' . implode(',', self::ESTADOS)],
        ]);

        try {
            DB::transaction(function () use ($id, $datos) {
                $reserva = ReservaProductoModel::whereKey($id)->lockForUpdate()->firstOrFail();
                if ($reserva->estado === 'entregado') {
                    throw new \RuntimeException('Esta reserva ya fue entregada y su venta ya fue generada.');
                }

                if ($datos['estado'] !== 'entregado') {
                    $reserva->update(['estado' => $datos['estado']]);
                    return;
                }

                $producto = ProductoModel::whereKey($reserva->id_producto)->lockForUpdate()->firstOrFail();
                if ($producto->stock < $reserva->cantidad) {
                    throw new \RuntimeException("No hay stock suficiente para entregar {$producto->descripcion}. Disponible: {$producto->stock}.");
                }

                $venta = VentaModel::create([
                    'id_cliente' => $reserva->id_cliente,
                    'id_usuario' => session('idusuario'),
                    'fecha' => now()->toDateString(),
                    'total' => $producto->precioventa * $reserva->cantidad,
                ]);

                DetalleVentaModel::create([
                    'id_venta' => $venta->id_venta,
                    'id_producto' => $producto->id_producto,
                    'cantidad' => $reserva->cantidad,
                    'precioventa' => $producto->precioventa,
                ]);

                $producto->decrement('stock', $reserva->cantidad);
                $reserva->update(['estado' => 'entregado', 'id_venta' => $venta->id_venta]);
            });

            $mensajeActualizacion = $datos['estado'] === 'entregado'
                ? 'Estado actualizado correctamente. La venta se generó y el stock se descontó.'
                : 'Estado actualizado correctamente.';
            $respuesta = redirect()->route('reservas.productos.index')->with('success', $mensajeActualizacion);

            if ($datos['estado'] === 'confirmado') {
                $reserva = ReservaProductoModel::with(['cliente', 'producto'])->findOrFail($id);
                $telefono = preg_replace('/\D+/', '', (string) $reserva->cliente->telefono);
                if ($telefono !== '') {
                    if (!str_starts_with($telefono, '591')) $telefono = '591' . $telefono;
                    $fechaRecojo = $reserva->fecha_recojo
                        ? \Carbon\Carbon::parse($reserva->fecha_recojo)->format('d/m/Y')
                        : 'día acordado';
                    $horaRecojo = $reserva->hora_recojo
                        ? substr((string) $reserva->hora_recojo, 0, 5)
                        : 'hora acordada';
                    $mensaje = 'Hola ' . $reserva->cliente->nombre . ', confirmamos tu reserva de '
                        . $reserva->cantidad . ' unidad(es) de ' . $reserva->producto->descripcion
                        . '. Te esperamos el ' . $fechaRecojo . ' a las ' . $horaRecojo
                        . '. Método de pago: ' . ($reserva->metodo_pago ?: 'por coordinar') . '.';
                    $respuesta->with('whatsapp_url', 'https://wa.me/' . $telefono . '?text=' . urlencode($mensaje));
                } else {
                    $respuesta->with('warning', 'La reserva quedó confirmada, pero el cliente no tiene un teléfono registrado para WhatsApp.');
                }
            }

            return $respuesta;
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'No se pudo actualizar la reserva de producto.');
        }
    }
}
