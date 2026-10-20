/**
 * Listado Grupo Trabajo: modo edición (arrastrar personas, crear/editar/eliminar mesas)
 * y buscador. Las rutas y el token CSRF llegan desde la vista (atributos data-* y <meta>).
 */
(function () {
    'use strict';

    const raiz = document.getElementById('listado-grupos');
    if (!raiz) {
        return;
    }

    const urls = {
        mover:    raiz.dataset.urlMover,
        guardar:  raiz.dataset.urlGuardar,
        eliminar: raiz.dataset.urlEliminar,
        buscar:   raiz.dataset.urlBuscar,
        login:    raiz.dataset.urlLogin,
    };

    const metaToken = document.querySelector('meta[name="csrf-token"]');
    const cabeceraCsrf = document.querySelector('meta[name="csrf-header"]').content;
    let tokenCsrf = metaToken.content;

    // ------------------------------------------------------------------
    // Utilidades: peticiones, avisos y recarga
    // ------------------------------------------------------------------

    async function peticion(url, datos) {
        try {
            const respuesta = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    [cabeceraCsrf]: tokenCsrf,
                },
                body: JSON.stringify(datos || {}),
            });

            let cuerpo;
            try {
                cuerpo = await respuesta.json();
            } catch (e) {
                cuerpo = { ok: false, mensaje: 'El servidor devolvió una respuesta inesperada. Recarga la página e intenta de nuevo.' };
            }

            if (cuerpo.csrf) {
                tokenCsrf = cuerpo.csrf;
                metaToken.content = tokenCsrf;
            }
            if (respuesta.status === 401) {
                window.location.href = urls.login;
            }

            return cuerpo;
        } catch (error) {
            return { ok: false, mensaje: 'No se pudo conectar con el servidor.' };
        }
    }

    // El token cambia en cada petición, así que se envían una por una.
    let cola = Promise.resolve();
    function enviar(url, datos) {
        const turno = cola.then(function () { return peticion(url, datos); });
        cola = turno.catch(function () { /* la cola sigue aunque falle una */ });
        return turno;
    }

    const contenedorToast = document.getElementById('contenedor-toast');

    function avisar(mensaje, tipo) {
        const el = document.createElement('div');
        el.className = 'toast align-items-center border-0 text-bg-' + (tipo === 'error' ? 'danger' : 'success');
        el.setAttribute('role', 'alert');
        el.innerHTML = '<div class="d-flex"><div class="toast-body"></div>'
            + '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button></div>';
        el.querySelector('.toast-body').textContent = mensaje;
        contenedorToast.appendChild(el);
        el.addEventListener('hidden.bs.toast', function () { el.remove(); });
        new bootstrap.Toast(el, { delay: tipo === 'error' ? 6000 : 2500 }).show();
    }

    function recargarConAviso(mensaje) {
        sessionStorage.setItem('gt-scroll', String(window.scrollY));
        sessionStorage.setItem('gt-aviso', mensaje);
        window.location.reload();
    }

    (function restaurarTrasRecarga() {
        const scroll = sessionStorage.getItem('gt-scroll');
        const aviso = sessionStorage.getItem('gt-aviso');
        sessionStorage.removeItem('gt-scroll');
        sessionStorage.removeItem('gt-aviso');
        if (scroll !== null) {
            window.scrollTo(0, parseInt(scroll, 10) || 0);
        }
        if (aviso !== null) {
            avisar(aviso);
        }
    })();

    function actualizarContadorVacantes() {
        const cifra = document.getElementById('cifra-vacantes');
        if (cifra) {
            cifra.textContent = String(raiz.querySelectorAll('.vacante').length);
        }
    }

    // ------------------------------------------------------------------
    // Modo edición
    // ------------------------------------------------------------------

    const botonEdicion = document.getElementById('btn-edicion');

    function activarEdicion(activa) {
        raiz.classList.toggle('modo-edicion', activa);
        raiz.querySelectorAll('.persona').forEach(function (p) {
            p.setAttribute('draggable', activa ? 'true' : 'false');
        });
        botonEdicion.setAttribute('aria-pressed', String(activa));
        botonEdicion.querySelector('span').textContent = activa ? 'Terminar edición' : 'Editar mesas';
        sessionStorage.setItem('gt-edicion', activa ? '1' : '0');
    }

    botonEdicion.addEventListener('click', function () {
        activarEdicion(!raiz.classList.contains('modo-edicion'));
    });
    activarEdicion(sessionStorage.getItem('gt-edicion') === '1');

    // ------------------------------------------------------------------
    // Arrastrar y soltar personas entre puestos
    // ------------------------------------------------------------------

    let arrastre = null;      // { idEmpleado, fila }
    let filaMarcada = null;

    function limpiarMarcas() {
        if (filaMarcada) {
            filaMarcada.classList.remove('destino');
            filaMarcada = null;
        }
        raiz.querySelectorAll('.arrastrando').forEach(function (el) { el.classList.remove('arrastrando'); });
    }

    function intercambiarFilas(a, b) {
        ['.col-nombre', '.col-empleador'].forEach(function (selector) {
            const celdaA = a.querySelector(selector);
            const celdaB = b.querySelector(selector);
            const temporal = celdaA.innerHTML;
            celdaA.innerHTML = celdaB.innerHTML;
            celdaB.innerHTML = temporal;
        });
    }

    raiz.addEventListener('dragstart', function (e) {
        const persona = e.target.closest ? e.target.closest('.persona') : null;
        if (!persona || !raiz.classList.contains('modo-edicion')) {
            return;
        }
        arrastre = { idEmpleado: Number(persona.dataset.empleado), fila: persona.closest('tr.puesto') };
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', persona.dataset.empleado);
        persona.classList.add('arrastrando');
    });

    raiz.addEventListener('dragend', function () {
        limpiarMarcas();
        arrastre = null;
    });

    raiz.addEventListener('dragover', function (e) {
        if (!arrastre) {
            return;
        }
        const fila = e.target.closest('tr.puesto');
        if (!fila) {
            return;
        }
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        if (filaMarcada !== fila) {
            if (filaMarcada) {
                filaMarcada.classList.remove('destino');
            }
            filaMarcada = fila;
            fila.classList.add('destino');
        }
    });

    raiz.addEventListener('drop', async function (e) {
        if (!arrastre) {
            return;
        }
        const filaDestino = e.target.closest('tr.puesto');
        if (!filaDestino) {
            return;
        }
        e.preventDefault();

        const origen = arrastre;
        arrastre = null;
        limpiarMarcas();

        if (filaDestino === origen.fila) {
            return;
        }

        const ocupante = filaDestino.querySelector('.persona');
        if (ocupante) {
            const nombreOrigen = origen.fila.querySelector('.persona').textContent.trim();
            const aceptado = await confirmar({
                titulo: 'Intercambiar personas',
                contenido: contenidoIntercambio(nombreOrigen, ocupante.textContent.trim()),
                textoAceptar: 'Intercambiar',
                peligro: false,
            });

            if (!aceptado) {
                return;
            }
        }

        const resultado = await enviar(urls.mover, {
            IDempleado: origen.idEmpleado,
            IDgrupoPuestoDestino: Number(filaDestino.dataset.puesto),
        });

        if (!resultado.ok) {
            avisar(resultado.mensaje || 'No se pudo mover a la persona.', 'error');
            return;
        }

        intercambiarFilas(origen.fila, filaDestino);
        actualizarContadorVacantes();
        avisar(resultado.mensaje || 'Cambio guardado.');
    });

    // ------------------------------------------------------------------
    // Asignar una persona a un puesto vacante (búsqueda)
    // ------------------------------------------------------------------

    const modalAsignarEl = document.getElementById('modalAsignar');
    const modalAsignar = new bootstrap.Modal(modalAsignarEl);
    const campoBuscarPersona = document.getElementById('asignar-buscar');
    const listaResultados = document.getElementById('asignar-resultados');
    const avisoSinResultados = document.getElementById('asignar-vacio');
    let filaAsignar = null;
    let temporizador = null;

    function abrirAsignar(fila) {
        filaAsignar = fila;
        document.getElementById('asignar-cargo').textContent = fila.querySelector('.col-cargo').textContent.trim();
        campoBuscarPersona.value = '';
        listaResultados.innerHTML = '';
        avisoSinResultados.classList.add('d-none');
        modalAsignar.show();
    }

    modalAsignarEl.addEventListener('shown.bs.modal', function () { campoBuscarPersona.focus(); });

    campoBuscarPersona.addEventListener('input', function () {
        clearTimeout(temporizador);
        temporizador = setTimeout(buscarPersonal, 250);
    });

    async function buscarPersonal() {
        const texto = campoBuscarPersona.value.trim();
        listaResultados.innerHTML = '';
        avisoSinResultados.classList.add('d-none');
        if (texto.length < 2) {
            return;
        }

        let datos;
        try {
            const respuesta = await fetch(urls.buscar + '?q=' + encodeURIComponent(texto), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (respuesta.status === 401) {
                window.location.href = urls.login;
                return;
            }
            datos = await respuesta.json();
        } catch (error) {
            avisar('No se pudo buscar. Revisa tu conexión.', 'error');
            return;
        }

        if (texto !== campoBuscarPersona.value.trim()) {
            return; // el usuario siguió escribiendo
        }

        const resultados = datos.resultados || [];
        avisoSinResultados.classList.toggle('d-none', resultados.length > 0);

        resultados.forEach(function (p) {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'list-group-item list-group-item-action py-2';

            const nombre = document.createElement('div');
            nombre.className = 'fw-semibold small';
            nombre.textContent = p.nombresApellidos;

            const detalle = document.createElement('div');
            detalle.className = 'small text-secondary';
            detalle.textContent = (p.nombreCargo || 'Sin cargo') + (p.enMesa ? ' (saldrá de su mesa actual)' : '');

            item.append(nombre, detalle);
            item.addEventListener('click', function () { asignarPersona(p.IDempleado); });
            listaResultados.appendChild(item);
        });
    }

    async function asignarPersona(idEmpleado) {
        const resultado = await enviar(urls.mover, {
            IDempleado: idEmpleado,
            IDgrupoPuestoDestino: Number(filaAsignar.dataset.puesto),
        });

        if (!resultado.ok) {
            avisar(resultado.mensaje || 'No se pudo asignar a la persona.', 'error');
            return;
        }

        modalAsignar.hide();
        recargarConAviso(resultado.mensaje || 'Persona asignada.');
    }

    // ------------------------------------------------------------------
    // Crear / editar mesa
    // ------------------------------------------------------------------

    const modalGrupoEl = document.getElementById('modalGrupo');
    const modalGrupo = new bootstrap.Modal(modalGrupoEl);
    const formGrupo = document.getElementById('formGrupo');
    const tituloGrupo = document.getElementById('titulo-modal-grupo');
    const campoId = document.getElementById('grupo-id');
    const selectArea = document.getElementById('grupo-area');
    const selectSubarea = document.getElementById('grupo-subarea');
    const selectEscuelita = document.getElementById('grupo-escuelita');
    const campoLetra = document.getElementById('grupo-letra');
    const contenedorPuestos = document.getElementById('grupo-puestos');
    const errorGrupo = document.getElementById('grupo-error');
    const botonGuardar = document.getElementById('btn-guardar-grupo');
    const filaCopiar = document.getElementById('fila-copiar');
    const selectCopiar = document.getElementById('grupo-copiar');

    function mostrarErrorGrupo(mensaje, tipo) {
        errorGrupo.className = 'alert py-2 small alert-' + (tipo === 'aviso' ? 'warning' : 'danger');
        errorGrupo.textContent = mensaje;
    }

    function ocultarErrorGrupo() {
        errorGrupo.className = 'alert py-2 small d-none';
        errorGrupo.textContent = '';
    }

    function filtrarSubareas() {
        const area = selectArea.value;
        Array.from(selectSubarea.options).forEach(function (opcion) {
            if (opcion.value === '0') {
                return;
            }
            const visible = opcion.dataset.area === area;
            opcion.hidden = !visible;
            opcion.disabled = !visible;
        });
        const actual = selectSubarea.selectedOptions[0];
        if (actual && actual.disabled) {
            selectSubarea.value = '0';
        }
    }
    selectArea.addEventListener('change', filtrarSubareas);

    function agregarFilaPuesto(puesto) {
        const fila = document.createElement('div');
        fila.className = 'input-group input-group-sm mb-1 fila-puesto';
        fila.dataset.puesto = puesto ? String(puesto.IDgrupoPuesto) : '0';

        const campo = document.createElement('input');
        campo.type = 'text';
        campo.className = 'form-control';
        campo.maxLength = 100;
        campo.setAttribute('list', 'lista-cargos');
        campo.setAttribute('aria-label', 'Cargo del puesto');
        campo.placeholder = 'Cargo: elige uno existente o escribe uno nuevo';
        campo.value = puesto ? puesto.cargo : '';

        const quitar = document.createElement('button');
        quitar.type = 'button';
        quitar.className = 'btn btn-outline-danger';
        quitar.setAttribute('aria-label', 'Quitar puesto');
        quitar.innerHTML = '<i class="bi bi-x-lg"></i>';

        if (puesto && puesto.ocupado) {
            campo.readOnly = true;
            campo.title = 'Puesto ocupado: mueve primero a la persona para cambiarlo o quitarlo';
            quitar.disabled = true;
            quitar.title = campo.title;
        }

        quitar.addEventListener('click', function () { fila.remove(); });
        fila.append(campo, quitar);
        contenedorPuestos.appendChild(fila);
        return campo;
    }

    document.getElementById('btn-agregar-puesto').addEventListener('click', function () {
        agregarFilaPuesto(null).focus();
    });

    function leerGrupo(tarjeta) {
        return JSON.parse(tarjeta.dataset.grupo);
    }

    function llenarSelectCopiar() {
        selectCopiar.innerHTML = '<option value="0">Elige una mesa para copiar sus cargos</option>';
        raiz.querySelectorAll('.grupo[data-grupo]').forEach(function (tarjeta) {
            const g = leerGrupo(tarjeta);
            const opcion = document.createElement('option');
            opcion.value = String(g.IDgrupoTrabajo);
            opcion.textContent = g.areaNombre + (g.subareaNombre ? ' / ' + g.subareaNombre : '') + ' - mesa ' + g.letra;
            selectCopiar.appendChild(opcion);
        });
    }

    document.getElementById('btn-copiar').addEventListener('click', function () {
        const letra = campoLetra.value.trim().toUpperCase();
        if (letra === '') {
            mostrarErrorGrupo('Escribe primero la letra de la mesa nueva para copiar los cargos.');
            return;
        }
        const tarjeta = Array.from(raiz.querySelectorAll('.grupo[data-grupo]')).find(function (t) {
            return String(leerGrupo(t).IDgrupoTrabajo) === selectCopiar.value;
        });
        if (!tarjeta) {
            mostrarErrorGrupo('Elige la mesa de la que quieres copiar los cargos.');
            return;
        }

        ocultarErrorGrupo();
        contenedorPuestos.innerHTML = '';
        let sinPatron = 0;

        leerGrupo(tarjeta).puestos.forEach(function (puesto) {
            let cargo = puesto.cargo;
            const patron = /(\.\s*)([A-Za-z]{1,3})((?:-\d+)?)\s*$/;
            if (patron.test(cargo)) {
                cargo = cargo.replace(patron, function (coincidencia, punto, vieja, numero) {
                    return punto + letra + numero;
                });
            } else {
                sinPatron++;
            }
            agregarFilaPuesto({ IDgrupoPuesto: 0, cargo: cargo, ocupado: false });
        });

        if (sinPatron > 0) {
            mostrarErrorGrupo('Revisa ' + sinPatron + ' cargo(s): no terminan en letra y se copiaron sin cambios.', 'aviso');
        }
    });

    function abrirGrupo(datos, areaId, subareaId) {
        const nuevo = datos === null;

        tituloGrupo.textContent = nuevo ? 'Nueva mesa' : 'Editar mesa ' + datos.letra;
        campoId.value = nuevo ? '0' : String(datos.IDgrupoTrabajo);
        selectArea.value = String(nuevo ? (areaId || 0) : datos.IDarea);
        filtrarSubareas();
        selectSubarea.value = String(nuevo ? (subareaId || 0) : (datos.IDsubarea || 0));
        selectEscuelita.value = String(nuevo ? 0 : (datos.IDescuelita || 0));
        campoLetra.value = nuevo ? '' : datos.letra;

        contenedorPuestos.innerHTML = '';
        if (nuevo) {
            agregarFilaPuesto(null);
        } else {
            datos.puestos.forEach(function (puesto) { agregarFilaPuesto(puesto); });
        }

        filaCopiar.classList.toggle('d-none', !nuevo);
        if (nuevo) {
            llenarSelectCopiar();
        }
        ocultarErrorGrupo();
        modalGrupo.show();
    }

    modalGrupoEl.addEventListener('shown.bs.modal', function () { campoLetra.focus(); });

    formGrupo.addEventListener('submit', async function (e) {
        e.preventDefault();
        ocultarErrorGrupo();

        const puestos = Array.from(contenedorPuestos.querySelectorAll('.fila-puesto')).map(function (fila) {
            return {
                IDgrupoPuesto: Number(fila.dataset.puesto),
                cargo: fila.querySelector('input').value.trim(),
            };
        });

        botonGuardar.disabled = true;
        const resultado = await enviar(urls.guardar, {
            IDgrupoTrabajo: Number(campoId.value),
            IDarea: Number(selectArea.value),
            IDsubarea: Number(selectSubarea.value),
            IDescuelita: Number(selectEscuelita.value),
            letra: campoLetra.value.trim(),
            puestos: puestos,
        });
        botonGuardar.disabled = false;

        if (!resultado.ok) {
            mostrarErrorGrupo(resultado.mensaje || 'No se pudo guardar la mesa.');
            return;
        }

        modalGrupo.hide();
        recargarConAviso(resultado.mensaje || 'Mesa guardada.');
    });

    // ------------------------------------------------------------------
    // Eliminar mesa
    // ------------------------------------------------------------------

    async function eliminarGrupo(tarjeta) {
        const datos = leerGrupo(tarjeta);
        const personas = tarjeta.querySelectorAll('.persona').length;
                const aceptado = await confirmar({
            titulo: 'Eliminar mesa ' + datos.letra,
            contenido: contenidoEliminar(datos, personas),
            textoAceptar: 'Eliminar mesa',
            peligro: true,
        });

        if (!aceptado) {
            return;
        }

        const resultado = await enviar(urls.eliminar + '/' + datos.IDgrupoTrabajo, {});
        if (!resultado.ok) {
            avisar(resultado.mensaje || 'No se pudo eliminar la mesa.', 'error');
            return;
        }

        recargarConAviso(resultado.mensaje || 'Mesa eliminada.');
    }

    // ------------------------------------------------------------------
    // Botones de la vista (delegación)
    // ------------------------------------------------------------------

    raiz.addEventListener('click', function (e) {
        const boton = e.target.closest('[data-accion]');
        if (!boton) {
            return;
        }

        switch (boton.dataset.accion) {
            case 'asignar-persona':
                abrirAsignar(boton.closest('tr.puesto'));
                break;
            case 'nuevo-grupo':
                abrirGrupo(null, Number(boton.dataset.area), Number(boton.dataset.subarea || 0));
                break;
            case 'editar-grupo':
                abrirGrupo(leerGrupo(boton.closest('.grupo')), 0, 0);
                break;
            case 'eliminar-grupo':
                eliminarGrupo(boton.closest('.grupo'));
                break;
        }
    });

        // ------------------------------------------------------------------
    // Confirmaciones con modal propio (reemplazan a window.confirm)
    // ------------------------------------------------------------------

    const modalConfirmarEl = document.getElementById('modalConfirmar');
    const modalConfirmar = new bootstrap.Modal(modalConfirmarEl);
    const tituloConfirmar = document.getElementById('titulo-modal-confirmar');
    const cuerpoConfirmar = document.getElementById('confirmar-cuerpo');
    const botonAceptar = document.getElementById('confirmar-aceptar');

    /** Devuelve una promesa: true si la persona acepta, false si cancela o cierra. */
    function confirmar(opciones) {
        return new Promise(function (resolver) {
            let aceptado = false;

            tituloConfirmar.textContent = opciones.titulo;
            cuerpoConfirmar.replaceChildren(opciones.contenido);
            botonAceptar.textContent = opciones.textoAceptar;
            botonAceptar.className = 'btn btn-sm ' + (opciones.peligro ? 'btn-danger' : 'btn-success');

            const alAceptar = function () {
                aceptado = true;
                modalConfirmar.hide();
            };

            botonAceptar.addEventListener('click', alAceptar);
            modalConfirmarEl.addEventListener('shown.bs.modal', function () { botonAceptar.focus(); }, { once: true });
            modalConfirmarEl.addEventListener('hidden.bs.modal', function () {
                botonAceptar.removeEventListener('click', alAceptar);
                resolver(aceptado);
            }, { once: true });

            modalConfirmar.show();
        });
    }

    function crearParrafo(texto, clases) {
        const p = document.createElement('p');
        p.className = clases;
        p.textContent = texto;
        return p;
    }

    function contenidoIntercambio(nombreA, nombreB) {
        const caja = document.createElement('div');
        caja.className = 'intercambio';

        const personaA = document.createElement('div');
        personaA.className = 'intercambio-persona';
        personaA.textContent = nombreA;

        const flecha = document.createElement('i');
        flecha.className = 'bi bi-arrow-left-right intercambio-flecha';
        flecha.setAttribute('aria-hidden', 'true');

        const personaB = document.createElement('div');
        personaB.className = 'intercambio-persona';
        personaB.textContent = nombreB;

        caja.append(personaA, flecha, personaB);

        const contenido = document.createElement('div');
        contenido.append(caja, crearParrafo('Cada uno quedará en el puesto del otro.', 'small text-secondary mt-3 mb-0'));

        return contenido;
    }

    function contenidoEliminar(datos, personas) {
        const contenido = document.createElement('div');
        contenido.appendChild(crearParrafo('Vas a eliminar la mesa ' + datos.letra + ' de ' + datos.areaNombre + '.', 'mb-2'));

        if (personas > 0) {
            contenido.appendChild(crearParrafo(
                'Tiene ' + personas + ' persona(s). Conservan su cargo, pero dejarán de aparecer en el listado hasta que las asignes a otra mesa.',
                'small text-secondary mb-0'
            ));
        }

        return contenido;
    }

    // ------------------------------------------------------------------
    // Buscador del listado
    // ------------------------------------------------------------------

    const buscador = document.getElementById('buscador');
    const aviso = document.getElementById('sin-resultados');
    const normalizar = function (t) {
        return t.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    };

    buscador.addEventListener('input', function () {
        const q = normalizar(buscador.value.trim());

        raiz.querySelectorAll('.columna-grupo').forEach(function (col) {
            col.classList.toggle('d-none', q !== '' && !normalizar(col.textContent).includes(q));
        });
        raiz.querySelectorAll('.bloque-subarea').forEach(function (sub) {
            sub.classList.toggle('d-none', sub.querySelector('.columna-grupo:not(.d-none)') === null);
        });

        let hayResultados = false;
        raiz.querySelectorAll('.bloque-area').forEach(function (area) {
            const visible = area.querySelector('.columna-grupo:not(.d-none)') !== null;
            area.classList.toggle('d-none', !visible);
            hayResultados = hayResultados || visible;
        });

        aviso.classList.toggle('d-none', hayResultados || q === '');
    });
})();