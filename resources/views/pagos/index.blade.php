@extends('adm_plantilla')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h4 class="m-0 text-danger fw-bold"><i class="bi bi-cash-coin me-2"></i>Pagos al personal</h4><small class="text-muted">El porcentaje de pago depende de la experiencia del empleado y se descuentan los adelantos del período.</small></div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white"><strong><i class="bi bi-exclamation-circle text-warning me-1"></i>Pagos pendientes</strong><small class="d-block text-muted">Empleados con saldo por pagar en su período actual.</small></div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Empleado</th><th>Período</th><th class="text-end">Saldo pendiente</th><th></th></tr></thead><tbody>
        @forelse($pendientesPago as $pendiente)<tr><td class="fw-semibold">{{ $pendiente['empleado']->username }}</td><td>{{ $pendiente['periodo']['etiqueta'] }}</td><td class="text-end fw-bold text-danger">Bs. {{ number_format($pendiente['monto'], 2) }}</td><td class="text-end"><a class="btn btn-outline-danger btn-sm rounded-pill" href="{{ route('pagos.index', ['empleado' => $pendiente['empleado']->id_usuario]) }}">Revisar pago</a></td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-3">No hay saldos pendientes de pago.</td></tr>@endforelse
    </tbody></table></div>
</div>

@if(!$empleado)
    <div class="row g-3">
        @forelse($empleados as $persona)
            <div class="col-md-6 col-xl-4"><div class="card h-100 border-0 shadow-sm rounded-4"><div class="card-body text-center p-4">
                @if($persona->foto)<img src="{{ asset('empleados/' . $persona->foto) }}" class="rounded-circle border mb-3" width="82" height="82" style="object-fit:cover">@else <i class="bi bi-person-circle fs-1 text-secondary d-block mb-2"></i>@endif
                <h5 class="mb-1">{{ $persona->username }}</h5><span class="badge bg-secondary-subtle text-secondary rounded-pill">Pago {{ $persona->pago ?: 'Mensual' }}</span> <span class="badge bg-primary-subtle text-primary rounded-pill">{{ $persona->experiencia }}%</span>
                <a class="btn btn-danger rounded-pill w-100 mt-3" href="{{ route('pagos.index', ['empleado' => $persona->id_usuario]) }}"><i class="bi bi-calculator me-1"></i>Ver cálculo de pago</a>
            </div></div></div>
        @empty <p class="text-muted">No hay empleados activos registrados.</p>
        @endforelse
    </div>
