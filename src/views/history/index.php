<?php
declare(strict_types=1);

/**
 * Vista de historial académico (solo lectura).
 *
 * Variables esperadas:
 *  - $students        : lista de alumnos (para el select)
 *  - $idAlumno        : id del alumno seleccionado
 *  - $selectedStudent : datos del alumno o null
 *  - $history         : array con el historial completo
 *  - $errorMsg        : string
 *  - $successMsg      : string
 */
?>
<section class="tb-section">
    <div class="tb-section-header">
        <div>
            <h2 class="tb-section-title">Historial Académico</h2>
            <p class="tb-section-subtitle">
                Consulta las materias cursadas y calificaciones del alumno.
            </p>
        </div>
    </div>

    <?php if (!empty($errorMsg)): ?>
        <div class="tb-alert tb-alert-error">
            <?= htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($successMsg)): ?>
        <div class="tb-alert tb-alert-success">
            <?= htmlspecialchars($successMsg, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- Selector de Alumno -->
    <form method="get" action="index.php" class="tb-form tb-form-inline">
        <input type="hidden" name="m" value="historial">
        <input type="hidden" name="a" value="index">

        <div class="tb-form-row">
            <label for="id_alumno" class="tb-label">Alumno</label>
            <select id="id_alumno" name="id_alumno" class="tb-input" onchange="this.form.submit()">
                <option value="">-- Selecciona un alumno --</option>
                <?php foreach ($students as $st): ?>
                    <?php
                    $val = (string)$st['id_alumno'];
                    $sel = ($idAlumno === $val) ? 'selected' : '';
                    $lbl = sprintf('%s - %s', $st['nombre'], $st['id_alumno']);
                    ?>
                    <option value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>" <?= $sel ?>>
                        <?= htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <?php if ($selectedStudent): ?>
        <hr style="margin: 16px 0; border: none; border-top: 1px solid rgba(148,163,184,0.4);">

        <div class="tb-group-info">
            <p>
                <strong>Alumno:</strong>
                <?= htmlspecialchars($selectedStudent['nombre'], ENT_QUOTES, 'UTF-8') ?>
                &nbsp;–&nbsp;
                <?= htmlspecialchars($selectedStudent['id_alumno'], ENT_QUOTES, 'UTF-8') ?>
            </p>
            <p>
                <strong>Semestre actual:</strong>
                <?= htmlspecialchars((string)$selectedStudent['semestre_actual'], ENT_QUOTES, 'UTF-8') ?>
            </p>

        </div>

        <?php if (empty($history)): ?>
            <div class="tb-alert tb-alert-info" style="margin-top: 16px;">
                El alumno no tiene materias registradas en su historial.
            </div>
        <?php else: ?>
            <div class="tb-table-wrapper" style="margin-top: 16px;">
                <table class="tb-table">
                    <thead>
                    <tr>
                        <th>Periodo</th>
                        <th>Clave</th>
                        <th>Materia</th>
                        <th>Sem</th>
                        <th>Calif</th>
                        <th>Tipo</th>
                        <th>Estatus</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($history as $row): ?>
                        <?php
                        // Formatear periodo
                        $periodoStr = htmlspecialchars($row['periodo_desc'], ENT_QUOTES, 'UTF-8')
                                    . ' (' . htmlspecialchars($row['id_periodo'], ENT_QUOTES, 'UTF-8') . ')';
                        
                        // Estatus legible
                        $estatusMap = [
                            'A' => 'Aprobada',
                            'R' => 'Reprobada',
                            'C' => 'Cursando'
                        ];
                        $estatus = $estatusMap[$row['estatus']] ?? $row['estatus'];

                        // Color de fila según estatus
                        $rowStyle = '';
                        if ($row['estatus'] === 'R') {
                            $rowStyle = 'background-color: rgba(239, 68, 68, 0.05);'; // Rojo muy suave
                        } elseif ($row['estatus'] === 'C') {
                            $rowStyle = 'background-color: rgba(59, 130, 246, 0.05);'; // Azul muy suave
                        }
                        ?>
                        <tr style="<?= $rowStyle ?>">
                            <td><?= $periodoStr ?></td>
                            <td><?= htmlspecialchars($row['clave_materia'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['materia'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$row['semestre_materia'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td style="font-weight: 600;">
                                <?= $row['calificacion'] !== null ? htmlspecialchars((string)$row['calificacion'], ENT_QUOTES, 'UTF-8') : '-' ?>
                            </td>
                            <td>
                                <?= $row['tipo_acred'] !== null ? htmlspecialchars((string)$row['tipo_acred'], ENT_QUOTES, 'UTF-8') : '-' ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($estatus, ENT_QUOTES, 'UTF-8') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</section>
