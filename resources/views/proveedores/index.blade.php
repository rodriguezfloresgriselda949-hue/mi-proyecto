@extends('adm_plantilla')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="m-0 text-danger fw-bold"><i class="bi bi-truck me-2"></i> Gestión de Proveedores</h4>
        <small class="text-muted">Lista de proveedores de productos e insumos para el salón</small>
    </div>
    <a href="{{ route('proveedores.create') }}" class="btn btn-danger rounded-pill shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> Nuevo Proveedor
    </a>
</div>

<!-- Buscador -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('proveedores.index') }}" class="row g-2 align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-control border-start-0" placeholder="Buscar por nombre, empresa, teléfono o email...">
                </div>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-outline-danger rounded-pill w-50">Buscar</button>
                <a href="{{ route('proveedores.index') }}" class="btn btn-outline-secondary rounded-pill w-50">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Proveedores -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-danger">
                    <tr>
                        <th class="rounded-start">ID</th>
                        <th>Nombre y Apellido</th>
                        <th>Empresa</th>
                        <th>Teléfono</th>
                        <th>Dirección / Ciudad</th>
                        <th>Correo Electrónico</th>
                        <th class="text-center rounded-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proveedores as $prov)
                        <tr>
                            <td class="fw-bold">#{{ $prov->id_proveedor }}</td>
                            <td>
                                <span class="fw-bold"><i class="bi bi-person-badge-fill text-danger me-1"></i> {{ $prov->nombre }} {{ $prov->apellido }}</span>
                            </td>
                            <td>{{ $prov->nombre_empresa ?? 'N/A' }}</td>
                            <td>
                                @if($prov->telefono)
                                    <i class="bi bi-telephone-fill text-secondary me-1"></i> {{ $prov->telefono }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ $prov->direccion ?? 'N/A' }}</td>
                            <td>{{ $prov->email ?? 'N/A' }}</td>
                            <td class="text-center">
                                <a href="{{ route('proveedores.edit', $prov->id_proveedor) }}" class="btn btn-outline-warning btn-sm rounded-pill px-2" title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form action="{{ route('proveedores.destroy', $prov->id_proveedor) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este proveedor?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2" title="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No se encontraron proveedores registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
