@php
    $layout = (strtolower(session('rol')) === 'administrador') ? 'adm_plantilla' : 'empleado_plantilla';
@endphp

@extends($layout)

@section('content')

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div>
            <h4 class="m-0 text-danger fw-bold"><i class="bi bi-receipt me-2"></i> Recibo de Venta #{{ $venta->id_venta }}</h4>
            <small class="text-muted">Detalles de la transacción y comprobante imprimible</small>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print();" class="btn btn-danger rounded-pill shadow-sm">
                <i class="bi bi-printer-fill me-1"></i> Imprimir Comprobante
            </button>
            <a href="{{ route('ventas.create') }}" class="btn btn-outline-danger rounded-pill">
                <i class="bi bi-cart-plus me-1"></i> Nueva Venta
            </a>
            <a href="{{ strtolower(session('rol')) === 'administrador' ? route('ventas.index') : route('empleado.dashboard') }}" class="btn btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left me-1"></i> {{ strtolower(session('rol')) === 'administrador' ? 'Historial' : 'Volver al Panel' }}
            </a>
        </div>
    </div>

    <!-- Comprobante Imprimible -->
    <div class="card border-0 shadow rounded-4 mx-auto" style="max-width: 650px;" id="seccionRecibo">
        <div class="card-body p-4 p-md-5">
            <!-- Encabezado del Recibo -->
            <div class="text-center mb-4 border-bottom pb-3">
                <h3 class="fw-bold text-danger mb-1">MISS UÑAS VALENTINA</h3>
                <p class="text-muted mb-1 small">Salón de Belleza y Cuidado Personal</p>
                <p class="text-muted mb-2 small"><i class="bi bi-geo-alt-fill text-danger"></i> Av. Principal #123 - Tel: 72541236</p>
                <span class="badge bg-danger px-3 py-2 fs-6 rounded-pill">
                    NOTA DE VENTA #{{ str_pad($venta->id_venta, 6, '0', STR_PAD_LEFT) }}
                </span>
            </div>

            <!-- Datos del Cliente y Venta -->
            <div class="row g-2 mb-4 small">
                <div class="col-6">
                    <strong class="text-secondary">Cliente:</strong> 
                    <div>{{ $venta->cliente->nombre ?? 'Cliente Ocasional' }} {{ $venta->cliente->apellido ?? '' }}</div>
                </div>
                <div class="col-6 text-end">
                    <strong class="text-secondary">Fecha y Hora:</strong> 
                    <div>{{ is_object($venta->fecha) ? $venta->fecha->format('d/m/Y') : $venta->fecha }}</div>
                </div>
                <div class="col-6">
                    <strong class="text-secondary">Teléfono:</strong> 
                    <div>{{ $venta->cliente->telefono ?? 'N/A' }}</div>
                </div>
                <div class="col-6 text-end">
                    <strong class="text-secondary">Atendido por:</strong> 
                    <div>{{ $venta->usuario->username ?? 'Cajero' }}</div>
                </div>
            </div>

            <!-- Tabla de Detalles -->
            <div class="table-responsive mb-4">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Cant.</th>
                            <th>Descripción</th>
                            <th class="text-end">P. Unit</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($venta->detalles as $det)
                            <tr>
                                <td class="fw-bold">{{ $det->cantidad }}</td>
                                <td>
                                    {{ $det->producto->descripcion ?? 'Producto' }}
                                    @if(isset($det->producto->marca))
                                        <small class="text-muted d-block">Marca: {{ $det->producto->marca->nombre }}</small>
                                    @endif
                                </td>
                                <td class="text-end">Bs. {{ number_format($det->precioventa, 2) }}</td>
                                <td class="text-end fw-bold">Bs. {{ number_format($det->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-top">
                        <tr>
                            <td colspan="3" class="text-end fw-bold fs-5 pt-3">TOTAL A PAGAR:</td>
                            <td class="text-end fw-bold fs-5 text-danger pt-3">Bs. {{ number_format($venta->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Pie de Recibo -->
            <div class="text-center text-muted small border-top pt-3">
                <p class="mb-1">¡Gracias por tu preferencia!</p>
                <p class="mb-0 fst-italic">"Realzando tu belleza y elegancia"</p>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    @page { size: A4 portrait; margin: 12mm; }
    html, body { width: 100% !important; height: auto !important; margin: 0 !important; padding: 0 !important; overflow: visible !important; background: #fff !important; }
    body.imprimiendo-documento > *:not(#seccionRecibo) { display: none !important; }
    body.imprimiendo-documento > #seccionRecibo { display: block !important; }
    #seccionRecibo {
        position: static !important;
        width: 100% !important;
        max-width: 186mm !important;
        margin: 0 auto !important;
        color: #111 !important;
        box-shadow: none !important;
        border: 1px solid #bbb !important;
    }
    #seccionRecibo .card-body { padding: 8mm !important; }
    #seccionRecibo .table-responsive { overflow: visible !important; }
    #seccionRecibo tr { break-inside: avoid; page-break-inside: avoid; }
    #seccionRecibo .table { font-size: 10pt; }
}
</style>
<script>
(() => {
    const recibo = document.getElementById('seccionRecibo');
    let padreOriginal, siguienteOriginal;
    window.addEventListener('beforeprint', () => {
        padreOriginal = recibo.parentNode;
        siguienteOriginal = recibo.nextSibling;
        document.body.appendChild(recibo);
        document.body.classList.add('imprimiendo-documento');
    });
    window.addEventListener('afterprint', () => {
        if (padreOriginal) padreOriginal.insertBefore(recibo, siguienteOriginal);
        document.body.classList.remove('imprimiendo-documento');
    });
})();
</script>

@endsection
