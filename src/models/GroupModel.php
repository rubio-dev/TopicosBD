<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * GroupModel
 *
 * Acceso a grupos (tabla grupo), periodos y horarios de grupos.
 */
class GroupModel
{
    /**
     * Devuelve todos los grupos de un periodo.
     *
     * @param string $idPeriodo
     * @return array
     */
    public function getByPeriod(string $idPeriodo): array
    {
        $pdo = db();
        $sql = "SELECT id_grupo, id_periodo, semestre, paquete, letra_grupo
                FROM grupo
                WHERE id_periodo = :p
                ORDER BY semestre, paquete, letra_grupo";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':p' => $idPeriodo]);
        return $stmt->fetchAll();
    }

    /**
     * Devuelve los grupos de un periodo filtrados por semestre.
     *
     * @param string $idPeriodo
     * @param int    $semestre
     * @return array
     */
    public function getByPeriodAndSemester(string $idPeriodo, int $semestre): array
    {
        $pdo = db();
        $sql = "SELECT id_grupo, id_periodo, semestre, paquete, letra_grupo
                FROM grupo
                WHERE id_periodo = :p
                  AND semestre = :s
                ORDER BY paquete, letra_grupo";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':p' => $idPeriodo,
            ':s' => $semestre,
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene datos de un grupo junto con la información del periodo.
     *
     * @param int $idGrupo
     * @return array|null
     */
    public function getGroupWithPeriod(int $idGrupo): ?array
    {
        $pdo = db();
        $sql = "SELECT 
                    g.id_grupo,
                    g.id_periodo,
                    g.semestre,
                    g.paquete,
                    g.letra_grupo,
                    p.descripcion AS desc_periodo,
                    p.anio,
                    p.ciclo
                FROM grupo g
                JOIN periodo_lectivo p ON p.id_periodo = g.id_periodo
                WHERE g.id_grupo = :g";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':g' => $idGrupo]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Devuelve la lista de materias del grupo con sus bloques por día.
     *
     * @param int $idGrupo
     * @return array
     */
    public function getSchedule(int $idGrupo): array
    {
        $pdo = db();

        $sql = "
            SELECT
                gm.id_grupo_materia,
                gm.clave_materia,
                m.nombre AS materia,
                gm.id_bloque_lun,
                gm.id_bloque_mar,
                gm.id_bloque_mie,
                gm.id_bloque_jue,
                gm.id_bloque_vie
            FROM grupo_materia gm
            JOIN materia m ON m.clave_materia = gm.clave_materia
            WHERE gm.id_grupo = :g
            ORDER BY m.clave_materia
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':g' => $idGrupo]);
        return $stmt->fetchAll();
    }

    /**
     * Devuelve todos los bloques horarios disponibles.
     *
     * @return array
     */
    public function getAllBlocks(): array
    {
        $pdo = db();
        $sql = "SELECT id_bloque, hora_inicio, hora_fin, etiqueta
                FROM bloque_horario
                ORDER BY hora_inicio";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    }
}
