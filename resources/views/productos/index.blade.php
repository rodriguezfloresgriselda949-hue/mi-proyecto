@php($layout = strtolower(session('rol')) === 'administrador' ? 'adm_plantilla' : 'empleado_plantilla')
@extends($layout)

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="m-0 text-danger fw-bold"><i class="bi bi-box-seam-fill me-2"></i> Inventario y Productos</h4>
        <small class="text-muted">Control de stock, precios y marcas de productos del salón</small>
    </div>
    @if(strtolower(session('rol')) === 'administrador')
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-danger rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#categoriasModal">
                <i class="bi bi-tags-fill me-1"></i> Administrar categorías
            </button>
            <a href="{{ route('productos.create') }}" class="btn btn-danger rounded-pill shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Nuevo Producto
            </a>
        </div>
    @endif
</div>

@if($sinStock > 0)
    <div class="alert alert-danger border-0 rounded-4 shadow-sm d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
        <div><strong>Sin producto disponible.</strong> Hay {{ $sinStock }} producto(s) con stock en 0.</div>
    </div>
@endif

<!-- Filtros y Búsqueda -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('productos.index') }}" class="row g-2 align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-control border-start-0" placeholder="Buscar por nombre, categoría o marca...">
                </div>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-outline-danger rounded-pill w-50">Buscar</button>
                <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary rounded-pill w-50">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Productos -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-danger">
                    <tr>
                        <th class="rounded-start">ID</th>
                        <th>Imagen</th>
                        <th>Descripción</th>
                        <th>Destacado</th>
                        <th>Categoría</th>
                        <th>Marca</th>
                        <th>Proveedor</th>
                        <th>P. Compra</th>
                        <th>P. Venta</th>
                        <th>Stock</th>
                        <th class="text-center rounded-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $p)
                        <tr>
                            <td class="fw-bold">#{{ $p->id_producto }}</td>
                            <td>
                                @if($p->imagen)
                                    <img src="{{ asset('productos/' . $p->imagen) }}" alt="{{ $p->descripcion }}" class="rounded border" width="52" height="52" style="object-fit:cover">
                                @else
                                    <span class="text-muted"><i class="bi bi-image"></i></span>
                                @endif
                            </td>
                            <td><span class="fw-bold">{{ $p->descripcion }}</span></td>
                            <td>
                                {{ $p->destacado ?: '—' }}
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill">
                                    {{ $p->categoria->nombre ?? 'Sin categoría' }}
                                </span>
                            </td>
                            <td>{{ $p->marca->nombre ?? 'N/A' }}</td>
                            <td>{{ $p->proveedor->nombre_completo ?? 'Sin asignar' }}</td>
                            <td>Bs. {{ number_format($p->preciocompra, 2) }}</td>
                            <td class="fw-bold text-danger">Bs. {{ number_format($p->precioventa, 2) }}</td>
                            <td>
                                @if($p->stock == 0)
                                    <span class="badge bg-danger rounded-pill px-2 py-1"><i class="bi bi-x-circle-fill me-1"></i> Sin producto</span>
                                @elseif($p->stock <= 5)
                                    <span class="badge bg-danger rounded-pill px-2 py-1">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $p->stock }} unid. (Bajo)
                                    </span>
                                @else
                                    <span class="badge bg-success rounded-pill px-2 py-1">
                                        {{ $p->stock }} unid.
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(strtolower(session('rol')) === 'administrador')
                                    <a href="{{ route('productos.edit', $p->id_producto) }}" class="btn btn-outline-warning btn-sm rounded-pill px-2" title="Editar">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('productos.destroy', $p->id_producto) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este producto?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2" title="Eliminar">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small">Solo consulta</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">No se encontraron productos en el inventario.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(strtolower(session('rol')) === 'administrador')
    <!-- Modal de administración de categorías -->
    <div class="modal fade" id="categoriasModal" tabindex="-1" aria-labelledby="categoriasModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="categoriasModalLabel"><i class="bi bi-tags-fill me-2"></i>Categorías de productos</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('categorias.store') }}" method="POST" class="row g-2 mb-4">
                        @csrf
                        <div class="col-sm-8">
                            <label for="nuevaCategoria" class="visually-hidden">Nueva categoría</label>
                            <input id="nuevaCategoria" type="text" name="nombre_categoria" value="{{ old('nombre_categoria') }}" class="form-control @error('nombre_categoria', 'categoria') is-invalid @enderror" maxlength="100" required placeholder="Nombre de la nueva categoría">
                            @error('nombre_categoria', 'categoria')
                                <div class="alert alert-danger py-2 mt-2 mb-0 small" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-sm-4 d-grid">
                            <button type="submit" class="btn btn-danger"><i class="bi bi-plus-lg me-1"></i>Agregar</button>
                        </div>
                    </form>

                    <div id="categoriaEdicion" class="card border-warning bg-warning-subtle mb-3 d-none">
                        <form id="formEditarCategoria" action="" method="POST" class="card-body">
                            @csrf
                            @method('PUT')
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong><i class="bi bi-pencil-fill me-1"></i>Actualizar categoría</strong>
                                <button type="button" class="btn-close" onclick="cancelarEdicionCategoria()" aria-label="Cancelar"></button>
                            </div>
                            <label for="nombreCategoriaEditar" class="form-label">Nombre</label>
                            <div class="input-group">
                                <input id="nombreCategoriaEditar" type="text" name="nombre" class="form-control" maxlength="100" required>
                                <button type="submit" class="btn btn-warning">Guardar</button>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Categoría</th>
                                    <th class="text-center">Productos</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categorias as $categoria)
                                    <tr>
                                        <td class="fw-semibold">{{ $categoria->nombre }}</td>
                                        <td class="text-center"><span class="badge text-bg-secondary rounded-pill">{{ $categoria->productos_count }}</span></td>
                                        <td class="text-center text-nowrap">
                                            <button type="button" class="btn btn-outline-warning btn-sm" data-id="{{ $categoria->id_categoria }}" data-nombre="{{ $categoria->nombre }}" onclick="editarCategoria(this)" title="Actualizar categoría">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <form action="{{ route('categorias.destroy', $categoria->id_categoria) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar la categoría {{ addslashes($categoria->nombre) }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar categoría" {{ $categoria->productos_count > 0 ? 'disabled' : '' }}>
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">No hay categorías registradas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <small class="d-block text-muted mt-3"><i class="bi bi-info-circle me-1"></i>Solo se pueden eliminar categorías que no tengan productos asociados.</small>
                </div>
            </div>
        </div>
    </div>

@endif

@endsection

@section('scripts')
@if(strtolower(session('rol')) === 'administrador')
<script>
    const categoriasBaseUrl = @json(url('/categorias'));
    const categoriasModal = document.getElementById('categoriasModal');

    // La plantilla usa un contenedor con z-index. Bootstrap requiere que el
    // modal esté directamente en body para que no quede detrás del backdrop.
    document.body.appendChild(categoriasModal);

    function editarCategoria(boton) {
        document.getElementById('formEditarCategoria').action = categoriasBaseUrl + '/' + boton.dataset.id;
        document.getElementById('nombreCategoriaEditar').value = boton.dataset.nombre;
        document.getElementById('categoriaEdicion').classList.remove('d-none');
        document.getElementById('nombreCategoriaEditar').focus();
    }

    function cancelarEdicionCategoria() {
        document.getElementById('categoriaEdicion').classList.add('d-none');
        document.getElementById('formEditarCategoria').action = '';
    }

    @if($errors->categoria->any())
        document.addEventListener('DOMContentLoaded', function () {
            new bootstrap.Modal(document.getElementById('categoriasModal')).show();
        });
    @endif
</script>
@endif
@endsection
