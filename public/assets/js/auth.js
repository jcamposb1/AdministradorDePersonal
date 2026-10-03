document.addEventListener('DOMContentLoaded', () => {
    const formLogin = document.getElementById('form-login');
    const inputPass = document.getElementById('password');
    const btnToggle = document.getElementById('btn-toggle-pass');
    const btnSubmit = document.getElementById('btn-submit');
    const alertaBox = document.getElementById('alerta-mensaje');

    // 1. Mostrar/Ocultar contraseña
    btnToggle.addEventListener('click', () => {
        const esPassword = inputPass.type === 'password';
        inputPass.type = esPassword ? 'text' : 'password';
        btnToggle.innerHTML = esPassword 
            ? '<i class="fa-regular fa-eye-slash"></i>' 
            : '<i class="fa-regular fa-eye"></i>';
    });

    // 2. Manejo asíncrono del formulario (Fetch API)
    formLogin.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(formLogin);
        mostrarAlerta(false);
        bloquearBoton(true);

        try {
            const respuesta = await fetch(`${BASE_URL}auth/autenticar`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const datos = await respuesta.json();

            if (datos.status === 'success') {
                mostrarAlerta(true, datos.message, 'success');
                setTimeout(() => {
                    window.location.href = datos.redirect;
                }, 1200);
            } else {
                mostrarAlerta(true, datos.message, 'error');
                bloquearBoton(false);
            }
        } catch (error) {
            mostrarAlerta(true, 'Ocurrió un error inesperado al conectar con el servidor.', 'error');
            bloquearBoton(false);
        }
    });

    function mostrarAlerta(mostrar, mensaje = '', tipo = 'error') {
        if (!mostrar) {
            alertaBox.className = 'alerta-box d-none';
            alertaBox.textContent = '';
            return;
        }

        alertaBox.className = `alerta-box alerta-${tipo}`;
        alertaBox.textContent = mensaje;
    }

    function bloquearBoton(bloquear) {
        btnSubmit.disabled = bloquear;
        const texto = btnSubmit.querySelector('.btn-text');
        texto.textContent = bloquear ? 'Verificando...' : 'Iniciar Sesión';
    }
});