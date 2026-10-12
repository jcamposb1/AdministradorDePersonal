<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titulo) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
</head>
<body>

    <header class="header-supra py-3">
        <div class="container-fluid px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-light btn-sm me-2">
                    <i class="bi bi-arrow-left"></i> Volver al Menú
                </a>
                <h5 class="fw-bold mb-0 text-white">Base de Personal</h5>
            </div>
            <span class="brand-badge text-white">Módulo Maestro</span>
        </div>
    </header>

    <main class="container-fluid px-4 py-3">
        <div class="card shadow-sm border-0 rounded-4">

            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-people text-success me-2"></i> Listado General de Empleados
                </h6>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                        Mostrando <span id="contador"><?= count($empleados) ?> de <?= count($empleados) ?></span>
                    </span>
                    <button type="button" id="btn-agregar-personal" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm">
                        <i class="bi bi-person-plus-fill me-2"></i> Agregar Personal
                    </button>
                </div>
            </div>

            <!-- Filtros -->
            <div class="px-3 pb-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="filtro-buscar" class="form-control" placeholder="Buscar por nombre, cédula o cargo...">
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <select id="filtro-zona" class="form-select form-select-sm">
                            <option value="">Todas las zonas</option>
                            <?php foreach ($catalogos['zonas'] as $item): ?>
                                <option value="<?= esc($item['nombre'], 'attr') ?>"><?= esc($item['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <select id="filtro-empleador" class="form-select form-select-sm">
                            <option value="">Todos los empleadores</option>
                            <?php foreach ($catalogos['empleadores'] as $item): ?>
                                <option value="<?= esc($item['nombre'], 'attr') ?>"><?= esc($item['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <select id="filtro-estado" class="form-select form-select-sm">
                            <option value="">Todos los estados</option>
                            <?php foreach ($catalogos['estados'] as $item): ?>
                                <option value="<?= esc($item['nombre'], 'attr') ?>"><?= esc($item['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <button type="button" id="btn-limpiar" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="bi bi-x-circle me-1"></i> Limpiar filtros
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="card-body p-0">
                <div class="table-responsive contenedor-tabla">
                    <table id="tabla-personal" class="table table-hover align-middle mb-0 tabla-personal">
                        <thead>
                            <tr>
                                <th class="text-center"># ID</th>
                                <th>Cédula</th>
                                <th>Nombres y Apellidos</th>
                                <th>Empleador</th>
                                <th>Cargo</th>
                                <th>Zona</th>
                                <th>F. Nacimiento</th>
                                <th>F. Ingreso</th>
                                <th class="text-center">Años T.</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($empleados)): ?>
                                <tr>
                                    <td colspan="11" class="text-center py-4 text-muted">No hay registros de empleados.</td>
                                </tr>
                            <?php else: ?>
                                <?php /** @var \App\Entities\EmpleadoEntity $emp */ ?>
                                <?php foreach ($empleados as $emp): ?>
                                    <tr class="<?= $emp->estaActivo() ? '' : 'fila-inactiva' ?>"
                                        data-busqueda="<?= esc($emp->getNombreCompleto() . ' ' . $emp->getCedula() . ' ' . $emp->getCargo(), 'attr') ?>"
                                        data-zona="<?= esc($emp->getZona(), 'attr') ?>"
                                        data-empleador="<?= esc($emp->getEmpleador(), 'attr') ?>"
                                        data-estado="<?= esc($emp->getEstado(), 'attr') ?>">
                                        <td class="text-center fw-bold text-secondary"><?= $emp->getId() ?></td>
                                        <td><span class="badge bg-light text-dark border font-monospace"><?= esc($emp->getCedula()) ?></span></td>
                                        <td class="fw-medium text-dark">
                                            <div class="d-flex align-items-center gap-2">
                                                <?php if ($emp->getFotoUrl() !== null): ?>
                                                    <img src="<?= esc($emp->getFotoUrl(), 'attr') ?>" class="avatar-emp" alt="">
                                                <?php else: ?>
                                                    <span class="avatar-emp avatar-iniciales"><?= esc($emp->getIniciales()) ?></span>
                                                <?php endif; ?>
                                                <?= esc($emp->getNombreCompleto()) ?>
                                            </div>
                                        </td>
                                        <td><?= esc($emp->getEmpleador()) ?></td>
                                        <td class="text-secondary"><?= esc($emp->getCargo()) ?></td>
                                        <td><span class="badge badge-zona <?= $emp->getZonaClase() ?>"><?= esc($emp->getZona()) ?></span></td>
                                        <td><?= esc($emp->getFechaNacimientoFormateada()) ?></td>
                                        <td><?= esc($emp->getFechaIngresoFormateada()) ?></td>
                                        <td class="text-center fw-semibold"><?= number_format($emp->getAniosTrabajados(), 2) ?></td>
                                        <td class="text-center">
                                            <?php $claseEstado = $emp->estaActivo() ? 'success' : 'danger'; ?>
                                            <span class="badge badge-estado bg-<?= $claseEstado ?>-subtle text-<?= $claseEstado ?>">
                                                <?= esc($emp->getEstado()) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button"
                                                    class="btn btn-sm btn-estado <?= $emp->estaActivo() ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                                                    data-id="<?= $emp->getId() ?>"
                                                    title="<?= $emp->estaActivo() ? 'Inactivar' : 'Activar' ?>">
                                                <i class="bi <?= $emp->estaActivo() ? 'bi-person-dash' : 'bi-person-check' ?>"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar"
                                                    data-id="<?= $emp->getId() ?>"
                                                    data-nombre="<?= esc($emp->getNombreCompleto(), 'attr') ?>"
                                                    title="Eliminar">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr id="sin-resultados" class="d-none">
                                    <td colspan="11" class="text-center py-4 text-muted">
                                        <i class="bi bi-search me-1"></i> Ningún empleado coincide con los filtros.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- ========== MODAL: AGREGAR PERSONAL ========== -->
    <div class="modal fade" id="modal-personal" tabindex="-1" aria-labelledby="modal-personal-titulo" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">

                <div class="modal-header modal-header-supra text-white">
                    <h5 class="modal-title fw-bold" id="modal-personal-titulo">
                        <i class="bi bi-person-plus-fill me-2"></i> Nuevo Personal
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form id="form-personal" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-body p-4">
                        <div id="modal-alerta" class="alert alert-danger py-2 d-none"></div>

                        <div class="row g-4">
                            <!-- Foto opcional -->
                            <div class="col-md-4 text-center">
                                <label for="m-foto" id="foto-preview" class="foto-upload">
                                    <i class="bi bi-camera-fill"></i>
                                    <span>Foto (opcional)</span>
                                </label>
                                <input type="file" name="foto" id="m-foto" class="d-none" accept="image/jpeg,image/png,image/webp">
                                <button type="button" id="btn-quitar-foto" class="btn btn-link btn-sm text-danger d-none">
                                    <i class="bi bi-x-circle"></i> Quitar foto
                                </button>
                                <div class="form-text">JPG, PNG o WEBP. Máximo 2 MB.</div>
                            </div>

                            <!-- Datos -->
                            <div class="col-md-8">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="m-nombres" class="form-label fw-semibold small mb-1">Nombres y apellidos *</label>
                                        <input type="text" class="form-control" id="m-nombres" name="nombresApellidos" maxlength="150" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="m-cedula" class="form-label fw-semibold small mb-1">Cédula *</label>
                                        <input type="text" class="form-control font-monospace" id="m-cedula" name="cedula" maxlength="10" inputmode="numeric" required>
                                        <div class="form-text">Si tiene 9 dígitos, se completa con 0.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="m-nacimiento" class="form-label fw-semibold small mb-1">Fecha de nacimiento</label>
                                        <input type="date" class="form-control" id="m-nacimiento" name="fechaNacimiento">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="m-empleador" class="form-label fw-semibold small mb-1">Empleador *</label>
                                        <select class="form-select" id="m-empleador" name="IDempleador" required>
                                            <option value="" selected disabled>Selecciona...</option>
                                            <?php foreach ($catalogos['empleadores'] as $item): ?>
                                                <option value="<?= (int) $item['id'] ?>"><?= esc($item['nombre']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="m-zona" class="form-label fw-semibold small mb-1">Zona *</label>
                                        <select class="form-select" id="m-zona" name="IDzona" required>
                                            <option value="" selected disabled>Selecciona...</option>
                                            <?php foreach ($catalogos['zonas'] as $item): ?>
                                                <option value="<?= (int) $item['id'] ?>"><?= esc($item['nombre']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="m-cargo" class="form-label fw-semibold small mb-1">Cargo *</label>
                                        <select class="form-select" id="m-cargo" name="IDcargo" required>
                                            <option value="" selected disabled>Selecciona...</option>
                                            <?php foreach ($catalogos['cargos'] as $item): ?>
                                                <option value="<?= (int) $item['id'] ?>"><?= esc($item['nombre']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="m-ingreso" class="form-label fw-semibold small mb-1">Fecha de ingreso *</label>
                                        <input type="date" class="form-control" id="m-ingreso" name="fechaIngreso" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 bg-light">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" id="btn-guardar" class="btn btn-success rounded-pill px-4 fw-semibold">
                            <i class="bi bi-check-circle me-2"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========== MODAL: CONFIRMAR ELIMINAR ========== -->
    <div class="modal fade" id="modal-eliminar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 rounded-4 shadow-lg text-center p-3">
                <div class="modal-body">
                    <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 2.5rem;"></i>
                    <h6 class="fw-bold mt-2">¿Eliminar este empleado?</h6>
                    <p class="mb-1 fw-semibold text-dark" id="eliminar-nombre"></p>
                    <p class="small text-muted">Esta acción no se puede deshacer. Si solo terminó su temporada, mejor inactívalo.</p>
                    <div class="d-flex gap-2 justify-content-center mt-3">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger rounded-pill px-4" id="btn-confirmar-eliminar">Sí, eliminar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Aviso emergente -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="toast-app" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body fw-medium" id="toast-mensaje"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>const BASE_URL = "<?= base_url() ?>";</script>
    <script src="<?= base_url('assets/js/personal.js') ?>"></script>
</body>
</html>