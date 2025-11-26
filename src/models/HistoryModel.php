<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * HistoryModel
 *
 * Maneja la consulta del historial de materias.
 */
class HistoryModel
{
    /**
     * Historial completo (todas las materias del alumno, agrupables por periodo).
     *
     * @param string $idAlumno
     * @return array
     */
    public function getHistory(string $idAlumno): array
    {
        $pdo = db();

        $sql = "
            SELECT
                am.id_alumno_materia,
                am.clave_materia,
                am.id_periodo,
                am.calificacion,
                am.tipo_acred,
                am.estatus,
                m.nombre         AS materia,
                m.semestre       AS semestre_materia,
                p.descripcion    AS periodo_desc,
                p.anio,
                p.ciclo
            FROM alumno_materia am
            JOIN materia m
              ON m.clave_materia = am.clave_materia
            JOIN periodo_lectivo p
              ON p.id_periodo = am.id_periodo
            WHERE am.id_alumno = :al
            ORDER BY p.anio, p.ciclo, m.semestre, am.clave_materia
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':al' => $idAlumno]);
        return $stmt->fetchAll();
    }

}
