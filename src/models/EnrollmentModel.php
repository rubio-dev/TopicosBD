<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * EnrollmentModel
 *
 * Lógica de acceso a datos para cargas académicas.
 */
class EnrollmentModel
{
    /**
     * Busca la carga activa de un alumno en un periodo.
     *
     * @param string $idAlumno
     * @param string $idPeriodo
     * @return array|null
     */
    public function findActiveByStudentAndPeriod(string $idAlumno, string $idPeriodo): ?array
    {
        $pdo = db();

        $sql = "SELECT 
                    ca.id_carga,
                    ca.id_alumno,
                    ca.id_periodo,
                    ca.fecha_alta,
                    ca.estatus,
                    a.nombre       AS alumno_nombre,
                    a.semestre_actual,
                    p.descripcion  AS periodo_desc
                FROM carga_academica ca
                JOIN alumno a          ON a.id_alumno   = ca.id_alumno
                JOIN periodo_lectivo p ON p.id_periodo  = ca.id_periodo
                WHERE ca.id_alumno = :al
                  AND ca.id_periodo = :pe
                  AND ca.estatus = 'A'
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':al' => $idAlumno,
            ':pe' => $idPeriodo,
        ]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Devuelve el detalle de una carga (materias y grupo).
     *
     * @param int $idCarga
     * @return array
     */
    public function getDetails(int $idCarga): array
    {
        $pdo = db();

        $sql = "SELECT
                    cd.id_carga_det,
                    gm.id_grupo_materia,
                    m.clave_materia,
                    m.nombre AS materia,
                    g.semestre,
                    g.paquete,
                    g.letra_grupo
                FROM carga_detalle cd
                JOIN grupo_materia gm ON gm.id_grupo_materia = cd.id_grupo_materia
                JOIN materia m        ON m.clave_materia     = gm.clave_materia
                JOIN grupo g          ON g.id_grupo          = gm.id_grupo
                WHERE cd.id_carga = :c
                ORDER BY m.clave_materia";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':c' => $idCarga]);

        return $stmt->fetchAll();
    }

    /**
     * Llama al procedimiento almacenado de carga automática.
     *
     * @param string $idAlumno
     * @param string $idPeriodo
     * @param int    $idGrupo
     * @throws \PDOException si el SP lanza un SIGNAL o hay error de BD
     */
    public function createAutomaticLoad(string $idAlumno, string $idPeriodo, int $idGrupo): void
    {
        $pdo = db();

        $stmt = $pdo->prepare(
            "CALL sp_crear_carga_automatica(:alumno, :periodo, :grupo)"
        );

        $stmt->execute([
            ':alumno'  => $idAlumno,
            ':periodo' => $idPeriodo,
            ':grupo'   => $idGrupo,
        ]);

        // Limpiar posibles resultsets extra del procedimiento
        try {
            do {
                $stmt->fetchAll();
            } while ($stmt->nextRowset());
        } catch (\Throwable $e) {
            // Ignoramos errores al limpiar, lo importante es que el SP se haya ejecutado
        }
    }
}
