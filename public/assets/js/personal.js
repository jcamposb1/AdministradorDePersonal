document.addEventListener('DOMContentLoaded', () => {
    // ---------- Referencias ----------
    const tabla        = document.getElementById('tabla-personal');
    const buscador     = document.getElementById('filtro-buscar');
    const selZona      = document.getElementById('filtro-zona');
    const selEmpleador = document.getElementById('filtro-empleador');
    const selEstado    = document.getElementById('filtro-estado');
    const contador     = document.getElementById('contador');

    const obtenerFilas = () => tabla.querySelectorAll('tbody tr[data-busqueda]');

    // Quita tildes y pasa a minúsculas: "Pérez" coincide con "perez"
    const normalizar = (texto) =>
        texto.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

    obtenerFilas().forEach((fila) => {
        fila.dataset.busqueda = normalizar(fila.dataset.busqueda);
    });

    // ---------- Utilidades ----------
    async function enviar(url, body = null) {
        const respuesta = await fetch(`${BASE_URL}${url}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body
        });
        return respuesta.json();
    }

    const toastEl = document.getElementById('toast-app');
    const toast   = new bootstrap.Toast(toastEl, { delay: 3000 });

    function avisar(mensaje, tipo = 'success') {
        toastEl.className = `toast align-items-center border-0 text-bg-${tipo}`;
        document.getElementById('toast-mensaje').textContent = mensaje;
        toast.show();
    }

    // ---------- Filtros ----------
    function filtrar() {
        const texto = normalizar(buscador.value.trim());
        const filas = obtenerFilas();
        let visibles = 0;

        filas.forEach((fila) => {
            const coincide =
                (texto === '' || fila.dataset.busqueda.includes(texto)) &&
                (selZona.value === '' || fila.dataset.zona === selZona.value) &&
                (selEmpleador.value === '' || fila.dataset.empleador === selEmpleador.value) &&
                (selEstado.value === '' || fila.dataset.estado === selEstado.value);

            fila.classList.toggle('d-none', !coincide);
            if (coincide) visibles++;
        });

        contador.textContent = `${visibles} de ${filas.length}`;

        const sinResultados = document.getElementById('sin-resultados');
        if (sinResultados) {
            sinResultados.classList.toggle('d-none', visibles !== 0 || filas.length === 0);
        }
    }

    buscador.addEventListener('input', filtrar);
    [selZona, selEmpleador, selEstado].forEach((sel) => sel.addEventListener('change', filtrar));

    document.getElementById('btn-limpiar').addEventListener('click', () => {
        buscador.value = '';
        selZona.value = '';
        selEmpleador.value = '';
        selEstado.value = '';
        filtrar();
    });

    // ---------- Modal: agregar personal ----------
    const modalPersonalEl = document.getElementById('modal-personal');
    const modalPersonal   = new bootstrap.Modal(modalPersonalEl);
    const formPersonal    = document.getElementById('form-personal');
    const alertaModal     = document.getElementById('modal-alerta');
    const btnGuardar      = document.getElementById('btn-guardar');
    const inputFoto       = document.getElementById('m-foto');
    const previewFoto     = document.getElementById('foto-preview');
    const btnQuitarFoto   = document.getElementById('btn-quitar-foto');
    const inputCedula     = document.getElementById('m-cedula');

    function mostrarErrorModal(mensaje) {
        alertaModal.textContent = mensaje;
        alertaModal.classList.toggle('d-none', mensaje === '');
    }

    function bloquearGuardar(bloquear) {
        btnGuardar.disabled = bloquear;
        btnGuardar.innerHTML = bloquear
            ? '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...'
            : '<i class="bi bi-check-circle me-2"></i>Guardar';
    }

    function quitarFoto() {
        inputFoto.value = '';
        previewFoto.style.backgroundImage = '';
        previewFoto.classList.remove('tiene-foto');
        btnQuitarFoto.classList.add('d-none');
    }

    document.getElementById('btn-agregar-personal').addEventListener('click', () => {
        formPersonal.reset();
        quitarFoto();
        mostrarErrorModal('');
        bloquearGuardar(false);
        modalPersonal.show();
    });

    modalPersonalEl.addEventListener('shown.bs.modal', () => {
        document.getElementById('m-nombres').focus();
    });

    // La cédula solo acepta números
    inputCedula.addEventListener('input', () => {
        inputCedula.value = inputCedula.value.replace(/\D/g, '');
    });

    // Vista previa de la foto
    inputFoto.addEventListener('change', () => {
        const archivo = inputFoto.files[0];
        if (!archivo) {
            quitarFoto();
            return;
        }

        const tiposOk = ['image/jpeg', 'image/png', 'image/webp'];
        if (!tiposOk.includes(archivo.type) || archivo.size > 2 * 1024 * 1024) {
            quitarFoto();
            mostrarErrorModal('La foto debe ser JPG, PNG o WEBP de máximo 2 MB.');
            return;
        }

        mostrarErrorModal('');
        previewFoto.style.backgroundImage = `url(${URL.createObjectURL(archivo)})`;
        previewFoto.classList.add('tiene-foto');
        btnQuitarFoto.classList.remove('d-none');
    });

    btnQuitarFoto.addEventListener('click', quitarFoto);

    formPersonal.addEventListener('submit', async (e) => {
        e.preventDefault();
        mostrarErrorModal('');
        bloquearGuardar(true);

        try {
            const datos = await enviar('personal/guardar', new FormData(formPersonal));

            if (datos.status === 'success') {
                modalPersonal.hide();
                avisar(datos.message);
                setTimeout(() => window.location.reload(), 900);
            } else {
                mostrarErrorModal(datos.message);
                bloquearGuardar(false);
            }
        } catch (error) {
            console.error('Error al guardar personal:', error);
            mostrarErrorModal('Ocurrió un error inesperado al conectar con el servidor.');
            bloquearGuardar(false);
        }
    });

    // ---------- Activar / Inactivar ----------
    function aplicarEstado(fila, activo, nombreEstado) {
        fila.dataset.estado = nombreEstado;
        fila.classList.toggle('fila-inactiva', !activo);

        const badge = fila.querySelector('.badge-estado');
        badge.textContent = nombreEstado;
        badge.className = `badge badge-estado ${activo ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'}`;

        const boton = fila.querySelector('.btn-estado');
        boton.className = `btn btn-sm btn-estado ${activo ? 'btn-outline-warning' : 'btn-outline-success'}`;
        boton.title = activo ? 'Inactivar' : 'Activar';
        boton.innerHTML = `<i class="bi ${activo ? 'bi-person-dash' : 'bi-person-check'}"></i>`;
    }

    async function alternarEstado(boton) {
        const fila = boton.closest('tr');
        boton.disabled = true;

        try {
            const datos = await enviar(`personal/estado/${boton.dataset.id}`);

            if (datos.status === 'success') {
                aplicarEstado(fila, datos.activo, datos.estado);
                avisar(datos.message);
                filtrar();
            } else {
                avisar(datos.message, 'danger');
            }
        } catch (error) {
            console.error('Error al cambiar estado:', error);
            avisar('Ocurrió un error inesperado al conectar con el servidor.', 'danger');
        } finally {
            boton.disabled = false;
        }
    }

    // ---------- Eliminar ----------
    const modalEliminar = new bootstrap.Modal(document.getElementById('modal-eliminar'));
    let filaAEliminar = null;

    document.getElementById('btn-confirmar-eliminar').addEventListener('click', async (e) => {
        if (!filaAEliminar) return;

        const boton = e.currentTarget;
        boton.disabled = true;

        try {
            const id = filaAEliminar.querySelector('.btn-eliminar').dataset.id;
            const datos = await enviar(`personal/eliminar/${id}`);
            modalEliminar.hide();

            if (datos.status === 'success') {
                filaAEliminar.remove();
                avisar(datos.message);
                filtrar();
            } else {
                avisar(datos.message, 'danger');
            }
        } catch (error) {
            console.error('Error al eliminar:', error);
            modalEliminar.hide();
            avisar('Ocurrió un error inesperado al conectar con el servidor.', 'danger');
        } finally {
            boton.disabled = false;
            filaAEliminar = null;
        }
    });

    // ---------- Clicks de la tabla (un solo listener para todas las filas) ----------
    tabla.addEventListener('click', (e) => {
        const btnEstado = e.target.closest('.btn-estado');
        if (btnEstado) {
            alternarEstado(btnEstado);
            return;
        }

        const btnEliminar = e.target.closest('.btn-eliminar');
        if (btnEliminar) {
            filaAEliminar = btnEliminar.closest('tr');
            document.getElementById('eliminar-nombre').textContent = btnEliminar.dataset.nombre;
            modalEliminar.show();
        }
    });
});