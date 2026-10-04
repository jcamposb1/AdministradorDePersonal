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
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-light btn-sm me-2">
                    <i class="bi bi-arrow-left"></i> Volver al Menú
                </a>
                <h5 class="fw-bold mb-0 text-white">Base de Personal</h5>
            </div>
            <span class="brand-badge text-white">Módulo Maestro</span>
        </div>
    </header>

    <main class="container py-4">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-people text-success me-2"></i> Listado General de Empleados
                </h6>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                    Total: <?= count($empleados) ?> registros
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 70px;"># ID</th>
                                <th>Cédula</th>
                                <th>Nombres y Apellidos</th>
                                <th>Empleador</th>
                                <th>Cargo</th>
                                <th>Zona</th>
                                <th>F. Ingreso</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($empleados)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No hay registros de empleados.</td>
                                </tr>
                            <?php else: ?>
                                <?php /** @var \App\Entities\EmpleadoEntity $emp */ ?>
                                <?php foreach ($empleados as $emp): ?>
                                    <tr>
                                        <td class="text-center fw-bold text-secondary"><?= $emp->getId() ?></td>
                                        <td><span class="badge bg-light text-dark border font-monospace"><?= esc($emp->getCedula()) ?></span></td>
                                        <td class="fw-medium text-dark"><?= esc($emp->getNombreCompleto()) ?></td>
                                        <td><i class="bi bi-building text-muted me-1"></i> <?= esc($emp->getEmpleador()) ?></td>
                                        <td><span class="text-secondary"><?= esc($emp->getCargo()) ?></span></td>
                                        <td><span class="badge bg-secondary-subtle text-secondary"><?= esc($emp->getZona()) ?></span></td>
                                        <td><?= esc($emp->getFechaIngresoFormateada()) ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-success-subtle text-success">
                                                <?= esc($emp->getEstado()) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

</body>
</html>