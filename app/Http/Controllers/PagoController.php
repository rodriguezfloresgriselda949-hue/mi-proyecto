<?php

namespace App\Http\Controllers;

use App\Models\AdelantoModel;
use App\Models\PagoModel;
use App\Models\ReservasModel;
use App\Models\UsuarioModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    private const ESTADOS_COMPLETADOS = ['Completada', 'Finalizado'];

    public function index(Request $request)
    {
        $empleados = UsuarioModel::where('rol', 'Empleado')->where('estado', 'activo')->orderBy('username')->get();
        $pendientesPago = $empleados->map(function ($persona) {
            $periodo = $this->periodo($persona->pago);
            $yaPagado = PagoModel::where('id_usuario', $persona->id_usuario)
                ->where('fecha_inicio', $periodo['inicio'])->where('fecha_fin', $periodo['fin'])->exists();
            if ($yaPagado) return null;
            $servicios = (float) ReservasModel::where('id_usuario', $persona->id_usuario)
                ->whereBetween('fecha', [$periodo['inicio'], $periodo['fin']])
                ->whereIn('estado', self::ESTADOS_COMPLETADOS)->sum('subtotal');
            $adelantos = (float) AdelantoModel::where('id_usuario', $persona->id_usuario)
                ->whereNull('id_pago')->whereBetween('fecha', [$periodo['inicio'], $periodo['fin']])->sum('monto');
            $monto = max(0, round($servicios * ((float) $persona->experiencia / 100) - $adelantos, 2));
            return $monto > 0 ? ['empleado' => $persona, 'periodo' => $periodo, 'monto' => $monto] : null;
        })->filter()->values();
        $empleado = $request->filled('empleado')
            ? $empleados->firstWhere('id_usuario', (int) $request->empleado)
            : null;

        $detalle = null;
        if ($empleado) {
            $periodo = $this->periodo($empleado->pago);
            $reservas = ReservasModel::with(['cliente', 'servicio'])
                ->where('id_usuario', $empleado->id_usuario)
                ->whereBetween('fecha', [$periodo['inicio'], $periodo['fin']])
                ->whereIn('estado', self::ESTADOS_COMPLETADOS)
                ->orderByDesc('fecha')->get();
            $adelantos = AdelantoModel::where('id_usuario', $empleado->id_usuario)
                ->whereNull('id_pago')
                ->whereBetween('fecha', [$periodo['inicio'], $periodo['fin']])
                ->orderByDesc('id_adelanto')->get();
            $totalServicios = (float) $reservas->sum('subtotal');
            $porcentaje = (float) $empleado->experiencia;
            $pagoBruto = round($totalServicios * ($porcentaje / 100), 2);
            $totalAdelantos = (float) $adelantos->sum('monto');
            $montoPagar = max(0, round($pagoBruto - $totalAdelantos, 2));
            $pagoRegistrado = PagoModel::where('id_usuario', $empleado->id_usuario)
                ->where('fecha_inicio', $periodo['inicio'])->where('fecha_fin', $periodo['fin'])->first();
            $detalle = compact('periodo', 'reservas', 'adelantos', 'porcentaje', 'totalServicios', 'pagoBruto', 'totalAdelantos', 'montoPagar', 'pagoRegistrado');
        }

        $pagos = PagoModel::with('empleado')->orderByDesc('id_pago')->limit(10)->get();
        return view('pagos.index', compact('empleados', 'empleado', 'detalle', 'pagos', 'pendientesPago'));
    }

    public function comprobante($id)
    {
        $pago = PagoModel::with('empleado')->findOrFail($id);
        $reservas = ReservasModel::with(['cliente', 'servicio', 'servicios'])
            ->where('id_usuario', $pago->id_usuario)
            ->whereBetween('fecha', [$pago->fecha_inicio, $pago->fecha_fin])
            ->whereIn('estado', self::ESTADOS_COMPLETADOS)
            ->orderBy('fecha')->orderBy('hora')->get();
        $adelantos = AdelantoModel::where('id_pago', $pago->id_pago)->orderBy('fecha')->orderBy('id_adelanto')->get();
        return view('pagos.comprobante', compact('pago', 'reservas', 'adelantos'));
    }

    public function guardarAdelanto(Request $request)
    {
        $datos = $request->validate([
            'id_usuario' => ['required', 'exists:usuario,id_usuario'],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'motivo' => ['required', 'string', 'min:3', 'max:255'],
        ]);
        $empleado = UsuarioModel::whereKey($datos['id_usuario'])->where('rol', 'Empleado')->firstOrFail();
        $periodo = $this->periodo($empleado->pago);

        AdelantoModel::create([
            'id_usuario' => $empleado->id_usuario,
            'monto' => $datos['monto'],
            'motivo' => trim($datos['motivo']),
            'fecha' => now()->toDateString(),
        ]);

        return redirect()->route('pagos.index', ['empleado' => $empleado->id_usuario])
            ->with('success', "Adelanto registrado para el período {$periodo['etiqueta']}.");
    }

    public function registrarPago(Request $request)
    {
        $datos = $request->validate(['id_usuario' => ['required', 'exists:usuario,id_usuario']]);
        try {
            DB::transaction(function () use ($datos) {
                $empleado = UsuarioModel::whereKey($datos['id_usuario'])->lockForUpdate()->firstOrFail();
                if ($empleado->rol !== 'Empleado') throw new \RuntimeException('Solo se pueden registrar pagos para empleados.');
                if (!in_array((int) $empleado->experiencia, [30, 35, 40, 50], true)) throw new \RuntimeException('Debe asignar una experiencia válida al empleado antes de registrar el pago.');
                $periodo = $this->periodo($empleado->pago);
                if (PagoModel::where('id_usuario', $empleado->id_usuario)->where('fecha_inicio', $periodo['inicio'])->where('fecha_fin', $periodo['fin'])->exists()) {
                    throw new \RuntimeException('El pago de este período ya fue registrado.');
                }
                $totalServicios = (float) ReservasModel::where('id_usuario', $empleado->id_usuario)
                    ->whereBetween('fecha', [$periodo['inicio'], $periodo['fin']])
                    ->whereIn('estado', self::ESTADOS_COMPLETADOS)->sum('subtotal');
                if ($totalServicios <= 0) throw new \RuntimeException('No existen servicios completados para pagar en este período.');
                $adelantos = AdelantoModel::where('id_usuario', $empleado->id_usuario)->whereNull('id_pago')
                    ->whereBetween('fecha', [$periodo['inicio'], $periodo['fin']])->lockForUpdate()->get();
                $porcentaje = (float) $empleado->experiencia;
                $bruto = round($totalServicios * ($porcentaje / 100), 2);
                $totalAdelantos = (float) $adelantos->sum('monto');
                $pago = PagoModel::create([
                    'id_usuario' => $empleado->id_usuario, 'fecha_inicio' => $periodo['inicio'], 'fecha_fin' => $periodo['fin'],
                    'total_servicios' => $totalServicios, 'porcentaje' => $porcentaje, 'total_adelantos' => $totalAdelantos,
                    'monto_pagado' => max(0, round($bruto - $totalAdelantos, 2)), 'fecha_pago' => now()->toDateString(),
                ]);
                AdelantoModel::whereIn('id_adelanto', $adelantos->pluck('id_adelanto'))->update(['id_pago' => $pago->id_pago]);
            });
            return redirect()->route('pagos.index', ['empleado' => $datos['id_usuario']])->with('success', 'Pago registrado correctamente.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function periodo(?string $modalidad): array
    {
        $hoy = Carbon::today();
        $modalidad = strtolower((string) $modalidad);
        if ($modalidad === 'semanal') { $inicio = $hoy->copy()->startOfWeek(); $fin = $hoy->copy()->endOfWeek(); }
        elseif ($modalidad === 'quincenal') { $inicio = $hoy->day <= 15 ? $hoy->copy()->startOfMonth() : $hoy->copy()->day(16); $fin = $hoy->day <= 15 ? $hoy->copy()->day(15) : $hoy->copy()->endOfMonth(); }
        else { $inicio = $hoy->copy()->startOfMonth(); $fin = $hoy->copy()->endOfMonth(); }
        return ['inicio' => $inicio->toDateString(), 'fin' => $fin->toDateString(), 'etiqueta' => $inicio->format('d/m/Y') . ' al ' . $fin->format('d/m/Y')];
    }
}
