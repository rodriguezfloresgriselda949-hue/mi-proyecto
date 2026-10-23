<?php

namespace App\Http\Controllers;

use App\Models\ProductoModel;
use App\Models\CategoriaModel;
use App\Models\MarcaModel;
use App\Models\LoteProductoModel;
use App\Models\ProveedorModel;
use App\Models\ServicioModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductoController extends Controller
{
    public function catalogo(Request $request)
    {
        $query = ProductoModel::with(['marca', 'categoria'])
            ->where('stock', '>', 0);

        if ($request->filled('buscar')) {
            $buscar = trim($request->buscar);
            $query->where(function($q) use ($buscar) {
                $q->where('descripcion', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('categoria', function($cq) use ($buscar) {
                      $cq->where('nombre', 'LIKE', "%{$buscar}%");
                  })
                  ->orWhereHas('marca', function($mq) use ($buscar) {
                      $mq->where('nombre', 'LIKE', "%{$buscar}%");
                  });
            });
        }

        $productos = $query->orderBy('descripcion')->get();
        $servicios = ServicioModel::orderBy('nombre')->get();
        return view('catalogo', compact('productos', 'servicios'));
    }
    // Listado de productos
    public function index(Request $request)
    {
        $query = ProductoModel::with(['categoria', 'marca', 'lote', 'proveedor']);

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where('descripcion', 'LIKE', "%{$buscar}%")
                  ->orWhereHas('categoria', function($q) use ($buscar) {
                      $q->where('nombre', 'LIKE', "%{$buscar}%");
                  })
                  ->orWhereHas('marca', function($q) use ($buscar) {
                      $q->where('nombre', 'LIKE', "%{$buscar}%");
                  });
        }

        $productos = $query->orderBy('descripcion')->get();
        $totalProductos = $productos->count();
        $stockBajo = $productos->where('stock', '<=', 5)->count();
        $sinStock = $productos->where('stock', 0)->count();

        $categorias = CategoriaModel::withCount('productos')->orderBy('nombre')->get();

        return view('productos.index', compact('productos', 'totalProductos', 'stockBajo', 'sinStock', 'categorias'));
    }

    // Formulario de creación
    public function create()
    {
        $categorias = CategoriaModel::orderBy('nombre')->get();
        $marcas = MarcaModel::orderBy('nombre')->get();
        $proveedores = ProveedorModel::orderBy('nombre')->orderBy('apellido')->get();

        return view('productos.create', compact('categorias', 'marcas', 'proveedores'));
    }

    // Guardar nuevo producto
    public function store(Request $request)
    {
        $request->validate([
            'descripcion'   => 'required|string|min:2|max:255',
            'stock'         => 'required|integer|min:0',
            'preciocompra'  => 'required|numeric|min:0|max:999999.99',
            'precioventa'   => 'required|numeric|min:0|max:999999.99|gte:preciocompra',
            'id_categoria'  => 'required|exists:categorias,id_categoria',
            'idmarca'       => 'required|exists:marca,idmarca',
            'id_proveedor'  => 'required|exists:proveedores,id_proveedor',
            'fecha_caducidad' => 'required|date_format:Y-m-d|after_or_equal:today',
            'destacado' => 'nullable|string|max:50',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'precioventa.gte' => 'El precio de venta no puede ser menor al precio de compra.',
        ]);

        $imagen = $this->guardarImagen($request);
        DB::transaction(function () use ($request, $imagen) {
            // La base histórica exige idlote en productos y el lote exige idproducto.
            // Se crea ambos registros dentro de una transacción para no asociar el producto al lote 1.
            $producto = ProductoModel::create([
                'descripcion'  => Str::upper(trim($request->descripcion)),
                'destacado'    => $request->filled('destacado') ? Str::upper(trim($request->destacado)) : null,
                'imagen'       => $imagen,
                'stock'        => $request->stock,
                'preciocompra' => $request->preciocompra,
                'precioventa'  => $request->precioventa,
                'id_categoria' => $request->id_categoria,
                'idmarca'      => $request->idmarca,
                'id_proveedor' => $request->id_proveedor,
                'idlote'       => 0,
            ]);

            $lote = LoteProductoModel::create([
                'idproducto' => $producto->id_producto,
                'fecha_caducidad' => $request->fecha_caducidad,
                'cantidad_producto_lote' => $request->stock,
            ]);

            $producto->update(['idlote' => $lote->idlote]);
        });

        return redirect()->route('productos.index')->with('success', 'Producto registrado exitosamente.');
    }

    // Formulario de edición
    public function edit($id)
    {
        $producto = ProductoModel::findOrFail($id);
        $categorias = CategoriaModel::orderBy('nombre')->get();
        $marcas = MarcaModel::orderBy('nombre')->get();
        $proveedores = ProveedorModel::orderBy('nombre')->orderBy('apellido')->get();

        return view('productos.edit', compact('producto', 'categorias', 'marcas', 'proveedores'));
    }

    // Actualizar producto
    public function update(Request $request, $id)
    {
        $request->validate([
            'descripcion'   => 'required|string|min:2|max:255',
            'stock'         => 'required|integer|min:0',
            'preciocompra'  => 'required|numeric|min:0|max:999999.99',
            'precioventa'   => 'required|numeric|min:0|max:999999.99|gte:preciocompra',
            'id_categoria'  => 'required|exists:categorias,id_categoria',
            'idmarca'       => 'required|exists:marca,idmarca',
            'id_proveedor'  => 'required|exists:proveedores,id_proveedor',
            'destacado' => 'nullable|string|max:50',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $producto = ProductoModel::findOrFail($id);
        $datos = [
            'descripcion'  => Str::upper(trim($request->descripcion)),
            'destacado'    => $request->filled('destacado') ? Str::upper(trim($request->destacado)) : null,
            'stock'        => $request->stock,
            'preciocompra' => $request->preciocompra,
            'precioventa'  => $request->precioventa,
            'id_categoria' => $request->id_categoria,
            'idmarca'      => $request->idmarca,
            'id_proveedor' => $request->id_proveedor,
        ];
        if ($imagen = $this->guardarImagen($request)) {
            if ($producto->imagen && file_exists(public_path('productos/' . $producto->imagen))) {
                @unlink(public_path('productos/' . $producto->imagen));
            }
            $datos['imagen'] = $imagen;
        }
        $producto->update($datos);

        return redirect()->route('productos.index')->with('success', 'Producto actualizado exitosamente.');
    }

    // Eliminar producto
    public function destroy($id)
    {
        try {
            $producto = ProductoModel::findOrFail($id);
            $imagen = $producto->imagen;
            $producto->delete();

            if ($imagen && file_exists(public_path('productos/' . $imagen))) {
                @unlink(public_path('productos/' . $imagen));
            }

            return redirect()->route('productos.index')->with('success', 'Producto eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('productos.index')->with('error', 'No se puede eliminar el producto porque tiene ventas o compras asociadas.');
        }
    }

    /** Crear una categoría desde la administración de inventario. */
    public function guardarCategoria(Request $request)
    {
        $datos = $request->validateWithBag('categoria', [
            'nombre_categoria' => ['required', 'string', 'min:2', 'max:100', 'unique:categorias,nombre'],
        ], [
            'nombre_categoria.required' => 'Escriba el nombre de la categoría.',
            'nombre_categoria.unique' => 'Esta categoría ya está registrada.',
        ]);

        CategoriaModel::create(['nombre' => Str::upper(trim($datos['nombre_categoria']))]);

        return redirect()->route('productos.index')->with('success', 'Categoría registrada correctamente.');
    }

    /** Actualizar una categoría desde la administración de inventario. */
    public function actualizarCategoria(Request $request, $id)
    {
        $categoria = CategoriaModel::findOrFail($id);
        $datos = $request->validateWithBag('categoria', [
            'nombre' => ['required', 'string', 'min:2', 'max:100', 'unique:categorias,nombre,' . $categoria->id_categoria . ',id_categoria'],
        ], [
            'nombre.unique' => 'Ya existe una categoría con ese nombre.',
        ]);

        $categoria->update(['nombre' => Str::upper(trim($datos['nombre']))]);

        return redirect()->route('productos.index')->with('success', 'Categoría actualizada correctamente.');
    }

    /** Eliminar una categoría solo cuando no esté en uso. */
    public function eliminarCategoria($id)
    {
        $categoria = CategoriaModel::withCount('productos')->findOrFail($id);
        if ($categoria->productos_count > 0) {
            return redirect()->route('productos.index')->with('error', 'No se puede eliminar la categoría porque tiene productos asociados.');
        }

        $categoria->delete();

        return redirect()->route('productos.index')->with('success', 'Categoría eliminada correctamente.');
    }

    // Endpoint AJAX para autocompletado en Punto de Venta
    public function buscarAjax(Request $request)
    {
        $term = $request->get('term', '');
        $productos = ProductoModel::with(['categoria', 'marca'])
            ->where('stock', '>', 0)
            ->where(function($q) use ($term) {
                $q->where('descripcion', 'LIKE', "%{$term}%");
            })
            ->limit(10)
            ->get();

        return response()->json($productos);
    }

    private function guardarImagen(Request $request): ?string
    {
        if (! $request->hasFile('imagen')) return null;
        $archivo = $request->file('imagen');
        $nombre = Str::uuid() . '.' . $archivo->getClientOriginalExtension();
        $archivo->move(public_path('productos'), $nombre);
        return $nombre;
    }
}
