<?= $this->extend('layouts/auth_layout') ?>

<?= $this->section('contenido') ?>
<div class="login-wrapper">
    <div class="login-card">
        
        <!-- Contenedor del Logo con Animación Ecológica -->
        <div class="logo-eco-container">
            <div class="eco-circle-pulse"></div>
            <div class="eco-circle-spin"></div>
            <img src="<?= base_url('assets/img/logo_admipersonal.jpg') ?>" alt="Logo Administración de Personal" class="logo-img">
        </div>

        <div class="login-header">
            <h2>Bienvenido</h2>
            <p>Sistema de Gestión Sostenible de Personal</p>
        </div>

        <!-- Mensajes dinámicos -->
        <div id="alerta-mensaje" class="alerta-box d-none"></div>

        <form id="form-login" autocomplete="off">
            <div class="input-group">
                <span class="input-icon"><i class="fa-solid fa-user"></i></span>
                <input type="text" id="usuario" name="usuario" placeholder="Usuario o Correo" required>
            </div>

            <div class="input-group">
                <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                <input type="password" id="password" name="password" placeholder="Contraseña" required>
                <button type="button" class="btn-toggle-pass" id="btn-toggle-pass">
                    <i class="fa-regular fa-eye"></i>
                </button>
            </div>

            <button type="submit" class="btn-login" id="btn-submit">
                <span class="btn-text">Iniciar Sesión</span>
                <i class="fa-solid fa-arrow-right-to-bracket"></i>
            </button>
        </form>

        <div class="login-footer">
            <p><i class="fa-solid fa-recycle"></i> Reutilizar &bull; Reciclar &bull; Reducir</p>
        </div>

    </div>
</div>
<?= $this->endSection() ?>