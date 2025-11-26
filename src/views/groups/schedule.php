<?php
declare(strict_types=1);

/**
 * Vista: horario de un grupo
 *
 * Variables esperadas:
 *  - $groupInfo : array con datos del grupo + periodo
 *  - $schedule  : array de materias con bloques lun-vie (getSchedule)
 *  - $blocks    : array de bloques horarios (getAllBlocks)
 */

if (!function_exists('tb_render_cell_list')) {
    /**
     * Convierte un arreglo de textos en un string HTML con saltos de línea.
     *
     * @param array $list
     * @return string
     */
    function tb_render_cell_list(array $list): string
    {
        if (empty($list)) {
            return '';
        }

        $items = [];
        foreach ($list as $item) {
            $items[] = htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
        }
        return implode('<br>', $items);
    }
}
?>
<section class="tb-section">
    <div class="tb-section-header">
        <div>
            <h2 class="tb-section-title">Horario de grupo</h2>
            <p class="tb-section-subtitle">
                Consulta del horario para el grupo seleccionado.
            </p>
        </div>
        <div>
            <a href="index.php?m=grupos&a=index" class="tb-btn tb-btn-outline">Volver a grupos</a>
        </div>
    </div>

    <div class="tb-group-info">
        <p>
            <strong>Periodo:</strong>
            <?= htmlspecialchars($groupInfo['desc_periodo'], ENT_QUOTES, 'UTF-8') ?>
            (<?= htmlspecialchars($groupInfo['id_periodo'], ENT_QUOTES, 'UTF-8') ?>)
        </p>
        <p>
            <strong>Grupo:</strong>
            Semestre <?= htmlspecialchars((string)$groupInfo['semestre'], ENT_QUOTES, 'UTF-8') ?>
            · Paquete <?= htmlspecialchars($groupInfo['paquete'], ENT_QUOTES, 'UTF-8') ?>
            · Letra <?= htmlspecialchars($groupInfo['letra_grupo'], ENT_QUOTES, 'UTF-8') ?>
        </p>
    </div>

    <?php if (empty($schedule)): ?>
        <p>Este grupo aún no tiene materias ni horarios asignados.</p>
    <?php else: ?>

        <?php
        // Mapa de bloques por id para acceso rápido
        $blockMap = [];
        foreach ($blocks as $b) {
            $blockMap[(int)$b['id_bloque']] = $b;
        }
        ?>

        <!-- Tabla simple de materias y su bloque de referencia -->
        <h3 class="tb-groups-title">Materias del grupo</h3>

        <div class="tb-table-wrapper">
            <table class="tb-table">
                <thead>
                <tr>
                    <th>Clave</th>
                    <th>Materia</th>
                    <th>Bloque (ref.)</th>
                    <th>Horario</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($schedule as $fila): ?>
                    <?php
                    // Usamos el bloque de lunes como referencia visual del horario
                    $idBloqueRef = isset($fila['id_bloque_lun']) ? (int)$fila['id_bloque_lun'] : 0;
                    $bloque      = $blockMap[$idBloqueRef] ?? null;
                    $etiqueta    = $bloque['etiqueta'] ?? '';
                    $hora        = '';
                    if ($bloque) {
                        $hora = sprintf(
                            '%s - %s',
                            substr((string)$bloque['hora_inicio'], 0, 5),
                            substr((string)$bloque['hora_fin'], 0, 5)
                        );
                    }
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($fila['clave_materia'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($fila['materia'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($hora, ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Vista tipo horario (rejilla por bloques y días) -->
        <h3 class="tb-groups-title">Vista tipo horario</h3>

        <?php
        // Construimos la rejilla: una fila por bloque y columnas por día
        $grid = [];

        foreach ($blocks as $b) {
            $idB = (int)$b['id_bloque'];
            $grid[$idB] = [
                'info' => $b,
                'LUN'  => [],
                'MAR'  => [],
                'MIE'  => [],
                'JUE'  => [],
                'VIE'  => [],
            ];
        }

        foreach ($schedule as $fila) {
            $materiaTexto = $fila['clave_materia'] . ' - ' . $fila['materia'];

            if (!empty($fila['id_bloque_lun'])) {
                $idB = (int)$fila['id_bloque_lun'];
                if (isset($grid[$idB])) {
                    $grid[$idB]['LUN'][] = $materiaTexto;
                }
            }
            if (!empty($fila['id_bloque_mar'])) {
                $idB = (int)$fila['id_bloque_mar'];
                if (isset($grid[$idB])) {
                    $grid[$idB]['MAR'][] = $materiaTexto;
                }
            }
            if (!empty($fila['id_bloque_mie'])) {
                $idB = (int)$fila['id_bloque_mie'];
                if (isset($grid[$idB])) {
                    $grid[$idB]['MIE'][] = $materiaTexto;
                }
            }
            if (!empty($fila['id_bloque_jue'])) {
                $idB = (int)$fila['id_bloque_jue'];
                if (isset($grid[$idB])) {
                    $grid[$idB]['JUE'][] = $materiaTexto;
                }
            }
            if (!empty($fila['id_bloque_vie'])) {
                $idB = (int)$fila['id_bloque_vie'];
                if (isset($grid[$idB])) {
                    $grid[$idB]['VIE'][] = $materiaTexto;
                }
            }
        }
        ?>

        <div class="tb-table-wrapper">
            <table class="tb-table tb-table-schedule">
                <thead>
                <tr>
                    <th>Bloque</th>
                    <th>Hora</th>
                    <th data-day="LUN">Lunes</th>
                    <th data-day="MAR">Martes</th>
                    <th data-day="MIE">Miércoles</th>
                    <th data-day="JUE">Jueves</th>
                    <th data-day="VIE">Viernes</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($grid as $filaBloque): ?>
                    <?php
                    /** @var array $info */
                    $info = $filaBloque['info'];
                    $horaStr = sprintf(
                        '%s - %s',
                        substr((string)$info['hora_inicio'], 0, 5),
                        substr((string)$info['hora_fin'], 0, 5)
                    );
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($info['etiqueta'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($horaStr, ENT_QUOTES, 'UTF-8') ?></td>
                        <td data-day="LUN"><?= tb_render_cell_list($filaBloque['LUN']) ?></td>
                        <td data-day="MAR"><?= tb_render_cell_list($filaBloque['MAR']) ?></td>
                        <td data-day="MIE"><?= tb_render_cell_list($filaBloque['MIE']) ?></td>
                        <td data-day="JUE"><?= tb_render_cell_list($filaBloque['JUE']) ?></td>
                        <td data-day="VIE"><?= tb_render_cell_list($filaBloque['VIE']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>
</section>
