@extends('adm_plantilla')

@section('content')

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="m-0 text-danger fw-bold"><i class="bi bi-box-seam me-2"></i> Registrar Nuevo Producto</h4>
            <small class="text-muted">Ingresa los datos para registrar un nuevo producto en el catálogo</small>
        </div>
        <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary rounded-pill">
            <i class="bi bi-arrow-left me-1"></i> Volver a la lista
        </a>
    </div>

    @if (isset($errors) && $errors->any())
        <div class="alert alert-danger rounded-4 shadow-sm mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-secondary">Descripción del Producto *</label>
                        <input type="text" name="descripcion" class="form-control" value="{{ old('descripcion') }}" placeholder="Ej: Esmalte Gel Rojo Intenso" minlength="2" maxlength="255" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary">Categoría *</label>
                        <select name="id_categoria" class="form-select" required>
                            <option value="">Seleccione una categoría...</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id_categoria }}" {{ old('id_categoria') == $cat->id_categoria ? 'selected' : '' }}>
                                    {{ $cat->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary">Marca *</label>
                        <select name="idmarca" class="form-select" required>
                            <option value="">Seleccione una marca...</option>
                            @foreach($marcas as $m)
                                <option value="{{ $m->idmarca }}" {{ old('idmarca') == $m->idmarca ? 'selected' : '' }}>
                                    {{ $m->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold text-secondary mb-0">Proveedor *</label>
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalProveedor"><i class="bi bi-plus-lg"></i> Nuevo proveedor</button>
                        </div>
                        <select name="id_proveedor" id="id_proveedor" class="form-select" required>
                            <option value="">Seleccione un proveedor...</option>
                            @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->id_proveedor }}" {{ old('id_proveedor') == $proveedor->id_proveedor ? 'selected' : '' }}>{{ $proveedor->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary">Stock Inicial *</label>
                        <input type="number" name="stock" class="form-control" value="{{ old('stock', 0) }}" min="0" step="1" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary">Destacado</label>
                        <input type="text" name="destacado" class="form-control text-uppercase" value="{{ old('destacado') }}" maxlength="50" placeholder="Ej.: NUEVO, OFERTA o valor de la base de datos">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary">Imagen del producto</label>
                        <input type="file" name="imagen" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted">JPG, PNG o WEBP. Máximo 2 MB.</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary">Precio de Compra (Bs.) *</label>
                        <input type="number" step="1" name="preciocompra" class="form-control" value="{{ old('preciocompra') }}" placeholder="0.00" min="0" max="999999.99" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary">Precio de Venta (Bs.) *</label>
                        <input type="number" step="1" name="precioventa" class="form-control" value="{{ old('precioventa') }}" placeholder="0.00" min="0" max="999999.99" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary">Fecha de Caducidad del Lote *</label>
                        <input type="date" name="fecha_caducidad" class="form-control" value="{{ old('fecha_caducidad') }}" min="{{ now()->toDateString() }}" required>
                    </div>
                </div>

                <div class="text-center mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm">
                        <i class="bi bi-save me-1"></i> Guardar Producto
                    </button>
                    <a href="{{ route('productos.index') }}" class="btn btn-secondary rounded-pill px-4 ms-2">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content rounded-4 border-0 shadow">
        <form id="formProveedorRapido">
            <div class="modal-header bg-danger text-white"><h5 class="modal-title"><i class="bi bi-truck me-2"></i>Nuevo proveedor</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><div id="errorProveedor" class="alert alert-danger d-none py-2"></div><div class="row g-3">
                <div class="col-6"><label class="form-label">Nombre *</label><input name="nombre" class="form-control" required></div>
                <div class="col-6"><label class="form-label">Apellido *</label><input name="apellido" class="form-control" required></div>
                <div class="col-6"><label class="form-label">Teléfono</label><input name="telefono" class="form-control"></div>
                <div class="col-6"><label class="form-label">Correo</label><input type="email" name="email" class="form-control"></div>
                <div class="col-12"><label class="form-label">Dirección</label><input name="direccion" class="form-control"></div>
            </div></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-danger rounded-pill" type="submit">Guardar y seleccionar</button></div>
        </form>
    </div></div>
</div>

@endsection

@section('scripts')
<script>
// Evita que la capa del contenedor quede por debajo del backdrop de Bootstrap.
const modalProveedorEl = document.getElementById('modalProveedor');
document.body.appendChild(modalProveedorEl);
modalProveedorEl.addEventListener('hidden.bs.modal', () => {
    if (!document.querySelector('.modal.show')) {
        document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    }
});

document.getElementById('formProveedorRapido').addEventListener('submit', async function (event) {
    event.preventDefault(); const error = document.getElementById('errorProveedor'); error.classList.add('d-none');
    try {
        const response = await fetch('{{ route('proveedores.store') }}', { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: new FormData(this) });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || (data.errors ? Object.values(data.errors).flat()[0] : 'No se pudo guardar el proveedor.'));
        document.getElementById('id_proveedor').add(new Option(data.nombre_completo, data.id_proveedor, true, true));
        bootstrap.Modal.getOrCreateInstance(modalProveedorEl).hide(); this.reset();
    } catch (e) { error.textContent = e.message || 'Error de conexión.'; error.classList.remove('d-none'); }
});
</script>
@endsection
