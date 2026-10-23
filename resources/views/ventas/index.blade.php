@extends('adm_plantilla')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="m-0 text-danger fw-bold"><i class="bi bi-cart-check-fill me-2"></i> Historial de Ventas</h4>
        <small class="text-muted">Registro de todas las ventas de productos realizadas en el salón</small>
    </div>
    <a href="{{ route('ventas.create') }}" class="btn btn-danger rounded-pill shadow-sm">
        <i class="bi bi-cart-plus-fill me-1"></i> Nueva Venta (Punto de Venta)
    </a>
</div>

<!-- Filtros de Fecha -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('ventas.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-0 fw-bold">Desde:</label>
                <input type="date" name="fecha_desde" value="{{ $fechaDesde }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted mb-0 fw-bold">Hasta:</label>
                <input type="date" name="fecha_hasta" value="{{ $fechaHasta }}" class="form-control">
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-outline-danger rounded-pill w-50">Filtrar</button>
                <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary rounded-pill w-50">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<!-- Tarjeta de Resumen -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-2">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted fw-bold">Total de Ventas Realizadas</small>
                    <h3 class="text-danger fw-bold m-0">{{ $ventas->count() }}</h3>
                    <small class="text-muted">Transacciones registradas</small>
                </div>
                <div class="badge bg-danger-subtle text-danger rounded-circle p-3">
                    <i class="bi bi-receipt-cutoff fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-2">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted fw-bold">Monto Total Recaudado</small>
                    <h3 class="text-success fw-bold m-0">Bs. {{ number_format($totalRecaudado, 2) }}</h3>
                    <small class="text-muted">Ingresos por productos</small>
                </div>
                <div class="badge bg-success-subtle text-success rounded-circle p-3">
                    <i class="bi bi-cash-coin fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Ventas -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-danger">
                    <tr>
                        <th class="rounded-start"># Venta</th>
                        <th>Cliente</th>
                        <th>Vendido por</th>
                        <th>Fecha</th>
                        <th>Productos Vendidos (Detalle)</th>
                        <th class="text-end">Total Recaudado</th>
                        <th class="text-center rounded-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventas as $v)
                        <tr>
                            <td class="fw-bold text-danger">#{{ $v->id_venta }}</td>
                            <td>
                                <div class="fw-bold">{{ $v->cliente->nombre ?? 'Cliente Ocasional' }} {{ $v->cliente->apellido ?? '' }}</div>
                                @if($v->cliente && $v->cliente->telefono)
                                    <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $v->cliente->telefono }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1">
                                    <i class="bi bi-person-fill me-1"></i> {{ $v->usuario->username ?? 'Sistema' }}
                                </span>
                            </td>
                            <td>
                                <small class="fw-semibold">{{ is_object($v->fecha) ? $v->fecha->format('d/m/Y') : $v->fecha }}</small>
                            </td>
                            <td>
                                <ul class="list-unstyled mb-0 small">
                                    @foreach($v->detalles as $det)
                                        <li class="py-1">
                                            &bull; <strong class="text-dark">{{ $det->cantidad }}x</strong> 
                                            <span>{{ $det->producto->descripcion ?? 'Producto' }}</span>
                                            @if($det->producto && $det->producto->destacado)
                                                <span class="badge text-bg-warning ms-1"><i class="bi bi-star-fill me-1"></i>{{ $det->producto->destacado }}</span>
                                            @endif
                                            <span class="text-muted ms-1">(Bs. {{ number_format($det->precioventa, 2) }} c/u &bull; Sub: Bs. {{ number_format($det->subtotal, 2) }})</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="text-end fw-bold text-success fs-6">Bs. {{ number_format($v->total, 2) }}</td>
                            <td class="text-center">
                                <a href="{{ route('ventas.show', $v->id_venta) }}" class="btn btn-outline-danger btn-sm rounded-pill px-3 me-1" title="Ver Comprobante">
                                    <i class="bi bi-receipt"></i> Recibo
                                </a>
                                @if(strtolower(session('rol')) === 'administrador')
                                    <form action="{{ route('ventas.destroy', $v->id_venta) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Anular esta venta? El stock de los productos será restaurado.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-2" title="Anular venta y restaurar stock">
                                            <i class="fa-solid fa-ban"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay ventas registradas en el período seleccionado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
