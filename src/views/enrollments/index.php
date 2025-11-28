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
 *  - $retakeOptions     : array (reprobadas recursables)
 *  - $newOptions        : array (materias nuevas disponibles)
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
        <hr class="tb-separator">

        <!-- Resumen de alumno / periodo -->
        <div class="tb-group-info">
            <p>
                <strong>Alumno:</strong>
                <?= htmlspecialchars($selectedStudent['nombre'], ENT_QUOTES, 'UTF-8') ?>
                &nbsp;–&nbsp;
                <?= htmlspecialchars($selectedStudent['id_alumno'], ENT_QUOTES, 'UTF-8') ?>
                <?php if ($isIrregular): ?>
                    <span class="tb-badge-irregular">IRREGULAR</span>
                <?php endif; ?>
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
            <?php if ($isIrregular): ?>
                <div class="tb-alert tb-alert-error tb-mt-2">
                    <strong>ESTATUS IRREGULAR:</strong> Este alumno tiene materias reprobadas. 
                    La carga automática está deshabilitada. Debe seleccionar las materias manualmente.
                </div>
            <?php endif; ?>
        </div>

        <?php if ($activeLoad !== null): ?>
            <!-- Carga activa -->
            <h3 class="tb-groups-title">Carga activa en este periodo</h3>

            <p class="tb-text-small tb-mt-2">
                <strong>ID carga:</strong>
                <?= htmlspecialchars((string)$activeLoad['id_carga'], ENT_QUOTES, 'UTF-8') ?>
                · <strong>Fecha alta:</strong>
                <?= htmlspecialchars((string)$activeLoad['fecha_alta'], ENT_QUOTES, 'UTF-8') ?>
                · <strong>Estatus:</strong>
                <?= htmlspecialchars($activeLoad['estatus'], ENT_QUOTES, 'UTF-8') ?>
            </p>

            <form method="post"
                  action="index.php?m=cargas&a=cancel"
                  class="tb-form tb-form-inline tb-mt-2 tb-mb-3">
                <input type="hidden" name="id_carga"
                       value="<?= htmlspecialchars((string)$activeLoad['id_carga'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="id_alumno"
                       value="<?= htmlspecialchars($selectedStudent['id_alumno'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="id_periodo"
                       value="<?= htmlspecialchars($selectedPeriod['id_periodo'], ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="tb-btn tb-btn-outline">
                    Cancelar carga
                </button>
            </form>

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
                                    · Paquete <?= htmlspecialchars($fila['paquete'], ENT_QUOTES, 'UTF-8') ?>
                                    · Grupo <?= htmlspecialchars($fila['letra_grupo'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>La carga activa no tiene materias en el detalle.</p>
            <?php endif; ?>

            <p class="tb-text-xsmall tb-text-muted tb-mt-2">
                Para modificar la carga, debes cancelarla y crear una nueva.
            </p>

        <?php else: ?>
            <!-- No hay carga activa -->
            <h3 class="tb-groups-title">No hay carga activa en este periodo</h3>
            
            <?php if (!$isIrregular): ?>
                <!-- SECCIÓN DE CARGA AUTOMÁTICA (Solo para regulares) -->
                <div class="tb-card tb-mb-3">
                    <?php if (isset($suggestedGroup1) && $suggestedGroup1): ?>
                        <!-- CASO 1ER SEMESTRE: ASIGNACIÓN AUTOMÁTICA -->
                        <h4 class="tb-card-title">Asignación Automática (Primer Ingreso)</h4>
                        <p class="tb-text-small tb-mb-2">
                            El sistema ha seleccionado el grupo más adecuado para equilibrar las cargas.
                        </p>
                        
                        <div class="tb-alert tb-alert-info">
                            <strong>Grupo Asignado:</strong> 
                            Paquete <?= htmlspecialchars($suggestedGroup1['paquete'], ENT_QUOTES, 'UTF-8') ?> 
                            (Grupo <?= htmlspecialchars($suggestedGroup1['letra_grupo'], ENT_QUOTES, 'UTF-8') ?>)
                        </div>

                        <form method="post" action="index.php?m=cargas&a=createAuto" class="tb-form">
                            <input type="hidden" name="id_alumno" value="<?= htmlspecialchars($idAlumno, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="id_periodo" value="<?= htmlspecialchars($idPeriodo, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="id_grupo" value="<?= (int)$suggestedGroup1['id_grupo'] ?>">

                            <div class="tb-form-actions tb-mt-2">
                                <button type="submit" class="tb-btn">
                                    Confirmar Asignación Automática
                                </button>
                            </div>
                        </form>

                    <?php else: ?>
                        <!-- CASO REGULAR (NO 1ER SEMESTRE): SELECCIÓN DE PAQUETE -->
                        <h4 class="tb-card-title">Opción 1: Carga Automática (Paquetes)</h4>
                        <p class="tb-text-small tb-mb-2">
                            Selecciona uno de los paquetes de horarios predefinidos para inscribir todas las materias del semestre.
                        </p>

                        <?php if (!empty($groupsForSemester)): ?>
                            <form method="post" action="index.php?m=cargas&a=createAuto" class="tb-form">
                                <input type="hidden" name="id_alumno" value="<?= htmlspecialchars($idAlumno, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="id_periodo" value="<?= htmlspecialchars($idPeriodo, ENT_QUOTES, 'UTF-8') ?>">

                                <div class="tb-package-grid">
                                    <?php foreach ($groupsForSemester as $g): ?>
                                        <label class="tb-package-option">
                                            <input type="radio" name="id_grupo" value="<?= (int)$g['id_grupo'] ?>" required>
                                            <span class="tb-package-title">
                                                Paquete <?= htmlspecialchars($g['paquete'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                            <span class="tb-package-subtitle">
                                                Grupo <?= htmlspecialchars($g['letra_grupo'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                                <div class="tb-form-actions tb-mt-2">
                                    <button type="submit" class="tb-btn">
                                        Generar Carga Automática
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <p class="tb-text-small tb-text-error">
                                No hay paquetes disponibles en este periodo para el semestre actual.
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($activeLoad === null): ?>
            <!-- =============================
                 Sección de CARGA MANUAL
                 ============================= -->
            <hr class="tb-separator-dashed">

            <?php 
                // Si es irregular o ya hay carga activa, mostramos la sección manual abierta o disponible
                // Si es irregular, la mostramos abierta por defecto.
                $manualDisplay = ($isIrregular) ? 'block' : 'none';
                $manualTitle   = ($isIrregular) ? 'Opción Única: Carga Manual (Irregular)' : 'Opción 2: Carga Manual (Agregar materias)';
            ?>

            <div class="tb-flex-between">
                <h3 class="tb-groups-title" style="margin-bottom: 0;"><?= $manualTitle ?></h3>
                <button type="button" id="btnToggleManual" class="tb-btn tb-btn-outline tb-text-small" style="padding: 4px 12px;">
                    Mostrar/Ocultar
                </button>
            </div>

            <div id="manualLoadSection" style="display: <?= $manualDisplay ?>;">
                <p class="tb-text-small tb-mb-2">
                    Selecciona materias reprobadas para recursar y/o materias nuevas
                    para las que el alumno ya cumple prerequisitos.
                </p>

                <?php if (empty($retakeOptions) && empty($newOptions)): ?>
                    <p class="tb-text-small">
                        No hay materias disponibles para carga manual en este periodo.
                    </p>
                <?php else: ?>
                    <form method="post"
                          action="index.php?m=cargas&a=addManual"
                          class="tb-form">
                        <input type="hidden" name="id_alumno"
                               value="<?= htmlspecialchars($selectedStudent['id_alumno'], ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="id_periodo"
                               value="<?= htmlspecialchars($selectedPeriod['id_periodo'], ENT_QUOTES, 'UTF-8') ?>">

                        <?php if (!empty($retakeOptions)): ?>
                            <div class="tb-table-wrapper tb-mb-3">
                                <h4 class="tb-text-small tb-mb-1" style="color: #d32f2f;">Materias reprobadas (Obligatorias)</h4>
                                <table class="tb-table tb-table-compact">
                                    <thead>
                                    <tr>
                                        <th>Seleccionar</th>
                                        <th>Clave</th>
                                        <th>Materia</th>
                                        <th>Paquete/Grupo</th>
                                        <th>Cupo</th>
                                        <th>Horario</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php 
                                    $seenRetake = [];
                                    foreach ($retakeOptions as $op): 
                                        // Check only the first option for each subject
                                        $isChecked = !in_array($op['clave_materia'], $seenRetake);
                                        $seenRetake[] = $op['clave_materia'];
                                    ?>
                                        <tr class="subject-row-<?= htmlspecialchars($op['clave_materia'], ENT_QUOTES, 'UTF-8') ?>">
                                            <td>
                                                <!-- Radio button agrupado por clave_materia para selección única -->
                                                <input type="radio"
                                                       class="tb-radio-retake"
                                                       name="gm[<?= htmlspecialchars($op['clave_materia'], ENT_QUOTES, 'UTF-8') ?>]"
                                                       value="<?= (int)$op['id_grupo_materia'] ?>"
                                                       <?= $isChecked ? 'checked' : '' ?>>
                                            </td>
                                            <td><?= htmlspecialchars($op['clave_materia'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($op['materia'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                Sem <?= htmlspecialchars((string)$op['semestre'], ENT_QUOTES, 'UTF-8') ?>
                                                · <?= htmlspecialchars($op['paquete'], ENT_QUOTES, 'UTF-8') ?>
                                                · <?= htmlspecialchars($op['letra_grupo'], ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                            <td>
                                                <?= (int)$op['inscritos'] ?> / <?= (int)$op['cupo'] ?>
                                            </td>
                                            <td class="tb-text-xsmall">
                                                <?= htmlspecialchars($op['schedule_text'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($newOptions)): ?>
                            <div class="tb-table-wrapper tb-mb-3">
                                <h4 class="tb-text-small tb-mb-1">Materias nuevas disponibles</h4>
                                <table class="tb-table tb-table-compact">
                                    <thead>
                                    <tr>
                                        <th>Seleccionar</th>
                                        <th>Clave</th>
                                        <th>Materia</th>
                                        <th>Paquete/Grupo</th>
                                        <th>Cupo</th>
                                        <th>Horario</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($newOptions as $op): ?>
                                        <tr>
                                            <td>
                                                <!-- Checkbox para materias nuevas (opcionales) -->
                                                <input type="checkbox"
                                                       name="gm[]"
                                                       value="<?= (int)$op['id_grupo_materia'] ?>">
                                            </td>
                                            <td><?= htmlspecialchars($op['clave_materia'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($op['materia'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                Sem <?= htmlspecialchars((string)$op['semestre'], ENT_QUOTES, 'UTF-8') ?>
                                                · <?= htmlspecialchars($op['paquete'], ENT_QUOTES, 'UTF-8') ?>
                                                · <?= htmlspecialchars($op['letra_grupo'], ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                            <td>
                                                <?= (int)$op['inscritos'] ?> / <?= (int)$op['cupo'] ?>
                                            </td>
                                            <td class="tb-text-xsmall">
                                                <?= htmlspecialchars($op['schedule_text'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                        <div class="tb-form-actions">
                            <button type="submit" class="tb-btn">
                                Agregar materias seleccionadas
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Toggle Manual Load Section
                    const btn = document.getElementById('btnToggleManual');
                    const section = document.getElementById('manualLoadSection');
                    if (btn && section) {
                        btn.addEventListener('click', function() {
                            if (section.style.display === 'none') {
                                section.style.display = 'block';
                            } else {
                                section.style.display = 'none';
                            }
                        });
                    }

                    // Logic to hide unselected Retake options
                    const retakeRadios = document.querySelectorAll('.tb-radio-retake');
                    
                    function updateRetakeVisibility(clave) {
                        const rows = document.querySelectorAll('.subject-row-' + clave);
                        let selectedRow = null;
                        
                        rows.forEach(row => {
                            const radio = row.querySelector('input[type="radio"]');
                            if (radio && radio.checked) {
                                selectedRow = row;
                            }
                        });

                        if (selectedRow) {
                            rows.forEach(row => {
                                if (row !== selectedRow) {
                                    row.style.display = 'none';
                                } else {
                                    row.style.display = ''; // Show selected
                                    
                                    // Add "Cambiar" link if not present
                                    let cell = row.cells[2]; // Materia column
                                    if (!cell.querySelector('.btn-change-group')) {
                                        const link = document.createElement('a');
                                        link.href = '#';
                                        link.className = 'btn-change-group tb-text-xsmall tb-ml-2';
                                        link.textContent = '(Cambiar)';
                                        link.style.color = 'var(--tb-teal)';
                                        link.style.fontWeight = '600';
                                        link.onclick = function(e) {
                                            e.preventDefault();
                                            // Show all rows for this subject
                                            rows.forEach(r => r.style.display = '');
                                            // Hide this link
                                            link.remove();
                                        };
                                        cell.appendChild(link);
                                    }
                                }
                            });
                        }
                    }

                    // Init listeners
                    const claves = new Set();
                    retakeRadios.forEach(r => {
                        const match = r.name.match(/gm\[(.*?)\]/);
                        if (match) {
                            const clave = match[1];
                            claves.add(clave);
                            r.addEventListener('change', () => updateRetakeVisibility(clave));
                        }
                    });

                    // Initial run
                    claves.forEach(c => updateRetakeVisibility(c));
                });
            </script>
        <?php endif; ?>

    <?php endif; ?>
</section>
