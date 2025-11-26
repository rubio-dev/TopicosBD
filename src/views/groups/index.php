<?php
declare(strict_types=1);

/**
 * Vista: periodos y grupos
 *
 * Variables esperadas:
 *  - $periods        : array de periodos
 *  - $selectedPeriod : array|null periodo seleccionado
 *  - $groups         : array de grupos del periodo
 */

$error = isset($_GET['error']) ? (string)$_GET['error'] : null;
?>
<section class="tb-section">
    <div class="tb-section-header">
        <div>
            <h2 class="tb-section-title">Periodos y grupos</h2>
            <p class="tb-section-subtitle">
                Selecciona un periodo lectivo para ver los grupos disponibles por semestre.
            </p>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="tb-alert tb-alert-error">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if (empty($periods)): ?>
        <p>No hay periodos registrados en el sistema.</p>
    <?php else: ?>
        <form method="get" class="tb-form tb-form-inline">
            <input type="hidden" name="m" value="grupos">
            <input type="hidden" name="a" value="index">

            <div class="tb-form-row">
                <label for="id_periodo" class="tb-label">Periodo lectivo</label>
                <select id="id_periodo" name="id_periodo" class="tb-input" onchange="this.form.submit();">
                    <?php foreach ($periods as $periodo): ?>
                        <?php
                        $idP   = (string)$periodo['id_periodo'];
                        $texto = $periodo['descripcion'] . ' (' . $idP . ')';
                        $sel   = ($selectedPeriod && $selectedPeriod['id_periodo'] === $idP) ? 'selected' : '';
                        ?>
                        <option value="<?= htmlspecialchars($idP, ENT_QUOTES, 'UTF-8') ?>" <?= $sel ?>>
                            <?= htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <?php if ($selectedPeriod): ?>
            <h3 class="tb-groups-title">
                Grupos del periodo
                «<?= htmlspecialchars($selectedPeriod['descripcion'], ENT_QUOTES, 'UTF-8') ?>»
            </h3>

            <?php if (empty($groups)): ?>
                <p>No hay grupos capturados para este periodo.</p>
            <?php else: ?>
                <?php
                // Agrupar grupos por semestre
                $bySemester = [];
                foreach ($groups as $g) {
                    $sem = (int)$g['semestre'];
                    if (!isset($bySemester[$sem])) {
                        $bySemester[$sem] = [];
                    }
                    $bySemester[$sem][] = $g;
                }
                ksort($bySemester);
                ?>

                <?php foreach ($bySemester as $semestre => $lista): ?>
                    <div class="tb-groups-block">
                        <h4 class="tb-groups-semester">
                            Semestre <?= htmlspecialchars((string)$semestre, ENT_QUOTES, 'UTF-8') ?>
                        </h4>
                        <div class="tb-table-wrapper">
                            <table class="tb-table">
                                <thead>
                                <tr>
                                    <th>ID grupo</th>
                                    <th>Paquete</th>
                                    <th>Letra</th>
                                    <th class="tb-col-actions">Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($lista as $grupo): ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string)$grupo['id_grupo'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($grupo['paquete'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($grupo['letra_grupo'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="tb-col-actions">
                                            <a href="index.php?m=grupos&a=schedule&id_grupo=<?= urlencode((string)$grupo['id_grupo']) ?>"
                                               class="tb-btn tb-btn-small">
                                                Ver horario
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</section>
s