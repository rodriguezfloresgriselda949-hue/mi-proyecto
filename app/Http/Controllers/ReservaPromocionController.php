<?php

namespace App\Http\Controllers;

use App\Models\ClienteModel;
use App\Models\PromocionModel;
use App\Models\ReservaPromocionModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservaPromocionController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d'],
            'estado' => ['nullable', 'in:todos,pendientes,atendiendo,completadas,canceladas'],
        ]);
        if (!empty($filtros['desde']) && !empty($filtros['hasta']) && $filtros['hasta'] < $filtros['desde']) {
            throw ValidationException::withMessages(['hasta' => 'La fecha "Hasta" debe ser igual o posterior a "Desde".']);
        }
        $estadoFiltro = $filtros['estado'] ?? 'todos';
        $query = ReservaPromocionModel::with(['promocion', 'cliente']);

        if (!empty($filtros['desde'])) $query->whereDate('fecha', '>=', $filtros['desde']);
        if (!empty($filtros['hasta'])) $query->whereDate('fecha', '<=', $filtros['hasta']);
        if ($estadoFiltro === 'pendientes') $query->whereIn('estado', ['Reservado', 'Pendiente']);
        elseif ($estadoFiltro === 'atendiendo') $query->where('estado', 'En Atención');
        elseif ($estadoFiltro === 'completadas') $query->where('estado', 'Completada');
        elseif ($estadoFiltro === 'canceladas') $query->where('estado', 'Cancelado');

        $reservas = $query->orderByDesc('fecha')->orderByDesc('hora')->get();
        return view('reservas_promociones.index', [
            'reservas' => $reservas,
            'desde' => $filtros['desde'] ?? '',
            'hasta' => $filtros['hasta'] ?? '',
            'estadoFiltro' => $estadoFiltro,
        ]);
    }

    public function create()
    {
        $promociones = PromocionModel::withCount(['reservas as reservas_activas' => function ($query) {
            $query->where('estado', '!=', 'Cancelado');
        }])->where('fecha_hora_fin', '>=', now())->orderBy('fecha_hora_inicio')->get();
        $clientes = ClienteModel::orderBy('nombre')->orderBy('apellido')->get();
        return view('reservas_promociones.create', compact('promociones', 'clientes'));
    }

    public function edit($id)
    {
        $reserva = ReservaPromocionModel::findOrFail($id);
        $promociones = PromocionModel::withCount(['reservas as reservas_activas' => function ($query) {
            $query->where('estado', '!=', 'Cancelado');
        }])->where(function ($query) use ($reserva) {
            $query->where('fecha_hora_fin', '>=', now())->orWhere('id_promocion', $reserva->id_promocion);
        })->orderBy('fecha_hora_inicio')->get();
        $clientes = ClienteModel::orderBy('nombre')->orderBy('apellido')->get();
        return view('reservas_promociones.create', compact('promociones', 'clientes', 'reserva'));
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        DB::transaction(function () use ($datos) {
            $promocion = PromocionModel::whereKey($datos['id_promocion'])->lockForUpdate()->firstOrFail();
            $this->validarVigenciaYCupo($datos, $promocion);
            ReservaPromocionModel::create([
                'id_promocion' => $promocion->id_promocion,
                'id_cliente' => $datos['id_cliente'],
                'fecha' => $datos['fecha'],
                'hora' => $datos['hora'],
                'precio_promocion' => $promocion->precio,
                'adelanto' => $datos['adelanto'],
                'estado' => 'Reservado',
            ]);
        });

        return redirect()->route('reservas.promociones.index')->with('success', 'Reserva de promoción registrada correctamente.');
    }

    public function update(Request $request, $id)
    {
        $reserva = ReservaPromocionModel::findOrFail($id);
        $datos = $this->validar($request);

        DB::transaction(function () use ($datos, $reserva) {
            $promocion = PromocionModel::whereKey($datos['id_promocion'])->lockForUpdate()->firstOrFail();
            $this->validarVigenciaYCupo($datos, $promocion, $reserva->id_reserva_promocion);
            $reserva->update([
                'id_promocion' => $promocion->id_promocion,
                'id_cliente' => $datos['id_cliente'],
                'fecha' => $datos['fecha'],
                'hora' => $datos['hora'],
                'precio_promocion' => $promocion->precio,
                'adelanto' => $datos['adelanto'],
                'estado' => 'Reservado',
            ]);
        });

        return redirect()->route('reservas.promociones.index')->with('success', 'Reserva de promoción actualizada correctamente.');
    }

    public function destroy($id)
    {
        ReservaPromocionModel::findOrFail($id)->delete();
        return redirect()->route('reservas.promociones.index')->with('success', 'Reserva de promoción eliminada correctamente.');
    }

    public function comprobante($id)
    {
        $reserva = ReservaPromocionModel::with(['promocion.servicios', 'cliente'])->findOrFail($id);
        return view('reservas_promociones.comprobante', compact('reserva'));
    }

    public function cambiarEstado(Request $request, $id)
    {
        $datos = $request->validate(['estado' => ['required', 'in:En Atención,Completada']]);
        $reserva = ReservaPromocionModel::findOrFail($id);
        $transicionPermitida = ($reserva->estado === 'Reservado' && $datos['estado'] === 'En Atención')
            || ($reserva->estado === 'En Atención' && $datos['estado'] === 'Completada');
        if (!$transicionPermitida) {
            return back()->withErrors(['estado' => 'La reserva solo puede pasar de Reservado a En Atención y luego a Completada.']);
        }
        $reserva->update(['estado' => $datos['estado']]);
        return back()->with('success', 'Estado de la reserva actualizado a ' . $datos['estado'] . '.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'id_promocion' => ['required', 'integer', 'exists:promociones,id_promocion'],
            'id_cliente' => ['required', 'integer', 'exists:clientes,id_cliente'],
            'fecha' => ['required', 'date_format:Y-m-d'],
            'hora' => ['required', 'date_format:H:i'],
            'adelanto' => ['required', 'numeric', 'min:0', 'max:999999.99'],
        ]);
    }

    private function validarVigenciaYCupo(array $datos, PromocionModel $promocion, ?int $idReservaActual = null): void
    {
        $fechaHora = Carbon::createFromFormat('Y-m-d H:i', $datos['fecha'] . ' ' . $datos['hora']);
        if ($fechaHora->lt($promocion->fecha_hora_inicio) || $fechaHora->gt($promocion->fecha_hora_fin)) {
            throw ValidationException::withMessages([
                'fecha' => 'La fecha y hora deben estar dentro de la vigencia de la promoción (' . $promocion->fecha_hora_inicio->format('d/m/Y H:i') . ' a ' . $promocion->fecha_hora_fin->format('d/m/Y H:i') . ').',
            ]);
        }

        $consulta = ReservaPromocionModel::where('id_promocion', $promocion->id_promocion)->where('estado', '!=', 'Cancelado');
        if ($idReservaActual) $consulta->where('id_reserva_promocion', '!=', $idReservaActual);
        if ($consulta->count() >= $promocion->cantidad_clientes) {
            throw ValidationException::withMessages(['id_promocion' => 'La promoción ya alcanzó el máximo de clientes.']);
        }
        if ((float) $datos['adelanto'] > (float) $promocion->precio) {
            throw ValidationException::withMessages(['adelanto' => 'El adelanto no puede superar el precio de la promoción.']);
        }
    }
}
