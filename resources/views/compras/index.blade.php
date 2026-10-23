@extends('adm_plantilla')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="m-0 text-danger fw-bold"><i class="bi bi-bag-check-fill me-2"></i> Registro de Compras</h4>
        <small class="text-muted">Historial de compras de insumos y mercadería a proveedores</small>
    </div>
    <a href="{{ route('compras.create') }}" class="btn btn-danger rounded-pill shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> Nueva Compra de Mercadería
    </a>
</div>

@if($errors->any())<div class="alert alert-danger rounded-3">{{ $errors->first() }}</div>@endif
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('compras.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3"><label for="comprasDesde" class="form-label fw-bold small">Desde</label><input id="comprasDesde" type="date" name="desde" class="form-control" value="{{ request('desde') }}"></div>
            <div class="col-md-3"><label for="comprasHasta" class="form-label fw-bold small">Hasta</label><input id="comprasHasta" type="date" name="hasta" class="form-control" value="{{ request('hasta') }}"></div>
            <div class="col-md-4"><label for="comprasProducto" class="form-label fw-bold small">Producto</label><select id="comprasProducto" name="producto" class="form-select"><option value="">Todos los productos</option>@foreach($productos as $producto)<option value="{{ $producto->id_producto }}" @selected((string)$productoSeleccionado === (string)$producto->id_producto)>{{ $producto->descripcion }}</option>@endforeach</select></div>
            <div class="col-md-2 d-flex gap-2"><button class="btn btn-danger rounded-pill flex-fill"><i class="bi bi-funnel me-1"></i>Filtrar</button><a href="{{ route('compras.index') }}" class="btn btn-outline-secondary rounded-pill">Limpiar</a></div>
        </form>
    </div>
</div>

<!-- Tarjetas Resumen -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-2">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted fw-bold">Total Compras Registradas</small>
                    <h3 class="text-danger fw-bold m-0">{{ $compras->count() }}</h3>
                    <small class="text-muted">Órdenes de compra</small>
                </div>
                <div class="badge bg-danger-subtle text-danger rounded-circle p-3">
                    <i class="bi bi-box-arrow-in-down fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-2">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted fw-bold">Monto Total Invertido</small>
                    <h3 class="text-danger fw-bold m-0">Bs. {{ number_format($totalCompras, 2) }}</h3>
                    <small class="text-muted">Inversión en inventario</small>
                </div>
                <div class="badge bg-warning-subtle text-warning rounded-circle p-3">
                    <i class="bi bi-currency-dollar fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Compras -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-danger">
                    <tr>
                        <th class="rounded-start"># Compra</th>
                        <th>Proveedor</th>
                        <th>Empresa</th>
                        <th>Registrado por</th>
                        <th>Fecha</th>
                        <th>Productos</th>
                        <th class="text-end">Total Invertido</th>
                        <th class="text-center rounded-end">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($compras as $c)
                        <tr>
                            <td class="fw-bold">#{{ $c->id_compra }}</td>
                            <td>
                                <span class="fw-bold"><i class="bi bi-truck text-danger me-1"></i> {{ $c->proveedor->nombre ?? 'Proveedor General' }} {{ $c->proveedor->apellido ?? '' }}</span>
                            </td>
                            <td>{{ $c->proveedor->nombre_empresa ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill">
                                    {{ $c->usuario->username ?? 'Admin' }}
                                </span>
                            </td>
                            <td>{{ is_object($c->fecha) ? $c->fecha->format('d/m/Y H:i') : $c->fecha }}</td>
                            <td>
                                <ul class="list-unstyled mb-0 small">
                                    @foreach($c->detalles->when($productoSeleccionado, fn ($detalles) => $detalles->where('id_producto', $productoSeleccionado)) as $det)
                                        <li>&bull; {{ $det->cantidad }}x {{ $det->producto->descripcion ?? 'Producto' }} (Bs. {{ number_format($det->precio, 2) }})</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="text-end fw-bold text-danger fs-6">Bs. {{ number_format($c->total, 2) }}</td>
                            <td class="text-center"><a href="{{ route('compras.comprobante', $c->id_compra) }}" class="btn btn-outline-danger btn-sm rounded-pill" title="Imprimir reporte de compra"><i class="bi bi-printer-fill me-1"></i>Imprimir reporte</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No hay compras registradas en el sistema.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
