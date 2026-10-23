@extends('adm_plantilla')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div><h4 class="m-0 text-danger fw-bold"><i class="bi bi-tags-fill me-2"></i>Reservas de promociones</h4><small class="text-muted">Consulta las citas registradas para promociones.</small></div>
    <a href="{{ route('reservas.promociones.create') }}" class="btn btn-danger rounded-pill shadow-sm"><i class="bi bi-plus-lg me-1"></i>Nueva reserva de promoción</a>
</div>
@if(session('success'))<div class="alert alert-success rounded-3">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger rounded-3">{{ $errors->first() }}</div>@endif
<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-3">
    <form method="GET" action="{{ route('reservas.promociones.index') }}" class="row g-3 align-items-end">
        <div class="col-md-3"><label for="filtroDesde" class="form-label fw-bold small">Desde</label><input id="filtroDesde" type="date" name="desde" class="form-control" value="{{ $desde }}"></div>
        <div class="col-md-3"><label for="filtroHasta" class="form-label fw-bold small">Hasta</label><input id="filtroHasta" type="date" name="hasta" class="form-control" value="{{ $hasta }}"></div>
        <div class="col-md-3"><label for="filtroEstadoPromo" class="form-label fw-bold small">Estado</label><select id="filtroEstadoPromo" name="estado" class="form-select"><option value="todos" @selected($estadoFiltro === 'todos')>Todos</option><option value="pendientes" @selected($estadoFiltro === 'pendientes')>Pendientes</option><option value="atendiendo" @selected($estadoFiltro === 'atendiendo')>En atención</option><option value="completadas" @selected($estadoFiltro === 'completadas')>Completadas</option><option value="canceladas" @selected($estadoFiltro === 'canceladas')>Canceladas</option></select></div>
        <div class="col-md-3 d-flex gap-2"><button class="btn btn-danger rounded-pill flex-fill"><i class="bi bi-funnel me-1"></i>Filtrar</button><a href="{{ route('reservas.promociones.index') }}" class="btn btn-outline-secondary rounded-pill">Limpiar</a></div>
    </form>
</div></div>
<div class="card border-0 shadow-sm rounded-4"><div class="card-body p-3"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-danger"><tr><th>#</th><th>Promoción</th><th>Cliente</th><th>Fecha</th><th>Hora</th><th>Precio</th><th>Adelanto</th><th>Estado</th><th class="text-center">Acciones</th></tr></thead>
        <tbody>
            @forelse($reservas as $reserva)
                @php($claseEstado = match($reserva->estado) { 'Completada' => 'bg-success', 'En Atención' => 'bg-primary', 'Cancelado' => 'bg-secondary', default => 'bg-warning text-dark' })
                <tr><td class="fw-bold">#{{ $reserva->id_reserva_promocion }}</td><td><strong>{{ $reserva->promocion->nombre ?? 'Promoción eliminada' }}</strong></td><td>{{ $reserva->cliente->nombre ?? 'Cliente eliminado' }} {{ $reserva->cliente->apellido ?? '' }}</td><td>{{ $reserva->fecha->format('d/m/Y') }}</td><td>{{ $reserva->hora?->format('H:i') }}</td><td class="fw-bold text-danger">Bs. {{ number_format((float)$reserva->precio_promocion, 2) }}</td><td class="fw-bold text-success">Bs. {{ number_format((float)$reserva->adelanto, 2) }}</td><td><span class="badge {{ $claseEstado }} rounded-pill">{{ $reserva->estado }}</span></td><td class="text-center text-nowrap"><a href="{{ route('reservas.promociones.comprobante', $reserva->id_reserva_promocion) }}" class="btn btn-outline-danger btn-sm rounded-pill me-1" title="Imprimir comprobante"><i class="bi bi-printer-fill"></i> Comprobante</a>@if($reserva->estado === 'Reservado' || $reserva->estado === 'Pendiente')<form action="{{ route('reservas.promociones.cambiarEstado', $reserva->id_reserva_promocion) }}" method="POST" class="d-inline">@csrf<input type="hidden" name="estado" value="En Atención"><button class="btn btn-outline-primary btn-sm rounded-pill me-1"><i class="bi bi-play-fill"></i> Atender</button></form>@elseif($reserva->estado === 'En Atención')<form action="{{ route('reservas.promociones.cambiarEstado', $reserva->id_reserva_promocion) }}" method="POST" class="d-inline">@csrf<input type="hidden" name="estado" value="Completada"><button class="btn btn-success btn-sm rounded-pill me-1"><i class="bi bi-check-lg"></i> Completar</button></form>@endif<a href="{{ route('reservas.promociones.edit', $reserva->id_reserva_promocion) }}" class="btn btn-outline-warning btn-sm rounded-pill me-1" title="Editar"><i class="fa-solid fa-pen"></i> Editar</a><form action="{{ route('reservas.promociones.destroy', $reserva->id_reserva_promocion) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar esta reserva de promoción?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm rounded-pill" title="Eliminar"><i class="fa-solid fa-trash"></i> Eliminar</button></form></td></tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-4">Todavía no hay reservas de promociones.</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div></div>
@endsection
