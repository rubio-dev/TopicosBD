<?php
declare(strict_types=1);

/**
 * Vista principal de cargas académicas.
 *
 * Variables esperadas:
 *  - $students          : lista de alumnos
 *  - $periods           : lista de periodos
 *  - $idAlumno          : id de alumno buscado (string)
 *  - $idPeriodo         : id de periodo buscado (string)
 *  - $selectedStudent   : array|null
 *  - $selectedPeriod    : array|null
 *  - $activeLoad        : array|null
 *  - $loadDetails       : array
 *  - $groupsForSemester : array
 *  - $errorMsg          : string
 *  - $successMsg        : string
 */
?>
<section class="tb-section">
    <div class="tb-section-header">
        <div>
            <h2 class="tb-section-title">Cargas académicas</h2>
            <p class="tb-section-subtitle">
                Selecciona un alumno y periodo para consultar o generar su carga.
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

    <!-- Filtro: alumno + periodo -->
    <form method="get" action="index.php" class="tb-form tb-form-inline">
        <input type="hidden" name="m" value="cargas">
        <input type="hidden" name="a" value="index">

        <div class="tb-form-row">
            <label for="id_alumno" class="tb-label">Alumno</label>
            <select id="id_alumno" name="id_alumno" class="tb-input">
                <option value="">-- Selecciona un alumno --</option>
                <?php foreach ($students as $st): ?>
                    <?php
                    $value    = (string)$st['id_alumno'];
                    $selected = ($idAlumno === $value) ? 'selected' : '';
                    $label    = sprintf(
                        '%s - %s - %d',
                        (string)$st['nombre'],
                        (string)$st['id_alumno'],
                        (int)$st['semestre_actual']
                    );
                    ?>
                    <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= $selected ?>>
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="tb-form-row">
            <label for="id_periodo" class="tb-label">Periodo lectivo</label>
            <select id="id_periodo" name="id_periodo" class="tb-input">
                <?php foreach ($periods as $p): ?>
                    <?php
                    $value    = (string)$p['id_periodo'];
                    $selected = (isset($idPeriodo) && $idPeriodo === $value) ? 'selected' : '';
                    ?>
                    <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= $selected ?>>
                        <?= htmlspecialchars($p['descripcion'], ENT_QUOTES, 'UTF-8') ?>
                        (<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="tb-form-row">
            <button type="submit" class="tb-btn">Buscar carga</button>
        </div>
    </form>

    <?php if (!empty($selectedStudent) && !empty($selectedPeriod)): ?>
        <hr style="margin: 14px 0; border: none; border-top: 1px solid rgba(148,163,184,0.4);">

        <!-- Resumen de alumno / periodo -->
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
            <p>
                <strong>Periodo:</strong>
                <?= htmlspecialchars($selectedPeriod['descripcion'], ENT_QUOTES, 'UTF-8') ?>
                (<?= htmlspecialchars($selectedPeriod['id_periodo'], ENT_QUOTES, 'UTF-8') ?>)
            </p>
        </div>

        <?php if ($activeLoad !== null): ?>
            <!-- Carga activa -->
            <h3 class="tb-groups-title">Carga activa en este periodo</h3>

            <p style="font-size: 13px; margin-top: 2px;">
                <strong>ID carga:</strong>
                <?= htmlspecialchars((string)$activeLoad['id_carga'], ENT_QUOTES, 'UTF-8') ?>
                · <strong>Fecha alta:</strong>
                <?= htmlspecialchars((string)$activeLoad['fecha_alta'], ENT_QUOTES, 'UTF-8') ?>
                · <strong>Estatus:</strong>
                <?= htmlspecialchars($activeLoad['estatus'], ENT_QUOTES, 'UTF-8') ?>
            </p>

            <?php if (!empty($loadDetails)): ?>
                <div class="tb-table-wrapper">
                    <table class="tb-table">
                        <thead>
                        <tr>
                            <th>Clave</th>
                            <th>Materia</th>
                            <th>Grupo</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($loadDetails as $fila): ?>
                            <tr>
                                <td><?= htmlspecialchars($fila['clave_materia'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($fila['materia'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    Semestre <?= htmlspecialchars((string)$fila['semestre'], ENT_QUOTES, 'UTF-8') ?>
                                    · <?= htmlspecialchars($fila['paquete'], ENT_QUOTES, 'UTF-8') ?>
                                    · <?= htmlspecialchars($fila['letra_grupo'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>La carga activa no tiene materias en el detalle.</p>
            <?php endif; ?>

            <p style="font-size: 12px; opacity: 0.8; margin-top: 8px;">
                (Acciones como cancelar carga o quitar materias las podemos agregar después.)
            </p>

        <?php else: ?>
            <!-- No hay carga activa -->
            <h3 class="tb-groups-title">No hay carga activa en este periodo</h3>
            <p style="font-size: 13px;">
                Puedes generar una carga automática si el alumno es regular<br>
                o más adelante usar una carga manual para alumnos irregulares.
            </p>

            <?php if (!empty($groupsForSemester)): ?>
                <form method="post"
                      action="index.php?m=cargas&a=createAuto"
                      class="tb-form">
                    <input type="hidden" name="id_alumno"
                           value="<?= htmlspecialchars($idAlumno, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="id_periodo"
                           value="<?= htmlspecialchars($idPeriodo, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="tb-form-row">
                        <label for="id_grupo" class="tb-label">
                            Grupo para carga automática
                        </label>
                        <select id="id_grupo" name="id_grupo" class="tb-input">
                            <?php foreach ($groupsForSemester as $g): ?>
                                <?php
                                $label = 'Semestre ' . $g['semestre']
                                       . ' · ' . $g['paquete']
                                       . ' · ' . $g['letra_grupo'];
                                ?>
                                <option value="<?= (int)$g['id_grupo'] ?>">
                                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="tb-form-actions">
                        <button type="submit" class="tb-btn">
                            Crear carga automática
                        </button>
                        <button type="button" class="tb-btn tb-btn-outline" disabled>
                            Carga manual (próximamente)
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <p style="font-size: 13px;">
                    No hay grupos disponibles en este periodo para el semestre actual del alumno.
                </p>
            <?php endif; ?>

        <?php endif; ?>

    <?php endif; ?>
</section>
