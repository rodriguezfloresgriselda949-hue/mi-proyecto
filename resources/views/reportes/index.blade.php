@extends('adm_plantilla')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 d-print-none">
    <div>
        <h4 class="m-0 text-danger fw-bold"><i class="bi bi-bar-chart-fill me-2"></i>Reportes y estadísticas</h4>
        <small class="text-muted">Consulta cada gráfica y revisa debajo las operaciones que la componen.</small>
    </div>
    <button onclick="window.print()" class="btn btn-danger rounded-pill shadow-sm"><i class="bi bi-printer-fill me-1"></i>Imprimir reporte</button>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4 d-print-none">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('reportes.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4"><label for="fecha_desde" class="form-label small text-muted fw-bold mb-1">Desde</label><input id="fecha_desde" type="date" name="fecha_desde" value="{{ $fechaDesde }}" class="form-control"></div>
            <div class="col-md-4"><label for="fecha_hasta" class="form-label small text-muted fw-bold mb-1">Hasta</label><input id="fecha_hasta" type="date" name="fecha_hasta" value="{{ $fechaHasta }}" class="form-control"></div>
            <div class="col-md-4 d-flex gap-2"><button type="submit" class="btn btn-outline-danger rounded-pill flex-fill">Generar reporte</button><a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary rounded-pill flex-fill">Mes actual</a></div>
        </form>
    </div>
</div>

