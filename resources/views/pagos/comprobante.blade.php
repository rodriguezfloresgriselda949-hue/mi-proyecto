@extends('adm_plantilla')

@section('content')
@php($bruto = round((float)$pago->total_servicios * (float)$pago->porcentaje / 100, 2))
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 d-print-none">
    <div><h4 class="m-0 text-danger fw-bold"><i class="bi bi-receipt me-2"></i>Comprobante de pago #{{ $pago->id_pago }}</h4><small class="text-muted">Detalle imprimible del pago al empleado.</small></div>
    <div class="d-flex gap-2"><button type="button" onclick="window.print()" class="btn btn-danger rounded-pill"><i class="bi bi-printer-fill me-1"></i>Imprimir comprobante</button><a href="{{ route('pagos.index', ['empleado' => $pago->id_usuario]) }}" class="btn btn-outline-secondary rounded-pill"><i class="bi bi-arrow-left me-1"></i>Volver</a></div>
</div>

<div id="comprobantePagoEmpleado" class="card border-0 shadow rounded-4 mx-auto" style="max-width:800px">
    <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4 border-bottom pb-3">
            <h3 class="fw-bold text-danger mb-1">MISS UÑAS VALENTINA</h3>
            <p class="text-muted mb-1 small">Salón de Belleza y Cuidado Personal</p>
            <span class="badge bg-danger px-3 py-2 fs-6 rounded-pill">COMPROBANTE DE PAGO #{{ str_pad($pago->id_pago, 6, '0', STR_PAD_LEFT) }}</span>
        </div>

        <div class="row g-2 mb-4 small">
            <div class="col-6"><strong class="text-secondary">Empleado:</strong><div>{{ $pago->empleado->username ?? 'Empleado' }}</div></div>
            <div class="col-6 text-end"><strong class="text-secondary">Fecha de pago:</strong><div>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</div></div>
            <div class="col-6"><strong class="text-secondary">Modalidad:</strong><div>{{ $pago->empleado->pago ?? 'No registrada' }}</div></div>
            <div class="col-6 text-end"><strong class="text-secondary">Porcentaje:</strong><div>{{ number_format((float)$pago->porcentaje, 2) }}%</div></div>
            <div class="col-12"><strong class="text-secondary">Período pagado:</strong><div>{{ \Carbon\Carbon::parse($pago->fecha_inicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($pago->fecha_fin)->format('d/m/Y') }}</div></div>
        </div>

        <h6 class="fw-bold text-danger mb-2">Servicios concluidos del período</h6>
        <div class="table-responsive mb-4"><table class="table table-sm align-middle mb-0">
            <thead class="table-light"><tr><th>Fecha</th><th class="text-end">Importe</th></tr></thead>
            <tbody>
                @forelse($reservas as $reserva)
                    <tr><td>{{ \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') }}</td><td class="text-end">Bs. {{ number_format((float)$reserva->subtotal, 2) }}</td></tr>
                @empty
                    <tr><td colspan="2" class="text-center text-muted">No hay detalle de servicios para este período.</td></tr>
                @endforelse
            </tbody>
        </table></div>

        <h6 class="fw-bold text-danger mb-2">Adelantos descontados</h6>
        <div class="table-responsive mb-4"><table class="table table-sm align-middle mb-0">
            <thead class="table-light"><tr><th>Fecha</th><th>Motivo</th><th class="text-end">Monto</th></tr></thead>
            <tbody>
                @forelse($adelantos as $adelanto)
                    <tr><td>{{ \Carbon\Carbon::parse($adelanto->fecha)->format('d/m/Y') }}</td><td>{{ $adelanto->motivo }}</td><td class="text-end">Bs. {{ number_format((float)$adelanto->monto, 2) }}</td></tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted">No se descontaron adelantos.</td></tr>
                @endforelse
            </tbody>
        </table></div>

        <div class="table-responsive"><table class="table table-sm mb-0"><tbody>
            <tr><th class="text-end">Total servicios:</th><td class="text-end">Bs. {{ number_format((float)$pago->total_servicios, 2) }}</td></tr>
            <tr><th class="text-end">Pago bruto ({{ number_format((float)$pago->porcentaje, 2) }}%):</th><td class="text-end">Bs. {{ number_format($bruto, 2) }}</td></tr>
            <tr><th class="text-end">Adelantos descontados:</th><td class="text-end text-warning">- Bs. {{ number_format((float)$pago->total_adelantos, 2) }}</td></tr>
            <tr class="border-top"><th class="text-end fs-5">Monto pagado:</th><td class="text-end fw-bold fs-5 text-success">Bs. {{ number_format((float)$pago->monto_pagado, 2) }}</td></tr>
        </tbody></table></div>

        <div class="text-center text-muted small border-top pt-3 mt-4"><p class="mb-1">Constancia de pago al empleado.</p><p class="mb-0">MISS UÑAS VALENTINA</p></div>
        <div class="row justify-content-center mt-5 pt-4">
            <div class="col-8 col-md-6 text-center">
                <div style="border-top:1px solid #555;padding-top:8px"></div>
                <strong>Firma del empleado</strong>
                <div>{{ $pago->empleado->username ?? 'Empleado' }}</div>
                <small class="text-muted">Documento de identidad: ____________________</small>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    @page { size: A4 portrait; margin: 12mm; }
    html, body { width: 100% !important; height: auto !important; margin: 0 !important; padding: 0 !important; overflow: visible !important; background: #fff !important; }
    body.imprimiendo-comprobante-pago > *:not(#comprobantePagoEmpleado) { display: none !important; }
    body.imprimiendo-comprobante-pago > #comprobantePagoEmpleado { display: block !important; }
    #comprobantePagoEmpleado { position: static !important; width: 100% !important; max-width: 186mm !important; margin: 0 auto !important; color: #111 !important; box-shadow: none !important; border: 1px solid #bbb !important; }
    #comprobantePagoEmpleado .card-body { padding: 8mm !important; }
    #comprobantePagoEmpleado .table-responsive { overflow: visible !important; }
    #comprobantePagoEmpleado tr { break-inside: avoid; page-break-inside: avoid; }
    #comprobantePagoEmpleado .table { font-size: 9pt; }
    #comprobantePagoEmpleado .row.justify-content-center { break-inside: avoid; page-break-inside: avoid; }
}
</style>
<script>
(() => {
    const comprobante = document.getElementById('comprobantePagoEmpleado');
    let padreOriginal, siguienteOriginal;
    window.addEventListener('beforeprint', () => { padreOriginal = comprobante.parentNode; siguienteOriginal = comprobante.nextSibling; document.body.appendChild(comprobante); document.body.classList.add('imprimiendo-comprobante-pago'); });
    window.addEventListener('afterprint', () => { if (padreOriginal) padreOriginal.insertBefore(comprobante, siguienteOriginal); document.body.classList.remove('imprimiendo-comprobante-pago'); });
})();
</script>
@endsection
