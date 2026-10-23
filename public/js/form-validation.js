/**
 * Muestra la validación HTML5 mientras el usuario escribe, sin esperar al envío.
 */
document.addEventListener('DOMContentLoaded', () => {
    const mensajesPorCampo = {
        nombre: 'Solo se permiten letras y espacios.',
        apellido: 'Solo se permiten letras y espacios.',
        username: 'Solo se permiten letras, números, punto, guion y guion bajo.',
        telefono: 'Solo se permiten números, espacios, + y guion.',
    };

    function mensajeDeError(campo) {
        const v = campo.validity;
        if (v.valueMissing) return 'Este campo es obligatorio.';
        if (v.typeMismatch && campo.type === 'email') return 'Ingrese un correo electrónico válido.';
        if (v.patternMismatch) return mensajesPorCampo[campo.name] || 'El formato ingresado no está permitido.';
        if (v.tooShort) return `Debe ingresar al menos ${campo.minLength} caracteres.`;
        if (v.tooLong) return `No puede exceder ${campo.maxLength} caracteres.`;
        if (v.rangeUnderflow) return `El valor mínimo permitido es ${campo.min}.`;
        if (v.rangeOverflow) return `El valor máximo permitido es ${campo.max}.`;
        if (v.stepMismatch) return 'Ingrese un valor válido para este campo.';
        return 'El dato ingresado no es válido.';
    }

    function contenedorDeAyuda(campo) {
        let ayuda = campo.nextElementSibling;
        if (!ayuda || !ayuda.classList.contains('invalid-feedback')) {
            ayuda = document.createElement('div');
            ayuda.className = 'invalid-feedback';
            campo.insertAdjacentElement('afterend', ayuda);
        }
        return ayuda;
    }

    function validarCampo(campo, mostrarVacio = false) {
        if (campo.disabled || campo.type === 'hidden' || (!campo.value && !mostrarVacio)) {
            campo.classList.remove('is-invalid');
            return true;
        }

        if (campo.validity.valid) {
            campo.classList.remove('is-invalid');
            campo.classList.add('is-valid');
            return true;
        }

        campo.classList.remove('is-valid');
        campo.classList.add('is-invalid');
        contenedorDeAyuda(campo).textContent = mensajeDeError(campo);
        return false;
    }

    function convertirAMayusculas(campo) {
        if (!campo || typeof campo.value !== 'string') return;
        const tipo = (campo.type || '').toLowerCase();
        const tiposExcluidos = ['password', 'email', 'file', 'number', 'date', 'time', 'datetime-local', 'hidden', 'checkbox', 'radio', 'color', 'range'];
        if (tiposExcluidos.includes(tipo)) return;
        if (campo.classList.contains('keep-case') || campo.dataset.keepCase !== undefined) return;
        if (['email', 'password', 'password_confirmation', 'username', 'usu'].includes(campo.name)) return;

        if (campo.tagName === 'TEXTAREA' || tipo === 'text' || tipo === 'search' || tipo === '') {
            const start = campo.selectionStart;
            const end = campo.selectionEnd;
            const nuevoValor = campo.value.toUpperCase();
            if (campo.value !== nuevoValor) {
                campo.value = nuevoValor;
                if (start !== null && end !== null) {
                    campo.setSelectionRange(start, end);
                }
            }
        }
    }

    document.addEventListener('input', (e) => {
        if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA')) {
            convertirAMayusculas(e.target);
        }
    });

    document.querySelectorAll('form').forEach(formulario => {
        formulario.querySelectorAll('input, select, textarea').forEach(campo => {
            campo.addEventListener('input', () => {
                convertirAMayusculas(campo);
                validarCampo(campo);
            });
            campo.addEventListener('change', () => {
                convertirAMayusculas(campo);
                validarCampo(campo);
            });
            campo.addEventListener('blur', () => {
                convertirAMayusculas(campo);
                validarCampo(campo);
            });
        });

        formulario.addEventListener('submit', evento => {
            let primerInvalido = null;
            formulario.querySelectorAll('input, select, textarea').forEach(campo => {
                convertirAMayusculas(campo);
                if (!validarCampo(campo, true) && !primerInvalido) primerInvalido = campo;
            });
            if (primerInvalido) {
                evento.preventDefault();
                primerInvalido.focus();
            }
        });
    });
});