@else
    <a href="{{ route('pagos.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill mb-3"><i class="bi bi-arrow-left me-1"></i>Empleados</a>
    <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-4 d-flex align-items-center gap-3">
        @if($empleado->foto)<img src="{{ asset('empleados/' . $empleado->foto) }}" class="rounded-circle border" width="65" height="65" style="object-fit:cover">@endif
        <div><h5 class="m-0">{{ $empleado->username }}</h5><small class="text-muted">Modalidad: <strong>{{ $empleado->pago ?: 'Mensual' }}</strong> · Experiencia: <strong>{{ $detalle['porcentaje'] }}%</strong> · Período: {{ $detalle['periodo']['etiqueta'] }}</small></div>
    </div></div>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Servicios concluidos</small><h4 class="text-danger mb-0">Bs. {{ number_format($detalle['totalServicios'], 2) }}</h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">{{ $detalle['porcentaje'] }}% a pagar</small><h4 class="text-primary mb-0">Bs. {{ number_format($detalle['pagoBruto'], 2) }}</h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Adelantos</small><h4 class="text-warning mb-0">- Bs. {{ number_format($detalle['totalAdelantos'], 2) }}</h4></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Pago neto</small><h4 class="text-success mb-0">Bs. {{ number_format($detalle['montoPagar'], 2) }}</h4></div></div></div>
    </div>
    <div class="d-flex gap-2 mb-4">
        @if(!$detalle['pagoRegistrado'])
            <button class="btn btn-outline-warning rounded-pill" data-bs-toggle="modal" data-bs-target="#modalAdelanto"><i class="bi bi-wallet2 me-1"></i>Registrar adelanto</button>
            <form method="POST" action="{{ route('pagos.registrar') }}" onsubmit="return confirm('¿Registrar el pago final de este período?')">@csrf<input type="hidden" name="id_usuario" value="{{ $empleado->id_usuario }}"><button class="btn btn-success rounded-pill"><i class="bi bi-check2-circle me-1"></i>Registrar pago de Bs. {{ number_format($detalle['montoPagar'], 2) }}</button></form>
        @else <div class="alert alert-success py-2 mb-0 d-flex align-items-center gap-3 flex-wrap"><span><i class="bi bi-check-circle-fill me-1"></i>Pago registrado el {{ \Carbon\Carbon::parse($detalle['pagoRegistrado']->fecha_pago)->format('d/m/Y') }}: Bs. {{ number_format($detalle['pagoRegistrado']->monto_pagado, 2) }}</span><a class="btn btn-outline-success btn-sm rounded-pill" href="{{ route('pagos.comprobante', $detalle['pagoRegistrado']->id_pago) }}"><i class="bi bi-printer-fill me-1"></i>Imprimir comprobante</a></div>
        @endif
    </div>
    <div class="row g-4">
        <div class="col-lg-7"><div class="card border-0 shadow-sm"><div class="card-header bg-white"><strong>Servicios concluidos</strong></div><div class="table-responsive"><table class="table mb-0"><thead class="table-light"><tr><th>Fecha</th><th>Cliente</th><th>Servicio</th><th class="text-end">Total</th></tr></thead><tbody>@forelse($detalle['reservas'] as $reserva)<tr><td>{{ \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') }}</td><td>{{ $reserva->cliente->nombre ?? 'Cliente' }} {{ $reserva->cliente->apellido ?? '' }}</td><td>{{ $reserva->servicio->nombre ?? 'Servicio' }}</td><td class="text-end">Bs. {{ number_format($reserva->subtotal, 2) }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-3">No hay servicios finalizados en este período.</td></tr>@endforelse</tbody></table></div></div></div>
        <div class="col-lg-5"><div class="card border-0 shadow-sm"><div class="card-header bg-white"><strong>Adelantos pendientes de descuento</strong></div><div class="table-responsive"><table class="table mb-0"><thead class="table-light"><tr><th>Fecha</th><th>Motivo</th><th class="text-end">Monto</th></tr></thead><tbody>@forelse($detalle['adelantos'] as $adelanto)<tr><td>{{ \Carbon\Carbon::parse($adelanto->fecha)->format('d/m/Y') }}</td><td>{{ $adelanto->motivo }}</td><td class="text-end">Bs. {{ number_format($adelanto->monto, 2) }}</td></tr>@empty<tr><td colspan="3" class="text-center text-muted py-3">No hay adelantos.</td></tr>@endforelse</tbody></table></div></div></div>
    </div>
    <div class="modal fade" id="modalAdelanto" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form method="POST" action="{{ route('adelantos.store') }}" class="modal-content">@csrf<input type="hidden" name="id_usuario" value="{{ $empleado->id_usuario }}"><div class="modal-header bg-warning"><h5 class="modal-title">Registrar adelanto · {{ $empleado->username }}</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="form-label">Monto (Bs.)</label><input class="form-control mb-3" type="number" name="monto" step="1" min="0.01" required><label class="form-label">Motivo</label><textarea class="form-control" name="motivo" maxlength="255" required></textarea></div><div class="modal-footer"><button class="btn btn-warning">Guardar adelanto</button></div></form></div></div>
@endif

<div class="card border-0 shadow-sm mt-4"><div class="card-header bg-white"><strong>Últimos pagos registrados</strong></div><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>Empleado</th><th>Período</th><th>Servicios</th><th>Adelantos</th><th>Pagado</th><th>Fecha</th><th>Comprobante</th></tr></thead><tbody>@forelse($pagos as $pago)<tr><td>{{ $pago->empleado->username ?? 'Empleado eliminado' }}</td><td>{{ \Carbon\Carbon::parse($pago->fecha_inicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($pago->fecha_fin)->format('d/m/Y') }}</td><td>Bs. {{ number_format($pago->total_servicios, 2) }}</td><td>Bs. {{ number_format($pago->total_adelantos, 2) }}</td><td class="fw-bold text-success">Bs. {{ number_format($pago->monto_pagado, 2) }}</td><td>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</td><td><a class="btn btn-outline-danger btn-sm rounded-pill" href="{{ route('pagos.comprobante', $pago->id_pago) }}"><i class="bi bi-printer-fill me-1"></i>Ver comprobante</a></td></tr>@empty<tr><td colspan="7" class="text-center text-muted py-3">Aún no hay pagos registrados.</td></tr>@endforelse</tbody></table></div></div>
@endsection

@section('scripts')
<script>
    // El panel principal usa z-index; al llevar el modal a body queda sobre su fondo oscuro de Bootstrap.
    document.addEventListener('DOMContentLoaded', function () {
        const modalAdelanto = document.getElementById('modalAdelanto');
        if (modalAdelanto) document.body.appendChild(modalAdelanto);
    });
</script>
@endsection
