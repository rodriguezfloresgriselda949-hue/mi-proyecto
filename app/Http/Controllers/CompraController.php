<?php

namespace App\Http\Controllers;

use App\Models\CompraModel;
use App\Models\DetalleCompraModel;
use App\Models\ProductoModel;
use App\Models\ProveedorModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CompraController extends Controller
{
    // Historial de compras
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d'],
            'producto' => ['nullable', 'integer', 'exists:productos,id_producto'],
        ]);
        if (!empty($filtros['desde']) && !empty($filtros['hasta']) && $filtros['hasta'] < $filtros['desde']) {
            return back()->withErrors(['hasta' => 'La fecha "Hasta" debe ser igual o posterior a "Desde".'])->withInput();
        }

        $consulta = CompraModel::with(['proveedor', 'usuario', 'detalles.producto']);
        if (!empty($filtros['desde'])) $consulta->whereDate('fecha', '>=', $filtros['desde']);
        if (!empty($filtros['hasta'])) $consulta->whereDate('fecha', '<=', $filtros['hasta']);
        if (!empty($filtros['producto'])) {
            $consulta->whereHas('detalles', fn ($detalle) => $detalle->where('id_producto', $filtros['producto']));
        }

        $compras = $consulta
            ->orderBy('id_compra', 'desc')
            ->get();
        $totalCompras = $compras->sum('total');
        $productos = ProductoModel::orderBy('descripcion')->get(['id_producto', 'descripcion']);
        $productoSeleccionado = $filtros['producto'] ?? null;

        return view('compras.index', compact('compras', 'totalCompras', 'productos', 'productoSeleccionado'));
    }

    // Formulario de nueva compra
    public function create()
    {
        $proveedores = ProveedorModel::orderBy('nombre')->get();
        $productos = ProductoModel::orderBy('descripcion')->get();

        return view('compras.create', compact('proveedores', 'productos'));
    }

    // Guardar compra y aumentar stock
    public function store(Request $request)
    {
        $request->validate([
            'id_proveedor'     => 'required|exists:proveedores,id_proveedor',
            'productos'        => 'required|array|min:1',
            'productos.*.id'   => 'required|distinct|exists:productos,id_producto',
            'productos.*.cant' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0|max:999999.99',
        ], [
            'productos.required' => 'Debe agregar al menos un producto a la compra.',
            'id_proveedor.required' => 'Debe seleccionar un proveedor.',
        ]);

        $idUsuario = session('idusuario', 1);

        try {
            DB::transaction(function() use ($request, $idUsuario) {
                $totalCompra = 0;
                foreach ($request->productos as $item) {
                    $totalCompra += ($item['cant'] * $item['precio']);
                }

                $compra = CompraModel::create([
                    'id_proveedor' => $request->id_proveedor,
                    'id_usuario'   => $idUsuario,
                    'fecha'        => Carbon::now(),
                    'total'        => $totalCompra,
                ]);

                foreach ($request->productos as $item) {
                    DetalleCompraModel::create([
                        'id_compra'   => $compra->id_compra,
                        'id_producto' => $item['id'],
                        'cantidad'    => $item['cant'],
                        'precio'      => $item['precio'],
                    ]);

                    // Incrementar inventario del producto
                    $producto = ProductoModel::whereKey($item['id'])->lockForUpdate()->firstOrFail();
                    $producto->increment('stock', $item['cant']);
                    $producto->preciocompra = $item['precio'];
                    $producto->save();
                }
            });

            return redirect()->route('compras.index')->with('success', '¡Compra registrada y stock actualizado con éxito!');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'No se pudo procesar la compra. Intente nuevamente.')->withInput();
        }
    }

    // Comprobante imprimible de una compra registrada.
    public function comprobante($id)
    {
        $compra = CompraModel::with(['proveedor', 'usuario', 'detalles.producto'])->findOrFail($id);

        return view('compras.comprobante', compact('compra'));
    }
}
