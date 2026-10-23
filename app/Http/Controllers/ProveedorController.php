<?php

namespace App\Http\Controllers;

use App\Models\ProveedorModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProveedorController extends Controller
{
    private function reglasProveedor(): array
    {
        return [
            'nombre' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u'],
            'apellido' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u'],
            'nombre_empresa' => ['required', 'string', 'min:2', 'max:150', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s.,&()\-]+$/u'],
            'telefono' => ['nullable', 'string', 'regex:/^[0-9+\-\s]{6,20}$/'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email:rfc', 'max:100'],
        ];
    }

    // Listado de proveedores
    public function index(Request $request)
    {
        $query = ProveedorModel::query();

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where('nombre', 'LIKE', "%{$buscar}%")
                  ->orWhere('apellido', 'LIKE', "%{$buscar}%")
                  ->orWhere('nombre_empresa', 'LIKE', "%{$buscar}%")
                  ->orWhere('email', 'LIKE', "%{$buscar}%")
                  ->orWhere('telefono', 'LIKE', "%{$buscar}%");
        }

        $proveedores = $query->orderBy('id_proveedor', 'desc')->get();

        return view('proveedores.index', compact('proveedores'));
    }

    // Formulario de creación
    public function create()
    {
        return view('proveedores.create');
    }

    // Guardar nuevo proveedor
    public function store(Request $request)
    {
        $datos = $request->validate($this->reglasProveedor());

        $proveedor = ProveedorModel::create([
            'nombre' => Str::upper(trim($datos['nombre'])),
            'apellido' => Str::upper(trim($datos['apellido'])),
            'nombre_empresa' => Str::upper(trim($datos['nombre_empresa'])),
            'telefono' => isset($datos['telefono']) ? trim($datos['telefono']) : null,
            'direccion' => isset($datos['direccion']) ? Str::upper(trim($datos['direccion'])) : null,
            'email' => isset($datos['email']) ? trim($datos['email']) : null,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'id_proveedor' => $proveedor->id_proveedor,
                'nombre_completo' => $proveedor->nombre_completo,
            ], 201);
        }

        return redirect()->route('proveedores.index')->with('success', 'Proveedor registrado exitosamente.');
    }

    // Formulario de edición
    public function edit($id)
    {
        $proveedor = ProveedorModel::findOrFail($id);
        return view('proveedores.edit', compact('proveedor'));
    }

    // Actualizar proveedor
    public function update(Request $request, $id)
    {
        $datos = $request->validate($this->reglasProveedor());

        $proveedor = ProveedorModel::findOrFail($id);
        $proveedor->update([
            'nombre' => Str::upper(trim($datos['nombre'])),
            'apellido' => Str::upper(trim($datos['apellido'])),
            'nombre_empresa' => Str::upper(trim($datos['nombre_empresa'])),
            'telefono' => isset($datos['telefono']) ? trim($datos['telefono']) : null,
            'direccion' => isset($datos['direccion']) ? Str::upper(trim($datos['direccion'])) : null,
            'email' => isset($datos['email']) ? trim($datos['email']) : null,
        ]);

        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado exitosamente.');
    }

    // Eliminar proveedor
    public function destroy($id)
    {
        try {
            $proveedor = ProveedorModel::findOrFail($id);
            $proveedor->delete();

            return redirect()->route('proveedores.index')->with('success', 'Proveedor eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('proveedores.index')->with('error', 'No se puede eliminar el proveedor porque tiene compras registradas.');
        }
    }
}
