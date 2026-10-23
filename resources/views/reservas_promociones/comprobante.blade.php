@extends('adm_plantilla')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 d-print-none">
    <div><h4 class="m-0 text-danger fw-bold"><i class="bi bi-receipt me-2"></i>Comprobante de reserva de promoción #{{ $reserva->id_reserva_promocion }}</h4><small class="text-muted">Documento de respaldo de la reserva.</small></div>
    <div class="d-flex gap-2"><button type="button" onclick="window.print()" class="btn btn-danger rounded-pill"><i class="bi bi-printer-fill me-1"></i>Imprimir comprobante</button><a href="{{ route('reservas.promociones.index') }}" class="btn btn-outline-secondary rounded-pill"><i class="bi bi-arrow-left me-1"></i>Volver</a></div>
</div>

<div id="facturaReservaPromocion" class="card border-0 shadow rounded-4 mx-auto" style="max-width:700px">
    <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4 border-bottom pb-3"><h3 class="fw-bold text-danger mb-1">MISS UÑAS VALENTINA</h3><p class="text-muted mb-1 small">Salón de Belleza y Cuidado Personal</p><p class="text-muted mb-2 small"><i class="bi bi-geo-alt-fill text-danger"></i> Av. Principal #123 - Tel: 72541236</p><span class="badge bg-danger px-3 py-2 fs-6 rounded-pill">COMPROBANTE DE RESERVA #{{ str_pad($reserva->id_reserva_promocion, 6, '0', STR_PAD_LEFT) }}</span></div>
        <div class="row g-2 mb-4 small">
            <div class="col-6"><strong class="text-secondary">Cliente:</strong><div>{{ $reserva->cliente->nombre ?? 'Cliente' }} {{ $reserva->cliente->apellido ?? '' }}</div></div>
            <div class="col-6 text-end"><strong class="text-secondary">Fecha reservada:</strong><div>{{ $reserva->fecha->format('d/m/Y') }}</div></div>
            <div class="col-6"><strong class="text-secondary">Teléfono:</strong><div>{{ $reserva->cliente->telefono ?? 'N/A' }}</div></div>
            <div class="col-6 text-end"><strong class="text-secondary">Hora:</strong><div>{{ $reserva->hora?->format('H:i') }}</div></div>
            <div class="col-6"><strong class="text-secondary">Estado:</strong><div>{{ $reserva->estado }}</div></div>
            <div class="col-6 text-end"><strong class="text-secondary">Promoción:</strong><div>{{ $reserva->promocion->nombre ?? 'Promoción' }}</div></div>
        </div>
        <div class="table-responsive mb-4"><table class="table table-sm align-middle mb-0">
            <thead class="table-light"><tr><th>Servicios incluidos</th><th class="text-end">Precio</th></tr></thead>
            <tbody>@forelse($reserva->promocion?->servicios ?? [] as $servicio)<tr><td>{{ $servicio->nombre }}</td><td class="text-end text-muted">Incluido</td></tr>@empty<tr><td>{{ $reserva->promocion->nombre ?? 'Promoción' }}</td><td class="text-end text-muted">—</td></tr>@endforelse</tbody>
            <tfoot class="border-top"><tr><td class="text-end fw-bold pt-3">Total promoción:</td><td class="text-end fw-bold pt-3">Bs. {{ number_format((float)$reserva->precio_promocion, 2) }}</td></tr><tr><td class="text-end fw-bold">Adelanto:</td><td class="text-end text-success fw-bold">Bs. {{ number_format((float)$reserva->adelanto, 2) }}</td></tr><tr><td class="text-end fw-bold fs-5">Saldo pendiente:</td><td class="text-end fw-bold fs-5 text-danger">Bs. {{ number_format(max(0, (float)$reserva->precio_promocion - (float)$reserva->adelanto), 2) }}</td></tr></tfoot>
        </table></div>
        <div class="text-center text-muted small border-top pt-3"><p class="mb-1">¡Gracias por tu preferencia!</p><p class="mb-0 fst-italic">"Realzando tu belleza y elegancia"</p></div>
    </div>
</div>
<style>
@media print {
    @page { size: A4 portrait; margin: 12mm; }
    html, body { width: 100% !important; height: auto !important; margin: 0 !important; padding: 0 !important; overflow: visible !important; background: #fff !important; }
    body.imprimiendo-factura-promocion > *:not(#facturaReservaPromocion) { display: none !important; }
    body.imprimiendo-factura-promocion > #facturaReservaPromocion { display: block !important; }
    #facturaReservaPromocion { position: static !important; width: 100% !important; max-width: 186mm !important; margin: 0 auto !important; color: #111 !important; box-shadow: none !important; border: 1px solid #bbb !important; }
    #facturaReservaPromocion .card-body { padding: 8mm !important; }
    #facturaReservaPromocion .table-responsive { overflow: visible !important; }
    #facturaReservaPromocion tr { break-inside: avoid; page-break-inside: avoid; }
    #facturaReservaPromocion .table { font-size: 10pt; }
}
</style>
<script>
(() => {
    const factura = document.getElementById('facturaReservaPromocion');
    let padreOriginal, siguienteOriginal;
    window.addEventListener('beforeprint', () => { padreOriginal = factura.parentNode; siguienteOriginal = factura.nextSibling; document.body.appendChild(factura); document.body.classList.add('imprimiendo-factura-promocion'); });
    window.addEventListener('afterprint', () => { if (padreOriginal) padreOriginal.insertBefore(factura, siguienteOriginal); document.body.classList.remove('imprimiendo-factura-promocion'); });
})();
</script>
@endsection
