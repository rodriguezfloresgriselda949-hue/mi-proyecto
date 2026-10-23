(() => {
    const normalizar = texto => String(texto ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLocaleLowerCase('es');

    const mejorarSelect = select => {
        if (select.dataset.buscableInicializado === '1' || select.multiple) return;
        select.dataset.buscableInicializado = '1';

        const wrapper = document.createElement('div');
        wrapper.className = 'select-buscable';
        const input = document.createElement('input');
        input.type = 'search';
        input.className = 'form-control select-buscable__input';
        input.id = `busqueda-${select.id || Math.random().toString(36).slice(2)}`;
        input.autocomplete = 'off';
        input.setAttribute('role', 'combobox');
        input.setAttribute('aria-autocomplete', 'list');
        input.setAttribute('aria-expanded', 'false');
        input.setAttribute('aria-haspopup', 'listbox');
        const etiqueta = select.labels?.[0] || (select.id
            ? document.querySelector(`label[for="${CSS.escape(select.id)}"]`)
            : null);
        if (etiqueta) etiqueta.htmlFor = input.id;
        input.setAttribute('aria-label', etiqueta?.textContent.trim() || select.name || 'Buscar opción');

        const lista = document.createElement('div');
        lista.className = 'select-buscable__opciones';
        lista.setAttribute('role', 'listbox');
        const idLista = `opciones-${Math.random().toString(36).slice(2)}`;
        lista.id = idLista;
        input.setAttribute('aria-controls', idLista);

        select.parentNode.insertBefore(wrapper, select);
        const icono = document.createElement('span');
        icono.className = 'select-buscable__icono';
        icono.setAttribute('aria-hidden', 'true');
        icono.textContent = '⌄';
        wrapper.append(input, icono, lista, select);
        select.classList.add('visually-hidden');
        select.tabIndex = -1;
        const eraRequerido = select.required;
        select.required = false;
        input.required = eraRequerido;

        let opcionesVisibles = [];
        let indiceActivo = -1;

        const valorSeleccionado = () => {
            const opcion = select.selectedOptions[0];
            input.value = opcion && opcion.value !== '' ? opcion.text.trim() : '';
        };

        const cerrar = () => {
            wrapper.classList.remove('select-buscable--abierto');
            input.setAttribute('aria-expanded', 'false');
            indiceActivo = -1;
        };

        const elegir = opcion => {
            if (!opcion || opcion.disabled) return;
            select.value = opcion.value;
            valorSeleccionado();
            select.dispatchEvent(new Event('change', { bubbles: true }));
            cerrar();
        };

        const dibujar = () => {
            lista.replaceChildren();
            const consulta = normalizar(input.value);
            opcionesVisibles = [...select.options].filter(opcion =>
                normalizar(opcion.text).includes(consulta)
            );
            opcionesVisibles.forEach((opcion, indice) => {
                const boton = document.createElement('button');
                boton.type = 'button';
                boton.className = 'select-buscable__opcion';
                boton.setAttribute('role', 'option');
                boton.setAttribute('aria-selected', String(opcion.selected));
                boton.textContent = opcion.text.trim();
                boton.disabled = opcion.disabled;
                boton.addEventListener('mousedown', event => event.preventDefault());
                boton.addEventListener('click', () => elegir(opcion));
                lista.appendChild(boton);
            });
            if (!opcionesVisibles.length) {
                const vacio = document.createElement('p');
                vacio.className = 'select-buscable__vacio';
                vacio.textContent = 'No se encontraron opciones.';
                lista.appendChild(vacio);
            }
        };

        const abrir = () => {
            if (select.disabled) return;
            if (!wrapper.classList.contains('select-buscable--abierto')) {
                valorSeleccionado();
                dibujar();
                wrapper.classList.add('select-buscable--abierto');
                input.setAttribute('aria-expanded', 'true');
                input.select();
            }
        };

        input.addEventListener('focus', abrir);
        input.addEventListener('click', abrir);
        input.addEventListener('input', () => {
            if (!wrapper.classList.contains('select-buscable--abierto')) abrir();
            dibujar();
        });
        input.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                valorSeleccionado();
                cerrar();
            } else if (event.key === 'ArrowDown' && opcionesVisibles.length) {
                event.preventDefault();
                indiceActivo = Math.min(indiceActivo + 1, opcionesVisibles.length - 1);
                lista.children[indiceActivo]?.focus();
            } else if (event.key === 'Enter' && opcionesVisibles.length === 1) {
                event.preventDefault();
                elegir(opcionesVisibles[0]);
            }
        });
        select.addEventListener('change', valorSeleccionado);
        select.addEventListener('input', valorSeleccionado);
        select.form?.addEventListener('reset', () => {
            window.setTimeout(() => {
                valorSeleccionado();
                cerrar();
                dibujar();
            }, 0);
        });

        const observador = new MutationObserver(() => {
            if (wrapper.classList.contains('select-buscable--abierto')) dibujar();
            valorSeleccionado();
        });
        observador.observe(select, { childList: true, subtree: true, characterData: true, attributes: true });

        document.addEventListener('click', event => {
            if (!wrapper.contains(event.target)) cerrar();
        });
        valorSeleccionado();
        if (select.disabled) input.disabled = true;
    };

    const inicializar = raiz => raiz.querySelectorAll('form select').forEach(mejorarSelect);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => inicializar(document));
    } else {
        inicializar(document);
    }

    new MutationObserver(registros => registros.forEach(registro => registro.addedNodes.forEach(nodo => {
        if (nodo.nodeType !== Node.ELEMENT_NODE) return;
        if (nodo.matches?.('form select')) mejorarSelect(nodo);
        inicializar(nodo);
    }))).observe(document.documentElement, { childList: true, subtree: true });
})();
