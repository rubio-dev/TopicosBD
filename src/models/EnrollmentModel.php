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

    /**
     * Crea una carga vacía (manual) para un alumno y periodo.
     *
     * @param string $idAlumno
     * @param string $idPeriodo
     * @return int id_carga creada
     */
    public function createEmptyLoad(string $idAlumno, string $idPeriodo): int
    {
        $pdo = db();

        // 1. Verificar si ya existe una carga (activa o cancelada)
        $stmt = $pdo->prepare("SELECT id_carga FROM carga_academica WHERE id_alumno = :al AND id_periodo = :pe LIMIT 1");
        $stmt->execute([':al' => $idAlumno, ':pe' => $idPeriodo]);
        $existingId = $stmt->fetchColumn();

        if ($existingId) {
            // Si existe, la reactivamos (estatus = 'A')
            $pdo->prepare("UPDATE carga_academica SET estatus = 'A' WHERE id_carga = ?")
                ->execute([$existingId]);
            return (int)$existingId;
        }

        // 2. Si no existe, crear nueva
        $sql = "INSERT INTO carga_academica (id_alumno, id_periodo, estatus)
                VALUES (:al, :pe, 'A')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':al' => $idAlumno,
            ':pe' => $idPeriodo,
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Opciones de materias REPROBADAS que el alumno puede recursar
     * en el periodo indicado (existe grupo para esa materia en el periodo).
     *
     * @param string $idAlumno
     * @param string $idPeriodo
     * @return array
     */
    public function getRetakeOptions(string $idAlumno, string $idPeriodo): array
    {
        $pdo = db();

        $sql = "
            SELECT DISTINCT
                gm.id_grupo_materia,
                m.clave_materia,
                m.nombre AS materia,
                g.semestre,
                g.paquete,
                g.letra_grupo,
                g.cupo,
                (
                    SELECT COUNT(DISTINCT ca.id_alumno)
                    FROM carga_academica ca
                    JOIN carga_detalle cd ON cd.id_carga = ca.id_carga
                    JOIN grupo_materia gm2 ON gm2.id_grupo_materia = cd.id_grupo_materia
                    WHERE gm2.id_grupo = g.id_grupo
                      AND ca.estatus = 'A'
                ) as inscritos,
                gm.id_bloque_lun, gm.id_bloque_mar, gm.id_bloque_mie, gm.id_bloque_jue, gm.id_bloque_vie
            FROM alumno_materia am
            JOIN materia m
              ON m.clave_materia = am.clave_materia
            JOIN grupo_materia gm
              ON gm.clave_materia = m.clave_materia
            JOIN grupo g
              ON g.id_grupo = gm.id_grupo
            WHERE am.id_alumno = :al
              AND am.estatus = 'R'
              AND g.id_periodo = :pe
              AND NOT EXISTS (
                  SELECT 1
                  FROM carga_academica ca
                  JOIN carga_detalle cd
                    ON cd.id_carga = ca.id_carga
                  WHERE ca.id_alumno = :al2
                    AND ca.id_periodo = :pe2
                    AND cd.id_grupo_materia = gm.id_grupo_materia
              )
            ORDER BY m.clave_materia
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':al'  => $idAlumno,
            ':pe'  => $idPeriodo,
            ':al2' => $idAlumno,
            ':pe2' => $idPeriodo,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Opciones de materias NUEVAS para las que el alumno ya cumple prerequisitos,
     * que tienen grupo en el periodo y aún no las ha cursado/aprobado/reprobado.
     *
     * @param string $idAlumno
     * @param string $idPeriodo
     * @param int    $semestreAlumno
     * @return array
     */
    public function getNewSubjectOptions(string $idAlumno, string $idPeriodo, int $semestreAlumno): array
    {
        $pdo = db();

        $sql = "
            SELECT DISTINCT
                gm.id_grupo_materia,
                m.clave_materia,
                m.nombre AS materia,
                g.semestre,
                g.paquete,
                g.letra_grupo,
                g.cupo,
                (
                    SELECT COUNT(DISTINCT ca.id_alumno)
                    FROM carga_academica ca
                    JOIN carga_detalle cd ON cd.id_carga = ca.id_carga
                    JOIN grupo_materia gm2 ON gm2.id_grupo_materia = cd.id_grupo_materia
                    WHERE gm2.id_grupo = g.id_grupo
                      AND ca.estatus = 'A'
                ) as inscritos,
                gm.id_bloque_lun, gm.id_bloque_mar, gm.id_bloque_mie, gm.id_bloque_jue, gm.id_bloque_vie
            FROM materia m
            JOIN grupo_materia gm
              ON gm.clave_materia = m.clave_materia
            JOIN grupo g
              ON g.id_grupo = gm.id_grupo
            WHERE g.id_periodo = :pe
              AND m.semestre <= :sem
              -- No tener historial previo de esa materia
              AND NOT EXISTS (
                  SELECT 1
                  FROM alumno_materia am
                  WHERE am.id_alumno = :al2
                    AND am.clave_materia = m.clave_materia
                    AND am.estatus IN ('A','C','R')
              )
              -- Cumplir prerequisitos obligatorios
              AND NOT EXISTS (
                  SELECT 1
                  FROM materia_prerequisito mp
                  WHERE mp.clave_materia = m.clave_materia
                    AND mp.es_obligatorio = 1
                    AND NOT EXISTS (
                        SELECT 1
                        FROM alumno_materia am2
                        WHERE am2.id_alumno = :al3
                          AND am2.clave_materia = mp.clave_materia_prereq
                          AND am2.estatus = 'A'
                    )
              )
              -- No estar ya en la carga de este periodo
              AND NOT EXISTS (
                  SELECT 1
                  FROM carga_academica ca2
                  JOIN carga_detalle cd2 ON cd2.id_carga = ca2.id_carga
                  WHERE ca2.id_alumno = :al4
                    AND ca2.id_periodo = :pe2
                    AND cd2.id_grupo_materia = gm.id_grupo_materia
              )
            ORDER BY m.semestre, m.clave_materia
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':pe'  => $idPeriodo,
            ':sem' => $semestreAlumno,
            ':al2' => $idAlumno,
            ':al3' => $idAlumno,
            ':al4' => $idAlumno,
            ':pe2' => $idPeriodo,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Agrega una lista de materias (por id_grupo_materia) a la carga.
     * - Valida colisiones de horario.
     * - Inserta en carga_detalle (INSERT IGNORE para evitar duplicados).
     * - Inserta en alumno_materia con estatus 'C' para el periodo si no existe registro.
     *
     * @param int      $idCarga
     * @param string   $idAlumno
     * @param string   $idPeriodo
     * @param int[]    $idGrupoMaterias
     * @throws \RuntimeException Si hay colisión de horarios o error de BD.
     */
    public function addSubjectsToLoad(int $idCarga, string $idAlumno, string $idPeriodo, array $idGrupoMaterias): void
    {
        if (empty($idGrupoMaterias)) {
            return;
        }

        $pdo = db();

        try {
            $pdo->beginTransaction();

            // 0) Validar colisiones de horario
            // Obtener horarios de materias ya cargadas
            $sqlExisting = "
                SELECT gm.id_grupo_materia, m.nombre,
                       gm.id_bloque_lun, gm.id_bloque_mar, gm.id_bloque_mie, gm.id_bloque_jue, gm.id_bloque_vie
                FROM carga_detalle cd
                JOIN grupo_materia gm ON gm.id_grupo_materia = cd.id_grupo_materia
                JOIN materia m ON m.clave_materia = gm.clave_materia
                WHERE cd.id_carga = :c
            ";
            $stmtEx = $pdo->prepare($sqlExisting);
            $stmtEx->execute([':c' => $idCarga]);
            $existingSubjects = $stmtEx->fetchAll();

            // Obtener horarios de materias nuevas a agregar
            // (Usamos placeholders dinámicos para el array)
            // Aseguramos que el array sea indexado numéricamente (0, 1, 2...) para execute()
            $ids = array_values($idGrupoMaterias);
            $inQuery = implode(',', array_fill(0, count($ids), '?'));
            $sqlNew = "
                SELECT gm.id_grupo_materia, m.nombre,
                       gm.id_bloque_lun, gm.id_bloque_mar, gm.id_bloque_mie, gm.id_bloque_jue, gm.id_bloque_vie
                FROM grupo_materia gm
                JOIN materia m ON m.clave_materia = gm.clave_materia
                WHERE gm.id_grupo_materia IN ($inQuery)
            ";
            $stmtNew = $pdo->prepare($sqlNew);
            $stmtNew->execute($ids);
            $newSubjects = $stmtNew->fetchAll();

            // Verificar colisiones
            $days = ['lun', 'mar', 'mie', 'jue', 'vie'];

            foreach ($newSubjects as $newSub) {
                // Verificar contra existentes
                foreach ($existingSubjects as $exSub) {
                    foreach ($days as $day) {
                        $col = 'id_bloque_' . $day;
                        
                        if ($newSub[$col] !== null && $exSub[$col] !== null && $newSub[$col] == $exSub[$col]) {
                            throw new \RuntimeException(sprintf(
                                "Choque de horario: '%s' choca con '%s' en el bloque %s del día %s.",
                                $newSub['nombre'],
                                $exSub['nombre'],
                                $newSub[$col],
                                ucfirst($day)
                            ));
                        }
                    }
                }

                // Verificar contra otras nuevas (autocolisión)
                foreach ($newSubjects as $otherNew) {
                    if ($newSub['id_grupo_materia'] === $otherNew['id_grupo_materia']) {
                        continue;
                    }
                    foreach ($days as $day) {
                        $col = 'id_bloque_' . $day;
                        if ($newSub[$col] !== null && $otherNew[$col] !== null && $newSub[$col] == $otherNew[$col]) {
                             throw new \RuntimeException(sprintf(
                                "Choque de horario en selección: '%s' choca con '%s' en el bloque %s del día %s.",
                                $newSub['nombre'],
                                $otherNew['nombre'],
                                $newSub[$col],
                                ucfirst($day)
                            ));
                        }
                    }
                }
            }

            // Si no hay colisiones, procedemos a insertar
            foreach ($idGrupoMaterias as $idGM) {
                $idGM = (int)$idGM;
                if ($idGM <= 0) {
                    continue;
                }

                // Obtener datos del grupo_materia y validar que corresponda al periodo
                $sqlInfo = "
                    SELECT gm.id_grupo_materia, gm.clave_materia
                    FROM grupo_materia gm
                    JOIN grupo g ON g.id_grupo = gm.id_grupo
                    WHERE gm.id_grupo_materia = :gm
                      AND g.id_periodo = :pe
                    LIMIT 1
                ";
                $stmtInfo = $pdo->prepare($sqlInfo);
                $stmtInfo->execute([
                    ':gm' => $idGM,
                    ':pe' => $idPeriodo,
                ]);
                $row = $stmtInfo->fetch();

                if (!$row) {
                    // Podríamos lanzar excepción aquí si queremos ser estrictos
                    continue; 
                }

                $clave = (string)$row['clave_materia'];

                // 1) Insertar en carga_detalle (evitamos duplicados con INSERT IGNORE)
                $sqlCd = "
                    INSERT IGNORE INTO carga_detalle (id_carga, id_grupo_materia)
                    VALUES (:c, :gm)
                ";
                $stmtCd = $pdo->prepare($sqlCd);
                $stmtCd->execute([
                    ':c'  => $idCarga,
                    ':gm' => $idGM,
                ]);

                // 2) Insertar en alumno_materia para este periodo si no existe
                $sqlAm = "
                    INSERT INTO alumno_materia (id_alumno, clave_materia, id_periodo,
                                                calificacion, tipo_acred, estatus)
                    SELECT :al, :clave, :pe, NULL, NULL, 'C'
                    FROM DUAL
                    WHERE NOT EXISTS (
                        SELECT 1
                        FROM alumno_materia
                        WHERE id_alumno = :al2
                          AND clave_materia = :clave2
                          AND id_periodo = :pe2
                    )
                ";
                $stmtAm = $pdo->prepare($sqlAm);
                $stmtAm->execute([
                    ':al'     => $idAlumno,
                    ':clave'  => $clave,
                    ':pe'     => $idPeriodo,
                    ':al2'    => $idAlumno,
                    ':clave2' => $clave,
                    ':pe2'    => $idPeriodo,
                ]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Cancela una carga:
     *  - Borra las materias 'C' (cursando) del alumno_materia para ese periodo/carga
     *  - Borra el detalle de la carga
     *  - Marca la carga_academica como 'C' (cancelada)
     *
     * @param int    $idCarga
     * @param string $idAlumno
     * @param string $idPeriodo
     */
    public function cancelLoad(int $idCarga, string $idAlumno, string $idPeriodo): void
    {
        $pdo = db();

        try {
            $pdo->beginTransaction();

            // 1) Borrar registros de alumno_materia asociados a esta carga (solo estatus 'C')
            $sqlDeleteAlumnoMat = "
                DELETE am
                FROM alumno_materia am
                JOIN grupo_materia gm
                  ON gm.clave_materia = am.clave_materia
                JOIN carga_detalle cd
                  ON cd.id_grupo_materia = gm.id_grupo_materia
                WHERE cd.id_carga   = :c
                  AND am.id_alumno  = :al
                  AND am.id_periodo = :pe
                  AND am.estatus    = 'C'
            ";
            $stmt = $pdo->prepare($sqlDeleteAlumnoMat);
            $stmt->execute([
                ':c'  => $idCarga,
                ':al' => $idAlumno,
                ':pe' => $idPeriodo,
            ]);

            // 2) Borrar detalle de la carga
            $stmt = $pdo->prepare("DELETE FROM carga_detalle WHERE id_carga = :c");
            $stmt->execute([':c' => $idCarga]);

            // 3) Marcar carga como cancelada
            $stmt = $pdo->prepare("
                UPDATE carga_academica
                SET estatus = 'C'
                WHERE id_carga = :c
                  AND estatus = 'A'
            ");
            $stmt->execute([':c' => $idCarga]);

            if ($stmt->rowCount() === 0) {
                throw new \RuntimeException('La carga ya está cancelada o no existe.');
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Obtiene todos los bloques horarios (id => etiqueta).
     * @return array
     */
    public function getAllBlocks(): array
    {
        $pdo = db();
        $stmt = $pdo->query("SELECT id_bloque, etiqueta FROM bloque_horario");
        return $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    /**
     * Sugiere un grupo para 1er semestre basado en balanceo de carga (el menos lleno).
     * @param string $idPeriodo
     * @return array|null Datos del grupo sugerido o null
     */
    public function suggestGroupForFirstSemester(string $idPeriodo): ?array
    {
        $pdo = db();
        // Contamos cuántos alumnos hay en cada grupo de 1er semestre
        // (Contamos entradas en carga_detalle asociadas a grupos de ese semestre)
        $sql = "
            SELECT g.id_grupo, g.paquete, g.letra_grupo, g.cupo,
                   (
                       SELECT COUNT(DISTINCT ca.id_alumno)
                       FROM carga_academica ca
                       JOIN carga_detalle cd ON cd.id_carga = ca.id_carga
                       JOIN grupo_materia gm ON gm.id_grupo_materia = cd.id_grupo_materia
                       WHERE gm.id_grupo = g.id_grupo
                         AND ca.estatus = 'A'
                   ) as inscritos
            FROM grupo g
            WHERE g.semestre = 1
              AND g.id_periodo = :pe
            HAVING inscritos < g.cupo
            ORDER BY inscritos ASC, g.paquete ASC
            LIMIT 1
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':pe' => $idPeriodo]);
        $row = $stmt->fetch();
        
        return $row ?: null;
    }
}