<div id="contenidoReporteImprimible">
    <div id="encabezadoImpresionReporte" class="d-none d-print-block text-center mb-3">
        <h3 class="fw-bold text-danger">SALÓN MISS UÑAS VALENTINA</h3>
        <h5 id="tituloPestanaImpresion">REPORTES DE GESTIÓN</h5>
        <p class="mb-2">Período: {{ date('d/m/Y', strtotime($fechaDesde)) }} al {{ date('d/m/Y', strtotime($fechaHasta)) }}</p>
        <hr>
    </div>

    <div id="resumenIndicadores" class="row g-3 mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body"><small class="text-muted fw-bold">Ventas de productos</small><h3 class="text-danger fw-bold mb-0">Bs. {{ number_format($totalVentas, 2) }}</h3><small class="text-muted">{{ $ventas->count() }} ventas</small></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body"><small class="text-muted fw-bold">Servicios completados</small><h3 class="text-primary fw-bold mb-0">Bs. {{ number_format($totalServicios, 2) }}</h3><small class="text-muted">{{ $reservas->whereIn('estado', ['Completada', 'Finalizado'])->count() }} reservas completadas</small></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4 h-100 bg-danger text-white"><div class="card-body"><small class="text-white-50 fw-bold">Ingresos registrados</small><h3 class="text-white fw-bold mb-0">Bs. {{ number_format($ingresoTotal, 2) }}</h3><small class="text-white-50">Ventas + servicios completados</small></div></div></div>
    </div>

    <ul class="nav nav-tabs mb-3 d-print-none" id="pestanasReportes" role="tablist">
        <li class="nav-item" role="presentation"><button class="nav-link active" id="tab-ventas" data-bs-toggle="tab" data-bs-target="#panel-ventas" type="button" role="tab"><i class="bi bi-cart-check me-1"></i>Ventas y productos</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-compras" data-bs-toggle="tab" data-bs-target="#panel-compras" type="button" role="tab"><i class="bi bi-bag me-1"></i>Compras</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-servicios" data-bs-toggle="tab" data-bs-target="#panel-servicios" type="button" role="tab"><i class="bi bi-calendar-check me-1"></i>Servicios y reservas</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-promociones" data-bs-toggle="tab" data-bs-target="#panel-promociones" type="button" role="tab"><i class="bi bi-stars me-1"></i>Reservas de promociones</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-personal" data-bs-toggle="tab" data-bs-target="#panel-personal" type="button" role="tab"><i class="bi bi-people me-1"></i>Personal</button></li>
    </ul>

    <div class="tab-content" id="contenidoPestanasReportes">
        <section class="tab-pane fade show active" id="panel-ventas" role="tabpanel" aria-labelledby="tab-ventas" tabindex="0">
            <div class="row g-3 mb-4">
                <div class="col-lg-6"><div class="card border-0 shadow-sm rounded-4 reporte-grafica h-100"><div class="card-header bg-white border-0 pt-3"><h6 class="text-danger fw-bold mb-0">Ventas por mes</h6><small class="text-muted">Montos dentro del período seleccionado</small></div><div class="card-body"><div id="graficaVentasMensuales" class="grafica-contenedor" role="img" aria-label="Ventas por mes"></div></div></div></div>
                <div class="col-lg-6"><div class="card border-0 shadow-sm rounded-4 reporte-grafica h-100"><div class="card-header bg-white border-0 pt-3"><h6 class="text-danger fw-bold mb-0">Productos más vendidos</h6><small class="text-muted">Unidades vendidas en el período seleccionado</small></div><div class="card-body"><div id="graficaProductos" class="grafica-contenedor" role="img" aria-label="Productos más vendidos"></div></div></div></div>
            </div>
            <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-header bg-white"><strong>Ventas que forman el total</strong></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>N.º</th><th>Fecha</th><th>Cliente</th><th>Registrado por</th><th class="text-end">Total</th></tr></thead><tbody>
                @forelse($ventas as $venta)<tr><td>#{{ $venta->id_venta }}</td><td>{{ $venta->fecha?->format('d/m/Y') ?? $venta->fecha }}</td><td>{{ trim(($venta->cliente->nombre ?? 'Cliente ocasional').' '.($venta->cliente->apellido ?? '')) }}</td><td>{{ $venta->usuario->username ?? '—' }}</td><td class="text-end fw-bold">Bs. {{ number_format($venta->total, 2) }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-3">No hay ventas en este período.</td></tr>@endforelse
            </tbody></table></div></div>
            <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-header bg-white"><strong>Productos que forman la gráfica</strong></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Producto</th><th class="text-center">Unidades</th><th class="text-end">Recaudado</th></tr></thead><tbody>
                @forelse($topProductos as $producto)<tr><td>{{ $producto->descripcion }}</td><td class="text-center">{{ $producto->total_vendidos }}</td><td class="text-end fw-bold">Bs. {{ number_format($producto->total_recaudado, 2) }}</td></tr>@empty<tr><td colspan="3" class="text-center text-muted py-3">No hay productos vendidos en este período.</td></tr>@endforelse
            </tbody></table></div></div>
        </section>

        <section class="tab-pane fade" id="panel-compras" role="tabpanel" aria-labelledby="tab-compras" tabindex="0">
            <div class="card border-0 shadow-sm rounded-4 reporte-grafica mb-4"><div class="card-header bg-white border-0 pt-3"><h6 class="text-primary fw-bold mb-0">Compras por mes</h6><small class="text-muted">Montos registrados dentro del período seleccionado</small></div><div class="card-body"><div id="graficaComprasMensuales" class="grafica-contenedor" role="img" aria-label="Compras por mes"></div></div></div>
            <div class="card border-0 shadow-sm rounded-4"><div class="card-header bg-white"><strong>Compras que forman la gráfica</strong></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>N.º</th><th>Fecha</th><th>Empresa proveedora</th><th>Registrado por</th><th class="text-center">Productos</th><th class="text-end">Total</th><th class="text-center">Acción</th></tr></thead><tbody>
                @forelse($compras as $compra)<tr><td>#{{ $compra->id_compra }}</td><td>{{ $compra->fecha?->format('d/m/Y H:i') ?? $compra->fecha }}</td><td>{{ $compra->proveedor->nombre_empresa ?? $compra->proveedor->nombre ?? '—' }}</td><td>{{ $compra->usuario->username ?? '—' }}</td><td class="text-center">{{ $compra->detalles->sum('cantidad') }}</td><td class="text-end fw-bold">Bs. {{ number_format($compra->total, 2) }}</td><td class="text-center"><a href="{{ route('compras.comprobante', $compra->id_compra) }}" class="btn btn-outline-danger btn-sm rounded-pill"><i class="bi bi-printer-fill me-1"></i>Imprimir</a></td></tr>@empty<tr><td colspan="7" class="text-center text-muted py-3">No hay compras en este período.</td></tr>@endforelse
            </tbody></table></div></div>
        </section>

        <section class="tab-pane fade" id="panel-servicios" role="tabpanel" aria-labelledby="tab-servicios" tabindex="0">
            <div class="row g-3 mb-4">
                <div class="col-lg-6"><div class="card border-0 shadow-sm rounded-4 reporte-grafica h-100"><div class="card-header bg-white border-0 pt-3"><h6 class="text-success fw-bold mb-0">Ingresos por servicios completados</h6><small class="text-muted">Montos por mes dentro del período</small></div><div class="card-body"><div id="graficaReservasMensuales" class="grafica-contenedor" role="img" aria-label="Ingresos de servicios por mes"></div></div></div></div>
                <div class="col-lg-6"><div class="card border-0 shadow-sm rounded-4 reporte-grafica h-100"><div class="card-header bg-white border-0 pt-3"><h6 class="text-success fw-bold mb-0">Servicios más solicitados</h6><small class="text-muted">Reservas completadas en el período</small></div><div class="card-body"><div id="graficaServicios" class="grafica-contenedor" role="img" aria-label="Servicios más solicitados"></div></div></div></div>
            </div>
            <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-header bg-white"><strong>Reservas que forman los ingresos</strong></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>N.º</th><th>Fecha</th><th>Hora</th><th>Cliente</th><th>Servicio</th><th>Personal</th><th>Estado</th><th class="text-end">Importe</th></tr></thead><tbody>
                @forelse($reservas->whereIn('estado', ['Completada', 'Finalizado']) as $reserva)<tr><td>#{{ $reserva->id_reserva }}</td><td>{{ $reserva->fecha?->format('d/m/Y') ?? $reserva->fecha }}</td><td>{{ $reserva->hora?->format('H:i') ?? $reserva->hora }}</td><td>{{ trim(($reserva->cliente->nombre ?? '—').' '.($reserva->cliente->apellido ?? '')) }}</td><td>{{ $reserva->servicio->nombre ?? '—' }}</td><td>{{ $reserva->usuario->username ?? '—' }}</td><td><span class="badge bg-success">{{ $reserva->estado }}</span></td><td class="text-end fw-bold">Bs. {{ number_format($reserva->subtotal, 2) }}</td></tr>@empty<tr><td colspan="8" class="text-center text-muted py-3">No hay servicios completados en este período.</td></tr>@endforelse
            </tbody></table></div></div>
            <div class="card border-0 shadow-sm rounded-4"><div class="card-header bg-white"><strong>Servicios que forman la gráfica</strong></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Servicio</th><th class="text-center">Reservas completadas</th><th class="text-end">Importe generado</th></tr></thead><tbody>
                @forelse($topServicios as $servicio)<tr><td>{{ $servicio->nombre }}</td><td class="text-center">{{ $servicio->total_reservas }}</td><td class="text-end fw-bold">Bs. {{ number_format($servicio->total_generado, 2) }}</td></tr>@empty<tr><td colspan="3" class="text-center text-muted py-3">No hay servicios completados en este período.</td></tr>@endforelse
            </tbody></table></div></div>
        </section>

        <section class="tab-pane fade" id="panel-promociones" role="tabpanel" aria-labelledby="tab-promociones" tabindex="0">
            <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-header bg-white"><strong>Comparativa de promociones</strong><small class="d-block text-muted">Incluye también las promociones que no tuvieron reservas en el período.</small></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Promoción</th><th class="text-center">Reservas no canceladas</th><th class="text-center">Completadas</th><th class="text-center">Capacidad</th><th class="text-center">Espacios disponibles</th><th class="text-center">Ocupación</th><th class="text-end">Adelantos registrados</th></tr></thead><tbody>
                @forelse($resumenReservasPromociones as $promocion)<tr><td class="fw-semibold">{{ $promocion->nombre }}</td><td class="text-center"><span class="badge bg-danger rounded-pill">{{ $promocion->reservas_activas }}</span></td><td class="text-center">{{ $promocion->reservas_completadas }}</td><td class="text-center">{{ $promocion->cantidad_clientes }}</td><td class="text-center">{{ $promocion->plazas_disponibles }}</td><td class="text-center">{{ number_format($promocion->ocupacion_porcentaje, 1) }}%</td><td class="text-end">Bs. {{ number_format($promocion->adelantos_registrados, 2) }}</td></tr>@empty<tr><td colspan="7" class="text-center text-muted py-3">No hay promociones registradas.</td></tr>@endforelse
            </tbody></table></div></div>
            <div class="card border-0 shadow-sm rounded-4"><div class="card-header bg-white"><strong>Reservas de promociones del período</strong><small class="d-block text-muted">Detalle de reservas que respalda la comparación.</small></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>N.º</th><th>Promoción</th><th>Cliente</th><th>Fecha</th><th>Hora</th><th>Estado</th><th class="text-end">Precio</th><th class="text-end">Adelanto</th></tr></thead><tbody>
                @forelse($reservasPromociones as $reservaPromo)<tr><td>#{{ $reservaPromo->id_reserva_promocion }}</td><td>{{ $reservaPromo->promocion->nombre ?? 'Promoción eliminada' }}</td><td>{{ trim(($reservaPromo->cliente->nombre ?? '—').' '.($reservaPromo->cliente->apellido ?? '')) }}</td><td>{{ $reservaPromo->fecha?->format('d/m/Y') }}</td><td>{{ $reservaPromo->hora?->format('H:i') }}</td><td>{{ $reservaPromo->estado }}</td><td class="text-end">Bs. {{ number_format($reservaPromo->precio_promocion, 2) }}</td><td class="text-end">Bs. {{ number_format($reservaPromo->adelanto, 2) }}</td></tr>@empty<tr><td colspan="8" class="text-center text-muted py-3">No hay reservas de promociones en este período.</td></tr>@endforelse
            </tbody></table></div></div>
        </section>

        <section class="tab-pane fade" id="panel-personal" role="tabpanel" aria-labelledby="tab-personal" tabindex="0">
            <div class="card border-0 shadow-sm rounded-4 reporte-grafica mb-4"><div class="card-header bg-white border-0 pt-3"><h6 class="text-primary fw-bold mb-0">Atenciones completadas por empleado</h6><small class="text-muted">Cantidad de reservas completadas en el período</small></div><div class="card-body"><div id="graficaPersonal" class="grafica-contenedor" role="img" aria-label="Atenciones completadas por empleado"></div></div></div>
            <div class="card border-0 shadow-sm rounded-4"><div class="card-header bg-white"><strong>Atenciones que forman la gráfica</strong></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Empleado / promoción</th><th class="text-center">Atenciones completadas</th><th class="text-end">Importe generado</th><th class="text-center">Acciones</th></tr></thead><tbody>
                @forelse($desempenoPersonal as $empleado)<tr><td>{{ $empleado->username }}</td><td class="text-center">{{ $empleado->total_atenciones }}</td><td class="text-end fw-bold">Bs. {{ number_format($empleado->total_generado, 2) }}</td><td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalAtencionesEmpleado{{ $empleado->id_usuario }}"><i class="bi bi-info-circle me-1"></i>Más información</button></td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-3">No hay atenciones completadas en este período.</td></tr>@endforelse
                @foreach($promocionesAtendidas as $promocion)<tr class="table-light"><td><span class="badge bg-danger me-2">Promoción</span>{{ $promocion->nombre }}</td><td class="text-center">{{ $promocion->total_atenciones }}</td><td class="text-end fw-bold">Bs. {{ number_format($promocion->total_generado, 2) }}</td><td class="text-center text-muted">—</td></tr>@endforeach
            </tbody></table></div></div>
            @foreach($desempenoPersonal as $empleado)
                @php($atencionesEmpleado = $reservas->where('id_usuario', $empleado->id_usuario)->whereIn('estado', ['Completada', 'Finalizado']))
                <div class="modal fade" id="modalAtencionesEmpleado{{ $empleado->id_usuario }}" tabindex="-1" aria-labelledby="tituloAtencionesEmpleado{{ $empleado->id_usuario }}" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content rounded-4">
                        <div class="modal-header"><div><h5 class="modal-title text-danger fw-bold" id="tituloAtencionesEmpleado{{ $empleado->id_usuario }}">Atenciones de {{ $empleado->username }}</h5><small class="text-muted">Detalle de atenciones completadas en el período seleccionado</small></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                        <div class="modal-body"><div class="table-responsive"><table class="table table-hover align-middle"><thead class="table-light"><tr><th>N.º</th><th>Fecha</th><th>Hora</th><th>Cliente</th><th>Servicio</th><th>Estado</th><th class="text-end">Importe</th><th class="text-end">Adelanto</th></tr></thead><tbody>
                            @forelse($atencionesEmpleado as $atencion)<tr><td>#{{ $atencion->id_reserva }}</td><td>{{ $atencion->fecha?->format('d/m/Y') ?? $atencion->fecha }}</td><td>{{ $atencion->hora?->format('H:i') ?? $atencion->hora }}</td><td>{{ trim(($atencion->cliente->nombre ?? '—').' '.($atencion->cliente->apellido ?? '')) }}</td><td>{{ $atencion->servicio->nombre ?? '—' }}</td><td><span class="badge bg-success">{{ $atencion->estado }}</span></td><td class="text-end">Bs. {{ number_format((float) $atencion->subtotal, 2) }}</td><td class="text-end">Bs. {{ number_format((float) $atencion->adelanto, 2) }}</td></tr>@empty<tr><td colspan="8" class="text-center text-muted py-3">No hay atenciones para mostrar.</td></tr>@endforelse
                        </tbody></table></div></div>
                        <div class="modal-footer"><button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cerrar</button></div>
                    </div></div>
                </div>
            @endforeach
        </section>
    </div>
