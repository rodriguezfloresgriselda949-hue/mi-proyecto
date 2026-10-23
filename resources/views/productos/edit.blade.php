@extends('adm_plantilla')

@section('content')

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="m-0 text-danger fw-bold"><i class="bi bi-pencil-square me-2"></i> Editar Producto</h4>
            <small class="text-muted">Modifica los detalles del producto #{{ $producto->id_producto }}</small>
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
            <form action="{{ route('productos.update', $producto->id_producto) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-secondary">Descripción del Producto *</label>
                        <input type="text" name="descripcion" class="form-control" value="{{ old('descripcion', $producto->descripcion) }}" minlength="2" maxlength="255" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary">Categoría *</label>
                        <select name="id_categoria" class="form-select" required>
                            <option value="">Seleccione una categoría...</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id_categoria }}" {{ old('id_categoria', $producto->id_categoria) == $cat->id_categoria ? 'selected' : '' }}>
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
                                <option value="{{ $m->idmarca }}" {{ old('idmarca', $producto->idmarca) == $m->idmarca ? 'selected' : '' }}>
                                    {{ $m->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold text-secondary">Proveedor *</label>
                        <select name="id_proveedor" class="form-select" required>
                            <option value="">Seleccione un proveedor...</option>
                            @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->id_proveedor }}" {{ old('id_proveedor', $producto->id_proveedor) == $proveedor->id_proveedor ? 'selected' : '' }}>{{ $proveedor->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary">Stock Actual *</label>
                        <input type="number" name="stock" class="form-control" value="{{ old('stock', $producto->stock) }}" min="0" step="1" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary">Destacado</label>
                        <input type="text" name="destacado" class="form-control text-uppercase" value="{{ old('destacado', $producto->destacado) }}" maxlength="50">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary">Nueva imagen</label>
                        <input type="file" name="imagen" class="form-control" accept="image/jpeg,image/png,image/webp">
                        @if($producto->imagen && file_exists(public_path('productos/' . $producto->imagen)))
                            <div class="mt-2 d-flex align-items-center gap-2">
                                <img src="{{ asset('productos/' . $producto->imagen) }}" alt="Preview" class="rounded border" width="50" height="50" style="object-fit:cover">
                                <small class="text-muted text-truncate" style="max-width: 180px;">Actual: {{ $producto->imagen }}</small>
                            </div>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary">Precio de Compra (Bs.) *</label>
                        <input type="number" step="1" name="preciocompra" class="form-control" value="{{ old('preciocompra', $producto->preciocompra) }}" min="0" max="999999.99" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary">Precio de Venta (Bs.) *</label>
                        <input type="number" step="1" name="precioventa" class="form-control" value="{{ old('precioventa', $producto->precioventa) }}" min="0" max="999999.99" required>
                    </div>
                </div>

                <div class="text-center mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Actualizar Producto
                    </button>
                    <a href="{{ route('productos.index') }}" class="btn btn-secondary rounded-pill px-4 ms-2">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
