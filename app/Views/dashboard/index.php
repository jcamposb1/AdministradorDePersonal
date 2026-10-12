<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titulo) ?></title>
    
    <!-- Fuentes y CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- CSS Propio con base_url() correcta -->
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
</head>
<body>

    <!-- Header Corporativo SupraLive -->
    <header class="header-supra py-3">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-white rounded-circle p-1 shadow-sm" style="width: 44px; height: 44px; display:flex; align-items:center; justify-content:center;">
                    <i class="bi bi-recycle text-success fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-white">SupraLive</h5>
                    <span class="small text-white-50">Sistema de Gestión Sostenible de Personal</span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="user-pill d-flex align-items-center gap-2 text-white">
                    <i class="bi bi-person-circle fs-5"></i>
                    <span class="fw-medium small"><?= esc($usuario) ?></span>
                </div>
                <a href="<?= base_url('auth/logout') ?>" class="btn-salir text-decoration-none d-flex align-items-center gap-1">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Cerrar Sesión</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="container py-5">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4 pb-2 border-bottom">
            <div>
                <span class="brand-badge text-success bg-white border mb-2 d-inline-block">Panel de Operaciones</span>
                <h3 class="fw-bold text-dark mb-1">Módulos Administrativos</h3>
                <p class="text-secondary mb-0">Seleccione un área para gestionar los datos del personal y las jornadas.</p>
            </div>
        </div>

        <!-- Grilla de Módulos (Consumo de Objetos ModuloMenu) -->
        <div class="row g-4">
            <?php /** @var \App\DTOs\ModuloMenu $item */ ?>
            <?php foreach ($modulos as $item): ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <a href="<?= $item->getUrl() ?>" class="card-modulo acento-<?= $item->getColorAcento() ?> p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-box">
                                <i class="bi <?= esc($item->getIcono()) ?>"></i>
                            </div>
                            <?php if ($item->getEtiqueta() !== null): ?>
                                <span class="modulo-tag tag-<?= $item->getColorAcento() ?>">
                                    <?= esc($item->getEtiqueta()) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-2 text-dark"><?= esc($item->getTitulo()) ?></h5>
                            <p class="text-secondary small mb-3"><?= esc($item->getDescripcion()) ?></p>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                            <span class="small fw-semibold text-success">Acceder al módulo</span>
                            <div class="action-arrow">
                                <i class="bi bi-arrow-right-short fs-4"></i>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <footer class="py-4 text-center text-muted small mt-5">
        <p class="mb-0"><i class="bi bi-recycle text-success me-1"></i> Reutilizar &bull; Reciclar &bull; Reducir &mdash; Supra Live</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>