</div>

<style>
.grafica-contenedor svg{display:block;width:100%;height:auto;min-height:250px}.grafica-contenedor .barra-label{font-size:12px;fill:#495057}.grafica-contenedor .barra-valor{font-size:12px;fill:#343a40;font-weight:600}
@media print{
    @page{size:A4 portrait;margin:10mm}html,body{width:100%!important;height:auto!important;margin:0!important;padding:0!important;overflow:visible!important;background:#fff!important}
    body.imprimiendo-documento>*:not(#contenidoReporteImprimible){display:none!important}body.imprimiendo-documento>#contenidoReporteImprimible{display:block!important}
    #contenidoReporteImprimible{width:100%!important;color:#111!important;font-size:9pt!important}#contenidoReporteImprimible .d-print-none{display:none!important}#contenidoReporteImprimible .d-print-block{display:block!important}
    #contenidoReporteImprimible #resumenIndicadores,#contenidoReporteImprimible .nav-tabs{display:none!important}
    #contenidoReporteImprimible .tab-content>.tab-pane{display:none!important;opacity:1!important;visibility:visible!important}
    #contenidoReporteImprimible .tab-content>.tab-pane.active{display:block!important;opacity:1!important;visibility:visible!important}
    #contenidoReporteImprimible .card{box-shadow:none!important;border:1px solid #ccc!important;break-inside:avoid;page-break-inside:avoid}
    #contenidoReporteImprimible .card-header{background:#f3f3f3!important}#contenidoReporteImprimible .table-responsive{overflow:visible!important}#contenidoReporteImprimible table{font-size:8.5pt!important}
    #contenidoReporteImprimible thead{display:table-header-group}#contenidoReporteImprimible tr{break-inside:avoid;page-break-inside:avoid}#contenidoReporteImprimible .grafica-contenedor svg{max-height:75mm;print-color-adjust:exact;-webkit-print-color-adjust:exact}
}
</style>
<script>
(() => {
    // Los modales deben estar fuera de las pestañas para evitar que sus
    // contenedores ocultos o con opacidad tapen el contenido del diálogo.
    document.querySelectorAll('.modal[id^="modalAtencionesEmpleado"]').forEach(modal => document.body.appendChild(modal));

    const datosMes = @json($graficaComparativa);
    const productos = @json($topProductos->map(fn ($item) => ['nombre' => $item->descripcion, 'valor' => (int) $item->total_vendidos])->values());
    const servicios = @json($topServicios->map(fn ($item) => ['nombre' => $item->nombre, 'valor' => (int) $item->total_reservas])->values());
    const personal = @json($desempenoPersonal->map(fn ($item) => ['nombre' => $item->username, 'valor' => (int) $item->total_atenciones])->values());
    const moneda = valor => `Bs. ${Number(valor).toLocaleString('es-BO', {minimumFractionDigits: 0, maximumFractionDigits: 0})}`;
    const escapar = valor => String(valor).replace(/[&<>"']/g, caracter => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[caracter]));

    function barrasMensuales(id, clave, color) {
        const contenedor = document.getElementById(id);
        if (!contenedor) return;
        if (!datosMes.length) { contenedor.innerHTML = '<p class="text-center text-muted py-5">Sin datos para graficar.</p>'; return; }
        const ancho=720, alto=310, margen={izquierda:88,derecha:20,arriba:18,abajo:58}, plotAncho=ancho-margen.izquierda-margen.derecha, plotAlto=alto-margen.arriba-margen.abajo;
        const maximo=Math.max(0,...datosMes.map(d=>Number(d[clave]))), escala=maximo||1, grupo=plotAncho/datosMes.length, anchoBarra=Math.min(58,grupo*.58), y=v=>margen.arriba+plotAlto-(Number(v)/escala)*plotAlto;
        let svg=`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${ancho} ${alto}" role="img">`;
        for(let i=0;i<=4;i++){const valor=escala*i/4, py=y(valor);svg+=`<line x1="${margen.izquierda}" y1="${py}" x2="${ancho-margen.derecha}" y2="${py}" stroke="#e9ecef"/><text x="${margen.izquierda-8}" y="${py+4}" text-anchor="end" font-size="11" fill="#6c757d">${moneda(valor)}</text>`;}
        datosMes.forEach((dato,i)=>{const centro=margen.izquierda+grupo*(i+.5), valor=Number(dato[clave]), altura=plotAlto-(y(valor)-margen.arriba);svg+=`<rect x="${centro-anchoBarra/2}" y="${y(valor)}" width="${anchoBarra}" height="${Math.max(0,altura)}" rx="5" fill="${color}"><title>${escapar(dato.etiqueta)}: ${moneda(valor)}</title></rect><text x="${centro}" y="${alto-24}" text-anchor="middle" font-size="11" fill="#6c757d">${escapar(dato.etiqueta)}</text>`;});
        contenedor.innerHTML=svg+'</svg>';
    }

    function barrasRanking(id, filas, color, sufijo='') {
        const contenedor=document.getElementById(id);
        if(!contenedor)return;
        if(!filas.length){contenedor.innerHTML='<p class="text-center text-muted py-5">Sin datos para graficar.</p>';return;}
        const ancho=720, alto=Math.max(260,filas.length*48+35), margen={izquierda:Math.min(250,Math.max(125,...filas.map(f=>f.nombre.length*7))),derecha:90,arriba:15,abajo:15}, plotAncho=ancho-margen.izquierda-margen.derecha, maximo=Math.max(1,...filas.map(f=>Number(f.valor))), altoFila=(alto-margen.arriba-margen.abajo)/filas.length;
        let svg=`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${ancho} ${alto}" role="img">`;
        filas.forEach((fila,i)=>{const y=margen.arriba+i*altoFila+altoFila*.2, anchoBarra=plotAncho*Number(fila.valor)/maximo, etiqueta=fila.nombre.length>34?fila.nombre.slice(0,31)+'…':fila.nombre;svg+=`<text class="barra-label" x="${margen.izquierda-10}" y="${y+altoFila*.42}" text-anchor="end">${escapar(etiqueta)}</text><rect x="${margen.izquierda}" y="${y}" width="${Math.max(anchoBarra,1)}" height="${altoFila*.58}" rx="5" fill="${color}"><title>${escapar(fila.nombre)}: ${fila.valor}${sufijo}</title></rect><text class="barra-valor" x="${margen.izquierda+anchoBarra+8}" y="${y+altoFila*.42}">${fila.valor}${sufijo}</text>`;});
        contenedor.innerHTML=svg+'</svg>';
    }

    barrasMensuales('graficaVentasMensuales','ventas','#dc3545');
    barrasMensuales('graficaComprasMensuales','compras','#0d6efd');
    barrasMensuales('graficaReservasMensuales','reservas','#198754');
    barrasRanking('graficaProductos',productos,'#dc3545',' u.');
    barrasRanking('graficaServicios',servicios,'#198754',' citas');
    barrasRanking('graficaPersonal',personal,'#0d6efd',' atenciones');
})();

(() => {
    const reporte=document.getElementById('contenidoReporteImprimible');let padreOriginal,siguienteOriginal;
    window.addEventListener('beforeprint',()=>{
        const pestanaActiva=reporte.querySelector('.tab-content>.tab-pane.active');
        const botonActivo=pestanaActiva ? reporte.querySelector(`[data-bs-target="#${pestanaActiva.id}"]`) : null;
        document.getElementById('tituloPestanaImpresion').textContent=botonActivo ? botonActivo.textContent.trim() : 'Reporte de gestión';
        padreOriginal=reporte.parentNode;siguienteOriginal=reporte.nextSibling;document.body.appendChild(reporte);document.body.classList.add('imprimiendo-documento');
    });
    window.addEventListener('afterprint',()=>{if(padreOriginal)padreOriginal.insertBefore(reporte,siguienteOriginal);document.body.classList.remove('imprimiendo-documento');});
})();
</script>
@endsection
