<?php

namespace App\Http\Controllers;

use App\Models\VentaModel;
use App\Models\DetalleVentaModel;
use App\Models\ProductoModel;
use App\Models\ClienteModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VentaController extends Controller
{
    // Historial de ventas
    public function index(Request $request)
    {
        $query = VentaModel::with(['cliente', 'usuario', 'detalles.producto']);

        $fechaDesde = $request->input('fecha_desde', Carbon::today()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', Carbon::today()->toDateString());
        if ($fechaDesde > $fechaHasta) {
            [$fechaDesde, $fechaHasta] = [$fechaHasta, $fechaDesde];
        }

        $query->whereBetween('fecha', [$fechaDesde, $fechaHasta]);

        $ventas = $query->orderBy('id_venta', 'desc')->get();
        $totalRecaudado = $ventas->sum('total');

        return view('ventas.index', compact('ventas', 'totalRecaudado', 'fechaDesde', 'fechaHasta'));
    }

    // Formulario de Punto de Venta (Nueva Venta)
    public function create()
    {
        $clientes = ClienteModel::orderBy('nombre')->get();
        $productos = ProductoModel::with(['categoria', 'marca'])
            ->where('stock', '>', 0)
            ->orderBy('descripcion')
            ->get();

        return view('ventas.create', compact('clientes', 'productos'));
    }

    // Procesar y guardar la venta
    public function store(Request $request)
    {
        $request->validate([
            'id_cliente'     => 'required|exists:clientes,id_cliente',
            'productos'      => 'required|array|min:1',
            'productos.*.id' => 'required|distinct|exists:productos,id_producto',
            'productos.*.cant'   => 'required|integer|min:1',
        ], [
            'productos.required' => 'Debe agregar al menos un producto al carrito de venta.',
            'id_cliente.required' => 'Debe seleccionar un cliente.',
        ]);

        $idUsuario = session('idusuario', 1);

        try {
            $idVenta = DB::transaction(function() use ($request, $idUsuario) {
                // Calcular total
                $totalVenta = 0;
                foreach ($request->productos as $item) {
                    $producto = ProductoModel::whereKey($item['id'])->lockForUpdate()->firstOrFail();
                    $totalVenta += ($item['cant'] * $producto->precioventa);
                }

                // Crear cabecera de venta
                $venta = VentaModel::create([
                    'id_cliente' => $request->id_cliente,
                    'id_usuario' => $idUsuario,
                    'fecha'      => Carbon::now()->toDateString(),
                    'total'      => $totalVenta,
                ]);

                // Registrar detalles y actualizar stock
                foreach ($request->productos as $item) {
                    $producto = ProductoModel::whereKey($item['id'])->lockForUpdate()->firstOrFail();

                    if ($producto->stock < $item['cant']) {
                        throw new \RuntimeException("Stock insuficiente para el producto: " . $producto->descripcion . " (Disponible: " . $producto->stock . ")");
                    }

                    // Crear detalle
                    DetalleVentaModel::create([
                        'id_venta'     => $venta->id_venta,
                        'id_producto'  => $item['id'],
                        'cantidad'     => $item['cant'],
                        'precioventa'  => $producto->precioventa,
                    ]);

                    // Descontar inventario
                    $producto->decrement('stock', $item['cant']);
                }

                return $venta->id_venta;
            });

            return redirect()->route('ventas.show', $idVenta)->with('success', '¡Venta registrada con éxito!');

        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'No se pudo procesar la venta. Intente nuevamente.')->withInput();
        }
    }

    // Ver comprobante / recibo de venta
    public function show($id)
    {
        $venta = VentaModel::with(['cliente', 'usuario', 'detalles.producto.marca'])->findOrFail($id);

        if (strtolower(session('rol')) !== 'administrador' && (int) $venta->id_usuario !== (int) session('idusuario')) {
            abort(403, 'No tiene permiso para ver esta venta.');
        }

        return view('ventas.show', compact('venta'));
    }

    // Cancelar y anular una venta (reponiendo stock)
    public function destroy($id)
    {
        try {
            DB::transaction(function() use ($id) {
                $venta = VentaModel::with('detalles')->findOrFail($id);

                // Reponer stock
                foreach ($venta->detalles as $detalle) {
                        $producto = ProductoModel::whereKey($detalle->id_producto)->lockForUpdate()->first();
                    if ($producto) {
                        $producto->increment('stock', $detalle->cantidad);
                    }
                    $detalle->delete();
                }

                $venta->delete();
            });

            return redirect()->route('ventas.index')->with('success', 'Venta anulada y stock restituido correctamente.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('ventas.index')->with('error', 'No se pudo anular la venta. Intente nuevamente.');
        }
    }
}
