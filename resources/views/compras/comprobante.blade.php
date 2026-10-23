@extends('adm_plantilla')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 d-print-none">
    <div>
        <h4 class="m-0 text-danger fw-bold"><i class="bi bi-receipt me-2"></i>Comprobante de compra #{{ $compra->id_compra }}</h4>
        <small class="text-muted">Detalle imprimible de la compra registrada.</small>
    </div>
    <div class="d-flex gap-2">
        <button type="button" onclick="window.print()" class="btn btn-danger rounded-pill"><i class="bi bi-printer-fill me-1"></i>Imprimir comprobante</button>
        <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary rounded-pill"><i class="bi bi-arrow-left me-1"></i>Volver a reportes</a>
    </div>
</div>

<div id="comprobanteCompra" class="card border-0 shadow rounded-4 mx-auto" style="max-width:850px">
    <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4 border-bottom pb-3">
            <h3 class="fw-bold text-danger mb-1">MISS UÑAS VALENTINA</h3>
            <p class="text-muted mb-1 small">Salón de Belleza y Cuidado Personal</p>
            <span class="badge bg-danger px-3 py-2 fs-6 rounded-pill">COMPROBANTE DE COMPRA #{{ str_pad($compra->id_compra, 6, '0', STR_PAD_LEFT) }}</span>
        </div>

        <div class="row g-3 mb-4 small">
            <div class="col-sm-6"><strong class="text-secondary">Proveedor:</strong><div>{{ $compra->proveedor->nombre_empresa ?? $compra->proveedor->nombre ?? 'Proveedor no disponible' }}</div></div>
            <div class="col-sm-6 text-sm-end"><strong class="text-secondary">Fecha:</strong><div>{{ $compra->fecha?->format('d/m/Y H:i') ?? $compra->fecha }}</div></div>
            <div class="col-sm-6"><strong class="text-secondary">Registrado por:</strong><div>{{ $compra->usuario->username ?? 'Usuario no disponible' }}</div></div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light"><tr><th>Producto</th><th class="text-center">Cantidad</th><th class="text-end">Precio unitario</th><th class="text-end">Subtotal</th></tr></thead>
                <tbody>
                    @forelse($compra->detalles as $detalle)
                        <tr>
                            <td>{{ $detalle->producto->descripcion ?? 'Producto eliminado' }}</td>
                            <td class="text-center">{{ $detalle->cantidad }}</td>
                            <td class="text-end">Bs. {{ number_format((float) $detalle->precio, 2) }}</td>
                            <td class="text-end">Bs. {{ number_format((float) $detalle->subtotal, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">No hay productos en esta compra.</td></tr>
                    @endforelse
                </tbody>
                <tfoot><tr><th colspan="3" class="text-end">Total</th><th class="text-end text-danger">Bs. {{ number_format((float) $compra->total, 2) }}</th></tr></tfoot>
            </table>
        </div>
        <p class="text-center text-muted small mt-4 mb-0">Comprobante generado desde el sistema de gestión.</p>
    </div>
</div>

<style>
@media print {
    @page { size: A4 portrait; margin: 12mm; }
    body.imprimiendo-comprobante-compra > *:not(#comprobanteCompra) { display: none !important; }
    body.imprimiendo-comprobante-compra > #comprobanteCompra { display: block !important; }
    #comprobanteCompra { position: static !important; width: 100% !important; max-width: 186mm !important; margin: 0 auto !important; color: #111 !important; box-shadow: none !important; border: 1px solid #bbb !important; }
    #comprobanteCompra .card-body { padding: 8mm !important; }
    #comprobanteCompra .table-responsive { overflow: visible !important; }
    #comprobanteCompra tr { break-inside: avoid; page-break-inside: avoid; }
}
</style>
<script>
(() => {
    const comprobante = document.getElementById('comprobanteCompra');
    let padreOriginal, siguienteOriginal;
    window.addEventListener('beforeprint', () => {
        padreOriginal = comprobante.parentNode;
        siguienteOriginal = comprobante.nextSibling;
        document.body.appendChild(comprobante);
        document.body.classList.add('imprimiendo-comprobante-compra');
    });
    window.addEventListener('afterprint', () => {
        if (padreOriginal) padreOriginal.insertBefore(comprobante, siguienteOriginal);
        document.body.classList.remove('imprimiendo-comprobante-compra');
    });
})();
</script>
@endsection
