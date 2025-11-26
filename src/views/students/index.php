<?php
declare(strict_types=1);

/**
 * Vista: listado de alumnos
 *
 * Variables esperadas:
 *  - $students : array de alumnos (id_alumno, anio_ingreso, ciclo_ingreso, nombre, semestre_actual)
 *  - $mensaje  : string|null mensaje de éxito
 *  - $error    : string|null mensaje de error
 */
?>
<section class="tb-section">
    <div class="tb-section-header">
        <div>
            <h2 class="tb-section-title">Alumnos</h2>
            <p class="tb-section-subtitle">
                Listado general de alumnos registrados en el sistema.
            </p>
        </div>
        <div>
            <a href="index.php?m=alumnos&a=create" class="tb-btn">Nuevo alumno</a>
        </div>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="tb-alert tb-alert-success">
            <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="tb-alert tb-alert-error">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if (empty($students)): ?>
        <p>No hay alumnos registrados.</p>
    <?php else: ?>
        <div class="tb-table-wrapper">
            <table class="tb-table">
                <thead>
                <tr>
                    <th>ID alumno</th>
                    <th>Nombre</th>
                    <th>Año ingreso</th>
                    <th>Ciclo</th>
                    <th>Semestre actual</th>
                    <th class="tb-col-actions">Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($students as $alumno): ?>
                    <tr>
                        <td><?= htmlspecialchars($alumno['id_alumno'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($alumno['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string)$alumno['anio_ingreso'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($alumno['ciclo_ingreso'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string)$alumno['semestre_actual'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="tb-col-actions">
                            <a href="index.php?m=alumnos&a=edit&id=<?= urlencode($alumno['id_alumno']) ?>"
                               class="tb-btn tb-btn-small">
                                Editar
                            </a>
                            <a href="index.php?m=alumnos&a=delete&id=<?= urlencode($alumno['id_alumno']) ?>"
                               class="tb-btn tb-btn-small tb-btn-danger"
                               onclick="return confirm('¿Seguro que deseas eliminar este alumno? Esta acción no se puede deshacer.');">
                                Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
