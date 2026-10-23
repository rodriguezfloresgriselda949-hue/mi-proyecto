<?php

namespace App\Http\Controllers;

use App\Models\VentaModel;
use App\Models\CompraModel;
use App\Models\ReservasModel;
use App\Models\ServicioModel;
use App\Models\ProductoModel;
use App\Models\DetalleVentaModel;
use App\Models\UsuarioModel;
use App\Models\ReservaPromocionModel;
use App\Models\PromocionModel;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $datos = $request->validate([
            'fecha_desde' => ['nullable', 'date_format:Y-m-d'],
            'fecha_hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:fecha_desde'],
        ]);
        $fechaDesde = $datos['fecha_desde'] ?? Carbon::now()->startOfMonth()->toDateString();
        $fechaHasta = $datos['fecha_hasta'] ?? Carbon::now()->endOfMonth()->toDateString();
        $estadosCompletados = ['Completada', 'Finalizado'];

        // Ventas en el rango
        $ventas = VentaModel::with(['cliente', 'usuario', 'detalles.producto'])
            ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
            ->orderBy('fecha', 'desc')
            ->get();
        $totalVentas = $ventas->sum('total');

        // Reservas atendidas / completadas en el rango
        $reservas = ReservasModel::with(['cliente', 'servicio', 'usuario'])
            ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
            ->orderBy('fecha', 'desc')
            ->get();
        $totalServicios = $reservas->whereIn('estado', $estadosCompletados)->sum('subtotal');

        // Compras del mismo período para que cada pestaña muestre el detalle
        // que corresponde a su gráfica.
        $compras = CompraModel::with(['proveedor', 'usuario', 'detalles.producto'])
            ->whereDate('fecha', '>=', $fechaDesde)
            ->whereDate('fecha', '<=', $fechaHasta)
            ->orderByDesc('fecha')
            ->get();

        $reservasPromociones = ReservaPromocionModel::with(['promocion', 'cliente'])
            ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->get();

        $resumenReservasPromociones = DB::table('promociones')
            ->leftJoinSub(
                DB::table('reservas_promociones')
                    ->select('id_promocion')
                    ->selectRaw('COUNT(*) as total_reservas')
                    ->selectRaw("SUM(CASE WHEN estado <> 'Cancelado' THEN 1 ELSE 0 END) as reservas_activas")
                    ->selectRaw("SUM(CASE WHEN estado = 'Completada' THEN 1 ELSE 0 END) as reservas_completadas")
                    ->selectRaw("SUM(CASE WHEN estado <> 'Cancelado' THEN adelanto ELSE 0 END) as adelantos_registrados")
                    ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
                    ->groupBy('id_promocion'),
                'resumen_reservas',
                'resumen_reservas.id_promocion',
                '=',
                'promociones.id_promocion'
            )
            ->select(
                'promociones.id_promocion',
                'promociones.nombre',
                'promociones.cantidad_clientes'
            )
            ->selectRaw('COALESCE(resumen_reservas.total_reservas, 0) as total_reservas')
            ->selectRaw('COALESCE(resumen_reservas.reservas_activas, 0) as reservas_activas')
            ->selectRaw('COALESCE(resumen_reservas.reservas_completadas, 0) as reservas_completadas')
            ->selectRaw('COALESCE(resumen_reservas.adelantos_registrados, 0) as adelantos_registrados')
            ->orderByDesc('reservas_activas')
            ->orderBy('promociones.nombre')
            ->get()
            ->map(function ($promocion) {
                $promocion->plazas_disponibles = max(0, (int) $promocion->cantidad_clientes - (int) $promocion->reservas_activas);
                $promocion->ocupacion_porcentaje = (int) $promocion->cantidad_clientes > 0
                    ? min(100, round((int) $promocion->reservas_activas / (int) $promocion->cantidad_clientes * 100, 1))
                    : 0;
                return $promocion;
            });

        $inicioGrafica = Carbon::parse($fechaDesde)->startOfMonth();
        $finGrafica = Carbon::parse($fechaHasta)->endOfMonth();
        $ventasMensuales = VentaModel::whereBetween('fecha', [$fechaDesde, $fechaHasta])
            ->selectRaw("DATE_FORMAT(fecha, '%Y-%m') as periodo, SUM(total) as total")
            ->groupBy('periodo')->pluck('total', 'periodo');
        $comprasMensuales = CompraModel::whereDate('fecha', '>=', $fechaDesde)
            ->whereDate('fecha', '<=', $fechaHasta)
            ->selectRaw("DATE_FORMAT(fecha, '%Y-%m') as periodo, SUM(total) as total")
            ->groupBy('periodo')->pluck('total', 'periodo');
        $reservasMensuales = ReservasModel::whereBetween('fecha', [$fechaDesde, $fechaHasta])
            ->whereIn('estado', $estadosCompletados)
            ->selectRaw("DATE_FORMAT(fecha, '%Y-%m') as periodo, SUM(CAST(subtotal AS DECIMAL(10,2))) as total")
            ->groupBy('periodo')->pluck('total', 'periodo');

        $graficaComparativa = [];
        $mesesEnPeriodo = $inicioGrafica->diffInMonths($finGrafica) + 1;
        for ($mesOffset = 0; $mesOffset < $mesesEnPeriodo; $mesOffset++) {
            $mes = $inicioGrafica->copy()->addMonths($mesOffset);
            $periodo = $mes->format('Y-m');
            $graficaComparativa[] = [
                'etiqueta' => $mes->translatedFormat('M Y'),
                'ventas' => (float) ($ventasMensuales[$periodo] ?? 0),
                'compras' => (float) ($comprasMensuales[$periodo] ?? 0),
                'reservas' => (float) ($reservasMensuales[$periodo] ?? 0),
            ];
        }

        // Ingresos globales
        $ingresoTotal = $totalVentas + $totalServicios;

        // Productos más vendidos
        $topProductos = DetalleVentaModel::join('ventas', 'detalleventa.id_venta', '=', 'ventas.id_venta')
            ->join('productos', 'detalleventa.id_producto', '=', 'productos.id_producto')
            ->whereBetween('ventas.fecha', [$fechaDesde, $fechaHasta])
            ->select('productos.descripcion', DB::raw('SUM(detalleventa.cantidad) as total_vendidos'), DB::raw('SUM(detalleventa.cantidad * detalleventa.precioventa) as total_recaudado'))
            ->groupBy('productos.id_producto', 'productos.descripcion')
            ->orderByDesc('total_vendidos')
            ->limit(5)
            ->get();

        // Servicios más solicitados
        $topServicios = ReservasModel::join('servicios', 'reservas.id_servicio', '=', 'servicios.id_servicio')
            ->whereBetween('reservas.fecha', [$fechaDesde, $fechaHasta])
            ->whereIn('reservas.estado', $estadosCompletados)
            ->select('servicios.nombre', DB::raw('COUNT(reservas.id_reserva) as total_reservas'), DB::raw('SUM(reservas.subtotal) as total_generado'))
            ->groupBy('servicios.id_servicio', 'servicios.nombre')
            ->orderByDesc('total_reservas')
            ->limit(5)
            ->get();

        // Desempeño por Empleado
        $desempenoPersonal = ReservasModel::join('usuario', 'reservas.id_usuario', '=', 'usuario.id_usuario')
            ->whereBetween('reservas.fecha', [$fechaDesde, $fechaHasta])
            ->whereIn('reservas.estado', $estadosCompletados)
            ->select('usuario.id_usuario', 'usuario.username', DB::raw('COUNT(reservas.id_reserva) as total_atenciones'), DB::raw('SUM(reservas.subtotal) as total_generado'))
            ->groupBy('usuario.id_usuario', 'usuario.username')
            ->orderByDesc('total_atenciones')
            ->get();

        // Promociones atendidas en el período. Las reservas promocionales no
        // guardan empleado, por eso se muestran como resumen independiente.
        $promocionesAtendidas = ReservaPromocionModel::join('promociones', 'reservas_promociones.id_promocion', '=', 'promociones.id_promocion')
            ->whereBetween('reservas_promociones.fecha', [$fechaDesde, $fechaHasta])
            ->whereIn('reservas_promociones.estado', $estadosCompletados)
            ->select('promociones.nombre')
            ->selectRaw('COUNT(reservas_promociones.id_reserva_promocion) as total_atenciones')
            ->selectRaw('SUM(reservas_promociones.precio_promocion) as total_generado')
            ->groupBy('promociones.id_promocion', 'promociones.nombre')
            ->orderByDesc('total_atenciones')
            ->get();

        return view('reportes.index', compact(
            'fechaDesde',
            'fechaHasta',
            'ventas',
            'reservas',
            'compras',
            'reservasPromociones',
            'resumenReservasPromociones',
            'totalVentas',
            'totalServicios',
            'ingresoTotal',
            'graficaComparativa',
            'topProductos',
            'topServicios',
            'desempenoPersonal',
            'promocionesAtendidas'
        ));
    }
}
