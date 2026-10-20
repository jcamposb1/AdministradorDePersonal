<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-header" content="<?= csrf_header() ?>">
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

    <main id="listado-grupos"
          class="container-fluid px-4 py-4"
          data-url-mover="<?= base_url('grupos-trabajo/mover-persona') ?>"
          data-url-guardar="<?= base_url('grupos-trabajo/guardar') ?>"
          data-url-eliminar="<?= base_url('grupos-trabajo/eliminar') ?>"
          data-url-buscar="<?= base_url('grupos-trabajo/buscar-personal') ?>"
          data-url-login="<?= base_url('/') ?>">

        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">Listado Grupo Trabajo</h3>
                <p class="text-secondary mb-0">Cuadrillas por área, con el trabajador asignado a cada puesto.</p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="chip-resumen"><strong><?= (int) $resumen['grupos'] ?></strong> grupos</span>
                <span class="chip-resumen"><strong><?= (int) $resumen['puestos'] ?></strong> puestos</span>
                <span class="chip-resumen chip-vacantes"><strong id="cifra-vacantes"><?= (int) $resumen['vacantes'] ?></strong> vacantes</span>
                <button type="button" id="btn-edicion" class="btn btn-sm btn-outline-success" aria-pressed="false">
                    <i class="bi bi-pencil-square"></i> <span>Editar mesas</span>
                </button>
            </div>
        </div>

        <div class="aviso-edicion solo-edicion mb-3" role="note">
            Arrastra a una persona sobre otro puesto para moverla; si el puesto está ocupado, se intercambian.
            Usa <i class="bi bi-person-plus"></i> en un puesto vacante para asignar a alguien.
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
                <h2 class="barra-area">
                    <span><?= esc($area['nombre']) ?></span>
                    <button type="button" class="btn btn-sm btn-light py-0 solo-edicion"
                            data-accion="nuevo-grupo" data-area="<?= (int) $area['IDarea'] ?>" data-subarea="0">
                        <i class="bi bi-plus-lg"></i> Nueva mesa
                    </button>
                </h2>

                <?php foreach ($area['subareas'] as $sub): ?>
                    <div class="bloque-subarea">
                        <?php if ($sub['IDsubarea'] !== 0): ?>
                            <h3 class="titulo-subarea">
                                <span>
                                    <?= esc($sub['nombre']) ?>
                                    <?php if (!empty($sub['descripcion'])): ?>
                                        <small><?= esc($sub['descripcion']) ?></small>
                                    <?php endif; ?>
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 solo-edicion"
                                        data-accion="nuevo-grupo" data-area="<?= (int) $area['IDarea'] ?>" data-subarea="<?= (int) $sub['IDsubarea'] ?>">
                                    <i class="bi bi-plus-lg"></i> Nueva mesa
                                </button>
                            </h3>
                        <?php endif; ?>

                        <div class="row g-3">
                            <?php foreach ($sub['grupos'] as $grupo): ?>
                                <?php
                                    $puestos      = array_values($grupo['puestos']);
                                    $conEscuelita = !empty($grupo['escuelita']);
                                    $datosGrupo   = [
                                        'IDgrupoTrabajo' => $grupo['IDgrupoTrabajo'],
                                        'IDarea'         => $grupo['IDarea'],
                                        'IDsubarea'      => $grupo['IDsubarea'],
                                        'IDescuelita'    => $grupo['IDescuelita'],
                                        'letra'          => $grupo['letra'],
                                        'areaNombre'     => $grupo['areaNombre'],
                                        'subareaNombre'  => $grupo['subareaNombre'],
                                        'puestos'        => array_map(static fn(array $p): array => [
                                            'IDgrupoPuesto' => $p['IDgrupoPuesto'],
                                            'cargo'         => $p['cargo'],
                                            'ocupado'       => !$p['vacante'],
                                        ], $puestos),
                                    ];
                                ?>
                                <div class="col-12 col-md-6 col-xl-4 columna-grupo">
                                    <div class="grupo <?= $conEscuelita ? 'grupo-escuelita' : '' ?>"
                                         data-grupo="<?= esc(json_encode($datosGrupo, JSON_UNESCAPED_UNICODE), 'attr') ?>">

                                        <div class="cabecera-grupo">
                                            <?php if ($conEscuelita): ?>
                                                <div class="etiqueta-escuelita"><?= esc($grupo['escuelita']) ?></div>
                                            <?php endif; ?>
                                            <div class="acciones-grupo solo-edicion">
                                                <button type="button" class="btn btn-sm btn-outline-secondary py-0"
                                                        data-accion="editar-grupo" title="Editar mesa"
                                                        aria-label="Editar mesa <?= esc($grupo['letra']) ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger py-0"
                                                        data-accion="eliminar-grupo" title="Eliminar mesa"
                                                        aria-label="Eliminar mesa <?= esc($grupo['letra']) ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <table class="tabla-grupo">
                                            <?php if (empty($puestos)): ?>
                                                <tr>
                                                    <td class="letra-grupo"><?= esc($grupo['letra']) ?></td>
                                                    <td class="sin-puestos" colspan="3">Sin puestos asignados</td>
                                                </tr>
                                            <?php endif; ?>

                                            <?php foreach ($puestos as $i => $puesto): ?>
                                                <tr class="puesto" data-puesto="<?= (int) $puesto['IDgrupoPuesto'] ?>">
                                                    <?php if ($i === 0): ?>
                                                        <td class="letra-grupo" rowspan="<?= count($puestos) ?>"><?= esc($grupo['letra']) ?></td>
                                                    <?php endif; ?>

                                                    <td class="col-nombre"><?php if ($puesto['vacante']): ?><span class="vacante">VACANTE</span><button type="button" class="btn-asignar solo-edicion" data-accion="asignar-persona" title="Asignar persona" aria-label="Asignar persona a este puesto"><i class="bi bi-person-plus"></i></button><?php else: ?><span class="persona" data-empleado="<?= (int) $puesto['IDempleado'] ?>" draggable="false"><?= esc($puesto['nombre']) ?></span><?php endif; ?></td>

                                                    <td class="col-cargo"><?= esc($puesto['cargo']) ?></td>

                                                    <td class="col-empleador"><?php if ($puesto['tercero']): ?><span class="insignia-empleador" title="Empleador: <?= esc($puesto['empleador']) ?>"><?= esc($puesto['empleador']) ?></span><?php endif; ?></td>
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

    <!-- Crear / editar mesa -->
    <div class="modal fade" id="modalGrupo" tabindex="-1" aria-labelledby="titulo-modal-grupo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <form class="modal-content" id="formGrupo" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="titulo-modal-grupo">Mesa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="grupo-id" value="0">
                    <div id="grupo-error" class="alert py-2 small d-none" role="alert"></div>

                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="grupo-area" class="form-label small mb-1">Área</label>
                            <select id="grupo-area" class="form-select form-select-sm">
                                <option value="0">Selecciona un área</option>
                                <?php foreach ($catalogos['areas'] as $a): ?>
                                    <option value="<?= (int) $a['IDarea'] ?>"><?= esc($a['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="grupo-subarea" class="form-label small mb-1">Sub área</label>
                            <select id="grupo-subarea" class="form-select form-select-sm">
                                <option value="0">Sin sub área</option>
                                <?php foreach ($catalogos['subareas'] as $s): ?>
                                    <option value="<?= (int) $s['IDsubarea'] ?>" data-area="<?= (int) $s['IDarea'] ?>"><?= esc($s['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-8">
                            <label for="grupo-escuelita" class="form-label small mb-1">Escuelita</label>
                            <select id="grupo-escuelita" class="form-select form-select-sm">
                                <option value="0">Ninguna</option>
                                <?php foreach ($catalogos['escuelitas'] as $e): ?>
                                    <option value="<?= (int) $e['IDescuelita'] ?>"><?= esc($e['nombreEscuelita']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-4">
                            <label for="grupo-letra" class="form-label small mb-1">Letra de la mesa</label>
                            <input type="text" id="grupo-letra" class="form-control form-control-sm text-uppercase" maxlength="5" autocomplete="off">
                        </div>
                    </div>

                    <div id="fila-copiar" class="mb-3 d-none">
                        <label for="grupo-copiar" class="form-label small mb-1">Copiar los cargos de otra mesa</label>
                        <div class="input-group input-group-sm">
                            <select id="grupo-copiar" class="form-select"></select>
                            <button type="button" id="btn-copiar" class="btn btn-outline-secondary">Copiar</button>
                        </div>
                        <div class="form-text">Cambia la letra final de cada cargo por la letra de la mesa nueva.</div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="form-label small mb-0">Puestos, en el orden en que se muestran</span>
                        <button type="button" id="btn-agregar-puesto" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-plus-lg"></i> Agregar puesto
                        </button>
                    </div>
                    <div id="grupo-puestos"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn-guardar-grupo" class="btn btn-sm btn-success">Guardar mesa</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Asignar persona a un puesto vacante -->
    <div class="modal fade" id="modalAsignar" tabindex="-1" aria-labelledby="titulo-modal-asignar" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="titulo-modal-asignar">Asignar persona</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-secondary mb-2">Puesto: <strong id="asignar-cargo"></strong></p>
                    <label for="asignar-buscar" class="visually-hidden">Buscar trabajador</label>
                    <input type="search" id="asignar-buscar" class="form-control form-control-sm" placeholder="Escribe el nombre o apellido" autocomplete="off">
                    <div id="asignar-resultados" class="list-group list-group-flush mt-2"></div>
                    <p id="asignar-vacio" class="small text-secondary mt-2 d-none">Sin coincidencias.</p>
                </div>
            </div>
        </div>
    </div>
        <!-- Confirmaciones (intercambiar personas, eliminar mesa) -->
    <div class="modal fade" id="modalConfirmar" tabindex="-1" aria-labelledby="titulo-modal-confirmar" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="titulo-modal-confirmar">Confirmar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="confirmar-cuerpo"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="confirmar-aceptar" class="btn btn-sm btn-success">Aceptar</button>
                </div>
            </div>
        </div>
    </div>

    <datalist id="lista-cargos">
        <?php foreach ($catalogos['cargos'] as $cargo): ?>
            <option value="<?= esc($cargo['nombreCargo']) ?>"></option>
        <?php endforeach; ?>
    </datalist>

    <div id="contenedor-toast" class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('assets/js/grupos_trabajo.js') ?>"></script>
</body>
</html>