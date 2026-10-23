@php
    $layout = (strtolower(session('rol')) === 'administrador') ? 'adm_plantilla' : 'empleado_plantilla';
@endphp

@extends($layout)

@section('content')

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="m-0 text-danger fw-bold"><i class="bi bi-cart-plus me-2"></i> Punto de Venta (Nueva Venta)</h4>
            <small class="text-muted">Selecciona el cliente y los productos para procesar la venta</small>
        </div>
        <a href="{{ strtolower(session('rol')) === 'administrador' ? route('ventas.index') : route('empleado.dashboard') }}" class="btn btn-outline-secondary rounded-pill">
            <i class="bi bi-arrow-left me-1"></i> {{ strtolower(session('rol')) === 'administrador' ? 'Historial de Ventas' : 'Volver al Panel' }}
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

    <form action="{{ route('ventas.store') }}" method="POST" id="formVenta">
        @csrf

        <div class="row g-4">
            <!-- Sección Izquierda: Selección de Cliente y Producto -->
            <div class="col-lg-5">
                <!-- Tarjeta Cliente -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-3">
                        <h6 class="text-danger fw-bold m-0"><i class="bi bi-person-fill me-2"></i> 1. Datos del Cliente</h6>
                    </div>
                    <div class="card-body pt-2">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Cliente *</label>
                            <select name="id_cliente" id="selectCliente" class="form-select" required>
                                <option value="">Seleccione un cliente...</option>
                                @foreach($clientes as $c)
                                    <option value="{{ $c->id_cliente }}">
                                        {{ $c->nombre }} {{ $c->apellido }} ({{ $c->telefono ?? 'Sin tel' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">¿Cliente no registrado?</small>
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalCliente">
                                <i class="bi bi-person-plus me-1"></i> Registrar Cliente
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta Selección de Producto -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-3">
                        <h6 class="text-danger fw-bold m-0"><i class="bi bi-box-seam me-2"></i> 2. Seleccionar Producto</h6>
                    </div>
                    <div class="card-body pt-2">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Producto</label>
                            <select id="selectProducto" class="form-select">
                                <option value="">Seleccione un producto...</option>
                                @foreach($productos as $prod)
                                    <option value="{{ $prod->id_producto }}" 
                                            data-desc="{{ $prod->descripcion }}" 
                                            data-precio="{{ $prod->precioventa }}" 
                                            data-stock="{{ $prod->stock }}"
                                            data-imagen="{{ $prod->imagen ? asset('productos/' . $prod->imagen) : '' }}">
                                        {{ $prod->descripcion }} - Bs. {{ number_format($prod->precioventa, 2) }} (Stock: {{ $prod->stock }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="vistaProducto" class="d-none text-center mb-3">
                            <img id="imagenProducto" src="" alt="Producto seleccionado" class="rounded border shadow-sm" width="130" height="130" style="object-fit:cover">
                            <div id="nombreProducto" class="small fw-bold mt-1"></div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold text-secondary">Precio Unitario</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Bs.</span>
                                    <input type="number" id="inputPrecio" class="form-control" placeholder="0.00" readonly>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-secondary">Cantidad</label>
                                <input type="number" id="inputCantidad" class="form-control" value="1" min="1" step="1">
                            </div>
                        </div>

                        <div class="mb-2">
                            <small class="text-muted" id="infoStock">Stock disponible: -</small>
                        </div>

                        <button type="button" id="btnAgregar" class="btn btn-outline-danger rounded-pill w-100 fw-bold">
                            <i class="bi bi-plus-circle me-1"></i> Agregar al Carrito
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sección Derecha: Carrito de Venta y Totales -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                        <h6 class="text-danger fw-bold m-0"><i class="bi bi-cart3 me-2"></i> 3. Resumen de la Venta</h6>
                        <span class="badge bg-danger rounded-pill" id="badgeTotalItems">0 productos</span>
                    </div>
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <div class="table-responsive flex-grow-1">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-danger">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center" style="width: 100px;">Cant.</th>
                                        <th class="text-end" style="width: 110px;">P. Unit</th>
                                        <th class="text-end" style="width: 110px;">Subtotal</th>
                                        <th class="text-center" style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="cuerpoCarrito">
                                    <tr id="filaVacia">
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <i class="bi bi-cart-x fs-1 text-muted d-block mb-2"></i>
                                            El carrito de venta está vacío. Selecciona productos para agregarlos.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Bloque de Totales y Confirmación -->
                        <div class="border-top pt-3 mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="m-0 fw-bold text-secondary">Total a Pagar:</h4>
                                <h3 class="m-0 fw-bold text-danger" id="txtTotal">Bs. 0.00</h3>
                            </div>

                            <button type="submit" id="btnProcesar" class="btn btn-danger btn-lg rounded-pill w-100 fw-bold shadow-sm" disabled>
                                <i class="bi bi-check2-circle me-1"></i> Confirmar y Procesar Venta
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" id="modalCliente" tabindex="-1" aria-labelledby="modalClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form id="formClienteRapido">
                <div class="modal-header bg-danger text-white rounded-top-4">
                    <h5 class="modal-title fw-bold" id="modalClienteLabel">
                        <i class="bi bi-person-plus-fill me-2"></i> Registrar Nuevo Cliente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="errorCliente" class="alert alert-danger d-none rounded-3 py-2 small"></div>
                    <p class="text-muted small mb-3">Ingresa los datos básicos del cliente. Se seleccionará automáticamente en esta venta sin recargar la página.</p>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small text-secondary">Nombre *</label>
                            <input class="form-control text-uppercase" name="nombre" placeholder="Ej: Ana" required minlength="2">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small text-secondary">Apellido *</label>
                            <input class="form-control text-uppercase" name="apellido" placeholder="Ej: López" required minlength="2">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small text-secondary">Teléfono / Celular <small class="text-muted fw-normal">(Opcional)</small></label>
                            <input class="form-control" name="telefono" placeholder="Ej: 72541236" maxlength="20">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small text-secondary">Correo <small class="text-muted fw-normal">(Opcional)</small></label>
                            <input class="form-control" name="email" type="email" placeholder="ejemplo@gmail.com" maxlength="50">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-danger rounded-pill px-4 fw-bold" type="submit" id="btnGuardarClienteRapido">
                        <i class="bi bi-check-lg me-1"></i> Guardar y Seleccionar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
let carrito = [];

// El contenido se renderiza dentro de .container (que crea su propia capa CSS).
// Bootstrap agrega el fondo al body; por eso el modal debe vivir también en body.
const modalClienteEl = document.getElementById('modalCliente');
document.body.appendChild(modalClienteEl);
modalClienteEl.addEventListener('hidden.bs.modal', () => {
    // Limpia un backdrop residual si Bootstrap fue interrumpido durante el cierre.
    if (!document.querySelector('.modal.show')) {
        document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    }
});

function escaparHtml(valor) {
    return String(valor).replace(/[&<>'"]/g, caracter => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    })[caracter]);
}

const selectProducto = document.getElementById('selectProducto');
const inputPrecio = document.getElementById('inputPrecio');
const inputCantidad = document.getElementById('inputCantidad');
const infoStock = document.getElementById('infoStock');
const btnAgregar = document.getElementById('btnAgregar');
const cuerpoCarrito = document.getElementById('cuerpoCarrito');
const txtTotal = document.getElementById('txtTotal');
const btnProcesar = document.getElementById('btnProcesar');
const badgeTotalItems = document.getElementById('badgeTotalItems');

// Cuando se selecciona un producto en el dropdown
selectProducto.addEventListener('change', () => {
    const selected = selectProducto.options[selectProducto.selectedIndex];
    if (selected && selected.value) {
        const precio = parseFloat(selected.dataset.precio || 0);
        const stock = parseInt(selected.dataset.stock || 0);
        inputPrecio.value = precio.toFixed(2);
        inputCantidad.max = stock;
        inputCantidad.value = 1;
        infoStock.innerHTML = `Stock disponible: <strong class="${stock <= 5 ? 'text-danger' : 'text-success'}">${stock} unidades</strong>`;
        const vista = document.getElementById('vistaProducto');
        const imagen = selected.dataset.imagen;
        vista.classList.toggle('d-none', !imagen);
        if (imagen) document.getElementById('imagenProducto').src = imagen;
        document.getElementById('nombreProducto').textContent = selected.dataset.desc;
    } else {
        inputPrecio.value = '';
        inputCantidad.value = 1;
        infoStock.innerHTML = 'Stock disponible: -';
        document.getElementById('vistaProducto').classList.add('d-none');
    }
});

// Agregar producto al carrito
btnAgregar.addEventListener('click', () => {
    const selected = selectProducto.options[selectProducto.selectedIndex];
    if (!selected || !selected.value) {
        alert("Por favor selecciona un producto.");
        return;
    }

    const id = parseInt(selected.value);
    const desc = selected.dataset.desc;
    const precio = parseFloat(selected.dataset.precio);
    const stock = parseInt(selected.dataset.stock);
    const cant = parseInt(inputCantidad.value);

    if (isNaN(cant) || cant <= 0) {
        alert("Ingresa una cantidad válida mayor a 0.");
        return;
    }

    const indexExistente = carrito.findIndex(item => item.id === id);
    const cantActual = indexExistente !== -1 ? carrito[indexExistente].cant : 0;

    if (cantActual + cant > stock) {
        alert(`No hay suficiente stock. Disponible: ${stock}, en carrito: ${cantActual}`);
        return;
    }

    if (indexExistente !== -1) {
        carrito[indexExistente].cant += cant;
    } else {
        carrito.push({ id, desc, precio, cant, stock });
    }

    renderizarCarrito();
    // Reset selector
    selectProducto.value = '';
    inputPrecio.value = '';
    inputCantidad.value = 1;
    infoStock.innerHTML = 'Stock disponible: -';
});

// Renderizar tabla del carrito
function renderizarCarrito() {
    cuerpoCarrito.innerHTML = '';

    if (carrito.length === 0) {
        cuerpoCarrito.innerHTML = `
            <tr id="filaVacia">
                <td colspan="5" class="text-center text-muted py-5">
                    <i class="bi bi-cart-x fs-1 text-muted d-block mb-2"></i>
                    El carrito de venta está vacío. Selecciona productos para agregarlos.
                </td>
            </tr>
        `;
        txtTotal.innerText = 'Bs. 0.00';
        badgeTotalItems.innerText = '0 productos';
        btnProcesar.disabled = true;
        return;
    }

    let total = 0;
    let totalItems = 0;

    carrito.forEach((item, index) => {
        const subtotal = item.cant * item.precio;
        total += subtotal;
        totalItems += item.cant;

        cuerpoCarrito.innerHTML += `
            <tr>
                <td>
                    <span class="fw-bold">${escaparHtml(item.desc)}</span>
                    <input type="hidden" name="productos[${index}][id]" value="${item.id}">
                </td>
                <td class="text-center">
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control text-center p-1" 
                               name="productos[${index}][cant]" 
                               value="${item.cant}" min="1" max="${item.stock}" step="1" required
                               onchange="actualizarCantidad(${index}, this.value)">
                    </div>
                </td>
                <td class="text-end">
                    Bs. ${item.precio.toFixed(2)}
                    <input type="hidden" name="productos[${index}][precio]" value="${item.precio}">
                </td>
                <td class="text-end fw-bold text-danger">Bs. ${subtotal.toFixed(2)}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-circle p-1" onclick="eliminarItem(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    txtTotal.innerText = `Bs. ${total.toFixed(2)}`;
    badgeTotalItems.innerText = `${totalItems} producto(s)`;
    btnProcesar.disabled = false;
}

function actualizarCantidad(index, nuevaCant) {
    nuevaCant = parseInt(nuevaCant);
    if (isNaN(nuevaCant) || nuevaCant <= 0) {
        carrito[index].cant = 1;
    } else if (nuevaCant > carrito[index].stock) {
        alert(`La cantidad excede el stock disponible (${carrito[index].stock})`);
        carrito[index].cant = carrito[index].stock;
    } else {
        carrito[index].cant = nuevaCant;
    }
    renderizarCarrito();
}

function eliminarItem(index) {
    carrito.splice(index, 1);
    renderizarCarrito();
}

document.getElementById('formClienteRapido').addEventListener('submit', async function (event) {
    event.preventDefault();
    const error = document.getElementById('errorCliente');
    const submitBtn = document.getElementById('btnGuardarClienteRapido');
    error.classList.add('d-none');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

    try {
        const response = await fetch('{{ route('cliente.guardar') }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: new FormData(this)
        });
        const data = await response.json();

        if (!response.ok) {
            error.textContent = data.message || (data.errors ? Object.values(data.errors).flat()[0] : 'No se pudo registrar el cliente.');
            error.classList.remove('d-none');
            return;
        }

        const optionText = `${data.nombre} ${data.apellido} (${data.telefono || 'Sin teléfono'})`;
        const option = new Option(optionText, data.id_cliente, true, true);
        document.getElementById('selectCliente').add(option);

        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalClienteEl);
        modalInstance.hide();
        this.reset();
    } catch (e) {
        error.textContent = 'Error de conexión. Intente nuevamente.';
        error.classList.remove('d-none');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Guardar y Seleccionar';
    }
});
</script>
@endsection
