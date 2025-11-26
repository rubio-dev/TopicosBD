<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * StudentModel
 *
 * Encapsula el acceso a la tabla alumno y reglas básicas de negocio
 * relativas a alumnos.
 */
class StudentModel
{
    /**
     * Obtiene todos los alumnos ordenados por semestre y luego por id.
     *
     * @return array
     */
    public function getAll(): array
    {
        $pdo = db();
        $sql = "SELECT id_alumno, anio_ingreso, ciclo_ingreso, nombre, semestre_actual
                FROM alumno
                ORDER BY semestre_actual, id_alumno";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Busca un alumno por su id.
     *
     * @param string $idAlumno
     * @return array|null
     */
    public function getById(string $idAlumno): ?array
    {
        $pdo = db();
        $sql = "SELECT id_alumno, anio_ingreso, ciclo_ingreso, nombre, semestre_actual
                FROM alumno
                WHERE id_alumno = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $idAlumno]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Crea un nuevo alumno generando automáticamente su id_alumno.
     *
     * @param array $data
     * @return string id_alumno generado
     *
     * @throws InvalidArgumentException
     * @throws PDOException
     */
    public function create(array $data): string
    {
        $anioIngreso    = isset($data['anio_ingreso']) ? (int)$data['anio_ingreso'] : 0;
        $cicloIngreso   = isset($data['ciclo_ingreso']) ? trim((string)$data['ciclo_ingreso']) : '';
        $nombre         = isset($data['nombre']) ? trim((string)$data['nombre']) : '';
        $semestreActual = isset($data['semestre_actual']) ? (int)$data['semestre_actual'] : 0;

        // Validaciones del lado de PHP
        if ($anioIngreso < 2000 || $anioIngreso > 2100) {
            throw new InvalidArgumentException('El año de ingreso no es válido.');
        }

        if (!in_array($cicloIngreso, ['01', '02'], true)) {
            throw new InvalidArgumentException('El ciclo de ingreso debe ser 01 o 02.');
        }

        if (!in_array($semestreActual, [1, 3, 5], true)) {
            throw new InvalidArgumentException('El semestre actual debe ser 1, 3 o 5.');
        }

        if ($nombre === '') {
            throw new InvalidArgumentException('El nombre del alumno es obligatorio.');
        }

        // Generar el id_alumno con el patrón AACCNNNN
        $idAlumno = $this->generateStudentId($anioIngreso, $cicloIngreso);

        $pdo = db();
        $sql = "INSERT INTO alumno (id_alumno, anio_ingreso, ciclo_ingreso, nombre, semestre_actual)
                VALUES (:id_alumno, :anio_ingreso, :ciclo_ingreso, :nombre, :semestre_actual)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_alumno'       => $idAlumno,
            ':anio_ingreso'    => $anioIngreso,
            ':ciclo_ingreso'   => $cicloIngreso,
            ':nombre'          => $nombre,
            ':semestre_actual' => $semestreActual,
        ]);

        return $idAlumno;
    }

    /**
     * Actualiza los datos básicos de un alumno.
     * Por requerimiento sólo se permite cambiar nombre y semestre_actual.
     *
     * @param string $idAlumno
     * @param array  $data
     *
     * @throws InvalidArgumentException
     * @throws PDOException
     */
    public function update(string $idAlumno, array $data): void
    {
        $nombre         = isset($data['nombre']) ? trim((string)$data['nombre']) : '';
        $semestreActual = isset($data['semestre_actual']) ? (int)$data['semestre_actual'] : 0;

        if ($nombre === '') {
            throw new InvalidArgumentException('El nombre del alumno es obligatorio.');
        }

        if (!in_array($semestreActual, [1, 3, 5], true)) {
            throw new InvalidArgumentException('El semestre actual debe ser 1, 3 o 5.');
        }

        $pdo = db();
        $sql = "UPDATE alumno
                SET nombre = :nombre,
                    semestre_actual = :semestre_actual
                WHERE id_alumno = :id_alumno";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre'          => $nombre,
            ':semestre_actual' => $semestreActual,
            ':id_alumno'       => $idAlumno,
        ]);
    }

    /**
     * Indica si un alumno se puede eliminar (sin cargas académicas ni historial).
     *
     * @param string $idAlumno
     * @return bool
     */
    public function canDelete(string $idAlumno): bool
    {
        $pdo = db();

        // Verificar si tiene cargas académicas
        $sql = "SELECT COUNT(*) FROM carga_academica WHERE id_alumno = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $idAlumno]);
        $cargas = (int)$stmt->fetchColumn();

        // Verificar si tiene historial en alumno_materia
        $sql = "SELECT COUNT(*) FROM alumno_materia WHERE id_alumno = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $idAlumno]);
        $historial = (int)$stmt->fetchColumn();

        return ($cargas === 0 && $historial === 0);
    }

    /**
     * Elimina un alumno. Se recomienda llamar antes a canDelete().
     *
     * @param string $idAlumno
     */
    public function delete(string $idAlumno): void
    {
        $pdo = db();
        $sql = "DELETE FROM alumno WHERE id_alumno = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $idAlumno]);
    }

    /**
     * Genera un nuevo id_alumno usando el patrón AACCNNNN:
     *  - AA: últimos dos dígitos del año de ingreso
     *  - CC: ciclo de ingreso (01 o 02)
     *  - NNNN: consecutivo incremental dentro de ese año y ciclo
     *
     * @param int    $anioIngreso
     * @param string $cicloIngreso
     * @return string
     */
    private function generateStudentId(int $anioIngreso, string $cicloIngreso): string
    {
        $pdo = db();

        // AA = últimos 2 dígitos del año
        $anio2 = $anioIngreso % 100;

        // Obtener el mayor consecutivo actual para ese año y ciclo
        $sql = "SELECT MAX(CAST(SUBSTRING(id_alumno, 5, 4) AS UNSIGNED)) AS max_consec
                FROM alumno
                WHERE anio_ingreso = :anio_ingreso
                  AND ciclo_ingreso = :ciclo_ingreso";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':anio_ingreso'  => $anioIngreso,
            ':ciclo_ingreso' => $cicloIngreso,
        ]);
        $maxConsec = (int)($stmt->fetchColumn() ?? 0);

        $next = $maxConsec + 1;

        return sprintf('%02d%02d%04d', $anio2, (int)$cicloIngreso, $next);
    }
}
