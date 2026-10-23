<?php

namespace App\Http\Controllers;

use App\Models\PromocionModel;
use App\Models\ServicioModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PromocionController extends Controller
{
    public function create()
    {
        $servicios = ServicioModel::orderBy('nombre')->get();
        return view('promociones.form', compact('servicios'));
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        $datos['descripcion'] = isset($datos['descripcion']) ? Str::lower(trim($datos['descripcion'])) : null;
        $datos['imagen'] = $this->guardarImagen($request);
        $promocion = PromocionModel::create($datos);
        $promocion->servicios()->sync($datos['id_servicios']);

        return redirect()->route('servicio.lista')->with('success', 'Promoción registrada correctamente.');
    }

    public function edit($id)
    {
        $promocion = PromocionModel::with('servicios')->findOrFail($id);
        $servicios = ServicioModel::orderBy('nombre')->get();
        return view('promociones.form', compact('promocion', 'servicios'));
    }

    public function update(Request $request, $id)
    {
        $promocion = PromocionModel::findOrFail($id);
        $datos = $this->validar($request);
        $datos['descripcion'] = isset($datos['descripcion']) ? Str::lower(trim($datos['descripcion'])) : null;
        $imagen = $this->guardarImagen($request);
        unset($datos['id_servicios']);
        if ($imagen) {
            if ($promocion->imagen && File::exists(public_path('promociones/' . $promocion->imagen))) {
                File::delete(public_path('promociones/' . $promocion->imagen));
            }
            $datos['imagen'] = $imagen;
        }
        $promocion->update($datos);
        $promocion->servicios()->sync($request->input('id_servicios'));

        return redirect()->route('servicio.lista')->with('success', 'Promoción actualizada correctamente.');
    }

    public function destroy($id)
    {
        $promocion = PromocionModel::findOrFail($id);
        if ($promocion->imagen && File::exists(public_path('promociones/' . $promocion->imagen))) {
            File::delete(public_path('promociones/' . $promocion->imagen));
        }
        $promocion->delete();
        return redirect()->route('servicio.lista')->with('success', 'Promoción eliminada correctamente.');
    }

    public function informacion($id)
    {
        $promocion = PromocionModel::with('servicios')->findOrFail($id);
        return response()->json([
            'nombre' => $promocion->nombre,
            'descripcion' => $promocion->descripcion ? Str::lower($promocion->descripcion) : null,
            'inicio' => $promocion->fecha_hora_inicio->format('d/m/Y H:i'),
            'fin' => $promocion->fecha_hora_fin->format('d/m/Y H:i'),
            'precio' => number_format((float) $promocion->precio, 2),
            'cantidad_clientes' => $promocion->cantidad_clientes,
            'servicios' => $promocion->servicios->pluck('nombre'),
        ]);
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'fecha_hora_inicio' => ['required', 'date'],
            'fecha_hora_fin' => ['required', 'date', 'after:fecha_hora_inicio'],
            'precio' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'cantidad_clientes' => ['required', 'integer', 'min:1', 'max:1000000'],
            'id_servicios' => ['required', 'array', 'min:1'],
            'id_servicios.*' => ['integer', 'distinct', 'exists:servicios,id_servicio'],
        ]);
    }

    private function guardarImagen(Request $request): ?string
    {
        if (!$request->hasFile('imagen')) return null;
        $directorio = public_path('promociones');
        if (!is_dir($directorio)) mkdir($directorio, 0755, true);
        $archivo = $request->file('imagen');
        $nombre = Str::uuid() . '.' . $archivo->getClientOriginalExtension();
        $archivo->move($directorio, $nombre);
        return $nombre;
    }
}
