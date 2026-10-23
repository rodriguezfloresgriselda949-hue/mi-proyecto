@extends('adm_plantilla')

@section('content')

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="m-0 text-danger fw-bold"><i class="bi bi-bag-plus me-2"></i> Registrar Compra de Mercadería</h4>
            <small class="text-muted">Ingresa la compra de productos a proveedores para abastecer el stock</small>
        </div>
        <a href="{{ route('compras.index') }}" class="btn btn-outline-secondary rounded-pill">
            <i class="bi bi-arrow-left me-1"></i> Volver a Compras
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

    <form action="{{ route('compras.store') }}" method="POST" id="formCompra">
        @csrf

        <div class="row g-4">
            <div class="col-lg-5">
                <!-- Proveedor -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-3">
                        <h6 class="text-danger fw-bold m-0"><i class="bi bi-truck me-2"></i> 1. Proveedor</h6>
                    </div>
                    <div class="card-body pt-2">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Seleccionar Proveedor *</label>
                            <select name="id_proveedor" class="form-select" required>
                                <option value="">Seleccione un proveedor...</option>
                                @foreach($proveedores as $prov)
                                    <option value="{{ $prov->id_proveedor }}">
                                        {{ $prov->nombre }} {{ $prov->apellido }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Producto a comprar -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-3">
                        <h6 class="text-danger fw-bold m-0"><i class="bi bi-box-seam me-2"></i> 2. Producto a Ingresar</h6>
                    </div>
                    <div class="card-body pt-2">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Producto</label>
                            <select id="selectProdCompra" class="form-select">
                                <option value="">Seleccione un producto...</option>
                                @foreach($productos as $p)
                                    <option value="{{ $p->id_producto }}" data-desc="{{ $p->descripcion }}" data-precio="{{ $p->preciocompra }}">
                                        {{ $p->descripcion }} (Stock actual: {{ $p->stock }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-secondary">Costo Compra Unit.</label>
                                <input type="number" step="1" id="inputPrecioCompra" class="form-control" placeholder="0.00" min="0" max="999999.99">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-secondary">Cantidad Comprada</label>
                                <input type="number" id="inputCantCompra" class="form-control" value="1" min="1" step="1">
                            </div>
                        </div>

                        <button type="button" id="btnAgregarCompra" class="btn btn-outline-danger rounded-pill w-100 fw-bold">
                            <i class="bi bi-plus-circle me-1"></i> Añadir a la Compra
                        </button>
                    </div>
                </div>
            </div>

            <!-- Resumen de Compra -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-3">
                        <h6 class="text-danger fw-bold m-0"><i class="bi bi-list-check me-2"></i> 3. Detalle de la Orden de Compra</h6>
                    </div>
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <div class="table-responsive flex-grow-1">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-danger">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center" style="width: 90px;">Cant.</th>
                                        <th class="text-end" style="width: 110px;">Costo Unit.</th>
                                        <th class="text-end" style="width: 110px;">Subtotal</th>
                                        <th class="text-center" style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="cuerpoCompra">
                                    <tr id="vacioCompra">
                                        <td colspan="5" class="text-center text-muted py-5">
                                            Añade productos para registrar la orden de compra.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="border-top pt-3 mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="m-0 fw-bold text-secondary">Total Inversión:</h4>
                                <h3 class="m-0 fw-bold text-danger" id="txtTotalCompra">Bs. 0.00</h3>
                            </div>

                            <button type="submit" id="btnGuardarCompra" class="btn btn-danger btn-lg rounded-pill w-100 fw-bold shadow-sm" disabled>
                                <i class="bi bi-save me-1"></i> Guardar Compra e Incrementar Stock
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
let itemsCompra = [];

function escaparHtml(valor) {
    return String(valor).replace(/[&<>'"]/g, caracter => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    })[caracter]);
}

const selectProd = document.getElementById('selectProdCompra');
const inputPrecio = document.getElementById('inputPrecioCompra');
const inputCant = document.getElementById('inputCantCompra');
const btnAgregar = document.getElementById('btnAgregarCompra');
const cuerpoCompra = document.getElementById('cuerpoCompra');
const txtTotal = document.getElementById('txtTotalCompra');
const btnGuardar = document.getElementById('btnGuardarCompra');

selectProd.addEventListener('change', () => {
    const sel = selectProd.options[selectProd.selectedIndex];
    if (sel && sel.value) {
        inputPrecio.value = parseFloat(sel.dataset.precio || 0).toFixed(2);
    } else {
        inputPrecio.value = '';
    }
});

btnAgregar.addEventListener('click', () => {
    const sel = selectProd.options[selectProd.selectedIndex];
    if (!sel || !sel.value) {
        alert("Selecciona un producto.");
        return;
    }

    const id = parseInt(sel.value);
    const desc = sel.dataset.desc;
    const precio = parseFloat(inputPrecio.value);
    const cant = parseInt(inputCant.value);

    if (isNaN(precio) || precio < 0) {
        alert("Ingresa un precio de compra válido.");
        return;
    }
    if (isNaN(cant) || cant <= 0) {
        alert("Ingresa una cantidad válida.");
        return;
    }

    const existente = itemsCompra.find(item => item.id === id);
    if (existente) {
        existente.cant += cant;
        existente.precio = precio;
    } else {
        itemsCompra.push({ id, desc, precio, cant });
    }
    render();

    selectProd.value = '';
    inputPrecio.value = '';
    inputCant.value = 1;
});

function render() {
    cuerpoCompra.innerHTML = '';
    if (itemsCompra.length === 0) {
        cuerpoCompra.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-5">Añade productos para registrar la orden de compra.</td></tr>`;
        txtTotal.innerText = 'Bs. 0.00';
        btnGuardar.disabled = true;
        return;
    }

    let total = 0;
    itemsCompra.forEach((item, i) => {
        const sub = item.cant * item.precio;
        total += sub;
        cuerpoCompra.innerHTML += `
            <tr>
                <td>
                    <span class="fw-bold">${escaparHtml(item.desc)}</span>
                    <input type="hidden" name="productos[${i}][id]" value="${item.id}">
                </td>
                <td class="text-center">
                    <input type="number" class="form-control text-center p-1" name="productos[${i}][cant]" value="${item.cant}" min="1" step="1" required onchange="cambiarCant(${i}, this.value)">
                </td>
                <td class="text-end">
                    <input type="number" step="1" class="form-control text-end p-1" name="productos[${i}][precio]" value="${item.precio.toFixed(2)}" min="0" max="999999.99" required onchange="cambiarPrecio(${i}, this.value)">
                </td>
                <td class="text-end fw-bold text-danger">Bs. ${sub.toFixed(2)}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-circle p-1" onclick="remover(${i})"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
        `;
    });

    txtTotal.innerText = `Bs. ${total.toFixed(2)}`;
    btnGuardar.disabled = false;
}

function cambiarCant(i, val) {
    itemsCompra[i].cant = parseInt(val) || 1;
    render();
}

function cambiarPrecio(i, val) {
    itemsCompra[i].precio = parseFloat(val) || 0;
    render();
}

function remover(i) {
    itemsCompra.splice(i, 1);
    render();
}
</script>
@endsection
