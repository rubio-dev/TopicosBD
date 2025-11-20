<?php
// Variables: $students, $offer, $controlNumber, $periodCode, $baseUrl,
//            $message, $messageType, $periods, $currentEnrollment
?>

<div class="dashboard-grid">

    <!-- PANEL LATERAL: filtros (GET) -->
    <aside class="panel">
        <div class="panel-header">
            <div>
                <div class="panel-title">Configuración</div>
                <div class="panel-subtitle">Selecciona alumno y período</div>
            </div>
        </div>

        <form method="get" action="" class="form-grid">
            <label>
                Alumno
                <select name="student">
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo htmlspecialchars($student->controlNumber); ?>"
                            <?php echo $student->controlNumber === $controlNumber ? 'selected' : ''; ?>>
                            <?php
                            echo htmlspecialchars(
                                $student->controlNumber . ' — ' . $student->name .
                                ' (Sem ' . $student->currentSemester . ')'
                            );
                            ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Período
                <select name="period">
                    <?php foreach ($periods as $period): ?>
                        <?php $code = $period['clave_periodo']; ?>
                        <option value="<?php echo htmlspecialchars($code); ?>"
                            <?php echo $code === $periodCode ? 'selected' : ''; ?>>
                            <?php
                            echo htmlspecialchars(
                                $code . ' — ' . ($period['descripcion'] ?? '')
                            );
                            ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <button type="submit">Actualizar oferta</button>
        </form>

        <div style="margin-top:14px; font-size:12px; color:var(--text-muted);">
            <p>La oferta respeta:</p>
            <ul style="margin:0 0 0 16px; padding:0; list-style:disc;">
                <li>Materias aprobadas (no se vuelven a cargar).</li>
                <li>Materias en serie (requisitos previos).</li>
                <li>Prioridad definida por alumno (1, 2, 3).</li>
            </ul>
        </div>
    </aside>

    <!-- PANEL PRINCIPAL: oferta + inscripción + horario actual -->
    <section>
        <div class="panel offer-panel">
            <div class="offer-header">
                <div>
                    <div class="offer-title">
                        Oferta de horarios
                    </div>
                    <div class="offer-meta">
                        Alumno: <strong><?php echo htmlspecialchars($controlNumber); ?></strong> ·
                        Período: <strong><?php echo htmlspecialchars($periodCode); ?></strong>
                    </div>
                </div>
            </div>

            <?php if ($message !== null): ?>
                <div class="alert <?php echo $messageType === 'success' ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($offer)): ?>
                <div class="empty-state">
                    No se encontraron materias disponibles para este alumno en el período seleccionado.
                    <br>Verifica que existan grupos abiertos en la base de datos o revisa las seriaciones.
                </div>
            <?php else: ?>

                <!-- Formulario para inscribir selección -->
                <form method="post" action="">
                    <input type="hidden" name="student" value="<?php echo htmlspecialchars($controlNumber); ?>">
                    <input type="hidden" name="period" value="<?php echo htmlspecialchars($periodCode); ?>">

                    <div class="subject-list">
                        <?php foreach ($offer as $subjectCode => $item): ?>
                            <?php
                            /** @var Subject $subject */
                            $subject = $item['subject'];

                            $priorityClass = 'priority-medium';
                            $priorityLabel = 'Normal (2)';

                            if ($subject->priority === 1) {
                                $priorityClass = 'priority-high';
                                $priorityLabel = 'Alta (1)';
                            } elseif ($subject->priority === 3) {
                                $priorityClass = 'priority-low';
                                $priorityLabel = 'Baja / Adelanto (3)';
                            }
                            ?>
                            <article class="subject-card">
                                <div class="subject-header">
                                    <div class="subject-main">
                                        <span class="subject-code">
                                            Materia <?php echo htmlspecialchars($subject->code); ?>
                                        </span>
                                        <span class="subject-name">
                                            <?php echo htmlspecialchars($subject->name); ?>
                                        </span>
                                        <span class="subject-meta">
                                            Semestre sugerido:
                                            <strong><?php echo $subject->semester; ?></strong>
                                        </span>
                                    </div>
                                    <div class="priority-pill <?php echo $priorityClass; ?>">
                                        Prioridad: <?php echo $priorityLabel; ?>
                                    </div>
                                </div>

                                <table class="subject-groups-table">
                                    <thead>
                                    <tr>
                                        <th></th>
                                        <th>ID grupo</th>
                                        <th>Paquete</th>
                                        <th>Grupo</th>
                                        <th>Lun</th>
                                        <th>Mar</th>
                                        <th>Mié</th>
                                        <th>Jue</th>
                                        <th>Vie</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($item['groups'] as $group): ?>
                                        <tr>
                                            <td>
                                                <input
                                                    type="checkbox"
                                                    name="groups[]"
                                                    value="<?php echo $group->id; ?>"
                                                >
                                            </td>
                                            <td><?php echo $group->id; ?></td>
                                            <td><?php echo htmlspecialchars($group->package); ?></td>
                                            <td><?php echo htmlspecialchars($group->groupLetter); ?></td>
                                            <td><?php echo htmlspecialchars($group->hourMon); ?></td>
                                            <td><?php echo htmlspecialchars($group->hourTue); ?></td>
                                            <td><?php echo htmlspecialchars($group->hourWed); ?></td>
                                            <td><?php echo htmlspecialchars($group->hourThu); ?></td>
                                            <td><?php echo htmlspecialchars($group->hourFri); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div style="margin-top:14px; text-align:right;">
                        <button type="submit">
                            Inscribir selección
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <!-- HORARIO ACTUAL EN FORMATO TABLA (LUN-VIE x HORA) -->
        <div class="panel" style="margin-top:16px;">
            <div class="offer-header">
                <div>
                    <div class="offer-title">
                        Horario actual del alumno
                    </div>
                    <div class="offer-meta">
                        Grupos ya inscritos en <?php echo htmlspecialchars($periodCode); ?>
                    </div>
                </div>
            </div>

            <?php if (empty($currentEnrollment)): ?>
                <div class="empty-state">
                    Aún no hay grupos inscritos para este alumno en el período seleccionado.
                    <br>Selecciona algunos grupos en la oferta superior y pulsa “Inscribir selección”.
                </div>
            <?php else: ?>
                <?php
                // Construir matriz horario: horas x días
                $daysMap = [
                    'Lun' => 'hor_lun',
                    'Mar' => 'hor_mar',
                    'Mié' => 'hor_mie',
                    'Jue' => 'hor_jue',
                    'Vie' => 'hor_vie',
                ];

                $hours = [];

                foreach ($currentEnrollment as $row) {
                    foreach ($daysMap as $field) {
                        $hour = $row[$field];
                        if ($hour !== null && $hour !== '') {
                            $hours[] = $hour;
                        }
                    }
                }

                $hours = array_values(array_unique($hours));
                sort($hours);

                // Índice rápido: [día][hora] => textos
                $grid = [];
                foreach ($currentEnrollment as $row) {
                    foreach ($daysMap as $dayShort => $field) {
                        $hour = $row[$field];
                        if ($hour === null || $hour === '') {
                            continue;
                        }

                        $text = $row['clave_materia'] .
                            ' (' . $row['grupo'] . ')' .
                            '<br>' .
                            htmlspecialchars($row['nombre_materia'], ENT_QUOTES, 'UTF-8');

                        $grid[$dayShort][$hour][] = $text;
                    }
                }
                ?>

                <div class="subject-list">
                    <article class="subject-card">
                        <table class="subject-groups-table timetable">
                            <thead>
                            <tr>
                                <th>Hora</th>
                                <?php foreach ($daysMap as $dayShort => $field): ?>
                                    <th><?php echo $dayShort; ?></th>
                                <?php endforeach; ?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($hours as $hour): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($hour); ?></strong></td>
                                    <?php foreach ($daysMap as $dayShort => $field): ?>
                                        <td>
                                            <?php
                                            if (isset($grid[$dayShort][$hour])) {
                                                echo implode('<br><hr style="border:none;border-top:1px dashed #e5e7eb;margin:4px 0;">', $grid[$dayShort][$hour]);
                                            } else {
                                                echo '&mdash;';
                                            }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </article>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
