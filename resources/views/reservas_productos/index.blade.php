@extends('adm_plantilla')

@section('content')
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="m-0 text-danger fw-bold"><i class="bi bi-box2-heart-fill me-2"></i>Productos reservados</h4>
            <small class="text-muted">Administra las reservas realizadas desde el catálogo.</small>
        </div>
        <span class="badge bg-danger rounded-pill px-3 py-2">{{ $grupos->count() }} grupos</span>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('reservas.productos.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label for="fechaDesde" class="form-label small text-muted mb-0 fw-bold">Desde:</label>
                    <input type="date" id="fechaDesde" name="fecha_desde" value="{{ $filtros['fecha_desde'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="fechaHasta" class="form-label small text-muted mb-0 fw-bold">Hasta:</label>
                    <input type="date" id="fechaHasta" name="fecha_hasta" value="{{ $filtros['fecha_hasta'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="filtroEstado" class="form-label small text-muted mb-0 fw-bold">Estado:</label>
                    <select id="filtroEstado" name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        @foreach(['reserva', 'confirmado', 'pagado', 'entregado'] as $estadoFiltro)
                            <option value="{{ $estadoFiltro }}" @selected(($filtros['estado'] ?? '') === $estadoFiltro)>{{ ucfirst($estadoFiltro) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-danger rounded-pill flex-fill">Filtrar</button>
                    <a href="{{ route('reservas.productos.index') }}" class="btn btn-outline-secondary rounded-pill flex-fill">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr><th>Cliente / Fecha de recojo</th><th>Productos, precio y subtotal</th><th>Total del grupo</th><th>Estado</th><th>Acciones del grupo</th></tr>
            </thead>
            <tbody>
                @forelse($grupos as $grupo)
                    <tr>
                        <td>
                            <strong>{{ $grupo['cliente'] }}</strong>
                            <small class="d-block text-muted">{{ $grupo['telefono'] ?: 'Sin teléfono registrado' }}</small>
                            <small class="d-block mt-2">Recojo: {{ $grupo['fecha_recojo'] ? \Carbon\Carbon::parse($grupo['fecha_recojo'])->format('d/m/Y') : 'Sin fecha' }}</small>
                            <small class="d-block text-muted">Hora: {{ $grupo['horarios']->isNotEmpty() ? $grupo['horarios']->implode(', ') : 'Sin hora' }}</small>
                        </td>
                        <td>
                            @foreach($grupo['items'] as $item)
                                <div class="py-1 border-bottom">
                                    <strong>{{ $item['cantidad'] }} × {{ $item['producto'] }}</strong>
                                    <small class="d-block text-muted">Bs. {{ number_format($item['precio'], 2) }} c/u · Subtotal: Bs. {{ number_format($item['subtotal'], 2) }}</small>
                                    @if($item['id_venta'])<small><a href="{{ route('ventas.show', $item['id_venta']) }}">Venta #{{ $item['id_venta'] }}</a></small>@endif
                                </div>
                            @endforeach
                        </td>
                        <td class="fw-bold text-danger">Bs. {{ number_format($grupo['total'], 2) }}</td>
                        <td>
                            <span class="badge rounded-pill {{ $grupo['estado'] === 'entregado' ? 'bg-success' : ($grupo['estado'] === 'pagado' ? 'bg-primary' : ($grupo['estado'] === 'confirmado' ? 'bg-info text-dark' : ($grupo['estado'] === 'mixto' ? 'bg-secondary' : 'bg-warning text-dark'))) }}">{{ ucfirst($grupo['estado']) }}</span>
                        </td>
                        <td>
                            @if($grupo['whatsapp_url'] && !$grupo['incluye_entregado'])
                                <a class="btn btn-success btn-sm mb-2" target="_blank" rel="noopener" href="{{ $grupo['whatsapp_url'] }}"><i class="bi bi-whatsapp me-1"></i>Confirmar grupo por WhatsApp</a>
                            @elseif($grupo['incluye_entregado'])
                                <span class="badge bg-secondary mb-2">No se puede confirmar: incluye entregados</span>
                            @else
                                <span class="badge bg-secondary mb-2">Sin teléfono para WhatsApp</span>
                            @endif
                            @if(!$grupo['incluye_entregado'])
                                <form method="POST" action="{{ route('reservas.productos.estadoGrupo') }}" class="d-flex gap-2 flex-wrap" onsubmit="return this.estado.value !== 'entregado' || confirm('Al entregar el grupo se crearán las ventas y se descontará el stock. ¿Continuar?')">
                                    @csrf
                                    @foreach($grupo['ids'] as $idReservaProducto)<input type="hidden" name="ids[]" value="{{ $idReservaProducto }}">@endforeach
                                    <select name="estado" class="form-select form-select-sm" required>
                                        @if($grupo['estado'] === 'mixto')<option value="" selected disabled>Estado: mixto</option>@endif
                                        @foreach(['reserva', 'confirmado', 'pagado', 'entregado'] as $estado)
                                            <option value="{{ $estado }}" @selected($grupo['estado'] === $estado)>{{ ucfirst($estado) }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-danger btn-sm text-nowrap"><i class="bi bi-save me-1"></i>Guardar grupo</button>
                                </form>
                            @else
                                @if($grupo['estado'] === 'entregado')
                                    <span class="text-success small"><i class="bi bi-check2-circle"></i> Grupo entregado</span>
                                @else
                                    <span class="text-muted small">El grupo contiene productos ya entregados.</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No hay grupos de reservas para los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@section('scripts')
    @parent
    @if(session('whatsapp_url'))
        <script>window.open(@json(session('whatsapp_url')), '_blank', 'noopener');</script>
    @endif
@endsection
