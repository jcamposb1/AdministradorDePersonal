<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titulo) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/grupos_trabajo.css') ?>">
</head>
<body>

    <header class="header-supra py-3">
        <div class="container-fluid px-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
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
                <a href="<?= base_url('dashboard') ?>" class="btn-salir text-decoration-none d-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i>
                    <span>Panel</span>
                </a>
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

    <main class="container-fluid px-4 py-4">

        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">Listado Grupo Trabajo</h3>
                <p class="text-secondary mb-0">Cuadrillas por área, con el trabajador asignado a cada puesto.</p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="chip-resumen"><strong><?= (int) $resumen['grupos'] ?></strong> grupos</span>
                <span class="chip-resumen"><strong><?= (int) $resumen['puestos'] ?></strong> puestos</span>
                <span class="chip-resumen chip-vacantes"><strong><?= (int) $resumen['vacantes'] ?></strong> vacantes</span>
            </div>
        </div>

        <div class="barra-herramientas mb-3">
            <label for="buscador" class="visually-hidden">Buscar trabajador o cargo</label>
            <div class="input-group input-group-sm buscador">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="search" id="buscador" class="form-control" placeholder="Buscar trabajador o cargo" autocomplete="off">
            </div>

            <nav class="nav-areas" aria-label="Áreas">
                <?php foreach ($areas as $area): ?>
                    <a href="#area-<?= (int) $area['IDarea'] ?>"><?= esc($area['nombre']) ?></a>
                <?php endforeach; ?>
            </nav>
        </div>

        <?php if (empty($areas)): ?>
            <div class="alert alert-light border">Todavía no hay grupos de trabajo registrados.</div>
        <?php endif; ?>

        <?php foreach ($areas as $area): ?>
            <section class="bloque-area" id="area-<?= (int) $area['IDarea'] ?>">
                <h2 class="barra-area"><?= esc($area['nombre']) ?></h2>

                <?php foreach ($area['subareas'] as $sub): ?>
                    <div class="bloque-subarea">
                        <?php if ($sub['IDsubarea'] !== 0): ?>
                            <h3 class="titulo-subarea">
                                <?= esc($sub['nombre']) ?>
                                <?php if (!empty($sub['descripcion'])): ?>
                                    <small><?= esc($sub['descripcion']) ?></small>
                                <?php endif; ?>
                            </h3>
                        <?php endif; ?>

                        <div class="row g-3">
                            <?php foreach ($sub['grupos'] as $grupo): ?>
                                <?php
                                    $puestos       = array_values($grupo['puestos']);
                                    $conEscuelita  = !empty($grupo['escuelita']);
                                ?>
                                <div class="col-12 col-md-6 col-xl-4 columna-grupo">
                                    <div class="grupo <?= $conEscuelita ? 'grupo-escuelita' : '' ?>">

                                        <?php if ($conEscuelita): ?>
                                            <div class="etiqueta-escuelita"><?= esc($grupo['escuelita']) ?></div>
                                        <?php endif; ?>

                                        <table class="tabla-grupo">
                                            <?php if (empty($puestos)): ?>
                                                <tr>
                                                    <td class="letra-grupo"><?= esc($grupo['letra']) ?></td>
                                                    <td class="sin-puestos" colspan="3">Sin puestos asignados</td>
                                                </tr>
                                            <?php endif; ?>

                                            <?php foreach ($puestos as $i => $puesto): ?>
                                                <tr>
                                                    <?php if ($i === 0): ?>
                                                        <td class="letra-grupo" rowspan="<?= count($puestos) ?>"><?= esc($grupo['letra']) ?></td>
                                                    <?php endif; ?>

                                                    <?php if ($puesto['vacante']): ?>
                                                        <td class="col-nombre vacante">VACANTE</td>
                                                    <?php else: ?>
                                                        <td class="col-nombre"><?= esc($puesto['nombre']) ?></td>
                                                    <?php endif; ?>

                                                    <td class="col-cargo"><?= esc($puesto['cargo']) ?></td>

                                                    <td class="col-empleador">
                                                        <?php if ($puesto['tercero']): ?>
                                                            <span class="insignia-empleador" title="Empleador: <?= esc($puesto['empleador']) ?>"><?= esc($puesto['empleador']) ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endforeach; ?>

        <p id="sin-resultados" class="text-secondary d-none">No hay trabajadores ni cargos que coincidan con la búsqueda.</p>
    </main>

    <footer class="py-4 text-center text-muted small mt-4">
        <p class="mb-0"><i class="bi bi-recycle text-success me-1"></i> Reutilizar &bull; Reciclar &bull; Reducir &mdash; Supra Live</p>
    </footer>

    <script>
        (function () {
            const buscador = document.getElementById('buscador');
            const columnas = document.querySelectorAll('.columna-grupo');
            const subareas = document.querySelectorAll('.bloque-subarea');
            const areas    = document.querySelectorAll('.bloque-area');
            const aviso    = document.getElementById('sin-resultados');

            const normalizar = (t) => t.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

            buscador.addEventListener('input', function () {
                const q = normalizar(buscador.value.trim());

                columnas.forEach(function (col) {
                    col.classList.toggle('d-none', q !== '' && !normalizar(col.textContent).includes(q));
                });

                subareas.forEach(function (sub) {
                    sub.classList.toggle('d-none', sub.querySelector('.columna-grupo:not(.d-none)') === null);
                });

                let hayResultados = false;
                areas.forEach(function (area) {
                    const visible = area.querySelector('.columna-grupo:not(.d-none)') !== null;
                    area.classList.toggle('d-none', !visible);
                    hayResultados = hayResultados || visible;
                });

                aviso.classList.toggle('d-none', hayResultados || q === '');
            });
        })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>