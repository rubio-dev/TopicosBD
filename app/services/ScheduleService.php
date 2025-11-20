<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Subject.php';
require_once __DIR__ . '/../models/GroupModel.php';
require_once __DIR__ . '/../models/Student.php';

class ScheduleService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Oferta de materias y grupos (respeta seriación y prioridad).
     */
    public function getScheduleOffer(string $controlNumber, string $periodCode): array
    {
        $stmt = $this->db->prepare('CALL sp_oferta_materias_alumno(:nc, :per)');
        $stmt->execute([
            ':nc'  => $controlNumber,
            ':per' => $periodCode,
        ]);

        $rows = $stmt->fetchAll();
        $stmt->closeCursor();

        $data = [];

        foreach ($rows as $row) {
            $subjectCode = $row['clave_materia'];

            if (!isset($data[$subjectCode])) {
                $data[$subjectCode] = [
                    'subject' => new Subject($row),
                    'groups'  => [],
                ];
            }

            $data[$subjectCode]['groups'][] = new GroupModel($row);
        }

        return $data;
    }

    /**
     * Lista de alumnos para el selector.
     */
    public function getStudentList(): array
    {
        $stmt = $this->db->query(
            'SELECT num_control, nombre, semestre_actual
               FROM alumno
           ORDER BY num_control'
        );

        $students = [];
        while ($row = $stmt->fetch()) {
            $students[] = new Student($row);
        }
        return $students;
    }

    /**
     * Lista de períodos para el selector.
     */
    public function getPeriodList(): array
    {
        $stmt = $this->db->query(
            'SELECT id_periodo, clave_periodo, descripcion
               FROM periodo
           ORDER BY id_periodo'
        );
        return $stmt->fetchAll();
    }

    /**
     * Carga actual del alumno en un periodo:
     * grupos ya inscritos en inscripcion_grupo.
     */
    public function getCurrentEnrollment(string $controlNumber, string $periodCode): array
    {
        $sql = "
            SELECT
                ig.id_inscripcion,
                ig.id_grupo,
                g.paquete,
                g.grupo,
                g.clave_materia,
                g.hor_lun,
                g.hor_mar,
                g.hor_mie,
                g.hor_jue,
                g.hor_vie,
                m.nombre       AS nombre_materia,
                p.clave_periodo
            FROM inscripcion_grupo ig
            JOIN grupo   g ON g.id_grupo    = ig.id_grupo
            JOIN periodo p ON p.id_periodo  = g.id_periodo
            JOIN materia m ON m.clave_materia = g.clave_materia
            WHERE ig.num_control   = :nc
              AND p.clave_periodo  = :per
            ORDER BY g.paquete, g.clave_materia, g.grupo
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nc'  => $controlNumber,
            ':per' => $periodCode,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Intenta inscribir al alumno en los grupos seleccionados.
     */
    public function registerSelection(string $controlNumber, string $periodCode, array $groupIds): array
    {
        $result = [
            'success'  => false,
            'messages' => [],
        ];

        // Normalizar IDs (enteros únicos)
        $groupIds = array_values(array_unique(array_map('intval', $groupIds)));

        if (empty($groupIds)) {
            $result['messages'][] = 'No seleccionaste ningún grupo.';
            return $result;
        }

        // Traer info de los grupos
        $placeholders = implode(',', array_fill(0, count($groupIds), '?'));

        $sql = "
            SELECT g.*, p.clave_periodo
            FROM grupo g
            JOIN periodo p ON p.id_periodo = g.id_periodo
            WHERE g.id_grupo IN ($placeholders)
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($groupIds);
        $groups = $stmt->fetchAll();

        if (count($groups) !== count($groupIds)) {
            $result['messages'][] = 'Uno o más grupos seleccionados no existen.';
            return $result;
        }

        // Validar que todos son del período correcto
        $periodCodes = [];
        foreach ($groups as $g) {
            $periodCodes[$g['clave_periodo']] = true;
        }

        if (count($periodCodes) !== 1 || !isset($periodCodes[$periodCode])) {
            $result['messages'][] = 'Hay grupos de otro período en la selección.';
            return $result;
        }

        // Validar choques de horario
        $days = ['hor_lun', 'hor_mar', 'hor_mie', 'hor_jue', 'hor_vie'];

        foreach ($days as $day) {
            $map = [];
            foreach ($groups as $g) {
                $hour = $g[$day];
                if ($hour === null || $hour === '') {
                    continue;
                }

                if (isset($map[$hour]) && $map[$hour] !== $g['id_grupo']) {
                    $result['messages'][] =
                        "Choque de horario en {$this->dayLabel($day)} a la hora $hour.";
                    return $result;
                }

                $map[$hour] = $g['id_grupo'];
            }
        }

        // Validar seriación para cada materia
        $subjectCodes = [];
        foreach ($groups as $g) {
            $subjectCodes[$g['clave_materia']] = true;
        }

        foreach (array_keys($subjectCodes) as $subjectCode) {
            $stmt = $this->db->prepare(
                'SELECT fn_puede_cursar_materia(:nc, :mat) AS puede'
            );
            $stmt->execute([
                ':nc'  => $controlNumber,
                ':mat' => $subjectCode,
            ]);
            $row = $stmt->fetch();

            if (!$row || (int)$row['puede'] !== 1) {
                $result['messages'][] =
                    "El alumno no cumple seriación para la materia $subjectCode.";
                return $result;
            }
        }

        // Si todo bien, insertar en inscripcion_grupo
        try {
            $this->db->beginTransaction();

            $insert = $this->db->prepare(
                'INSERT IGNORE INTO inscripcion_grupo (num_control, id_grupo)
                 VALUES (:nc, :idg)'
            );

            foreach ($groupIds as $idg) {
                $insert->execute([
                    ':nc'  => $controlNumber,
                    ':idg' => $idg,
                ]);
            }

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            $result['messages'][] = 'Error al guardar la inscripción: ' . $e->getMessage();
            return $result;
        }

        $result['success']  = true;
        $result['messages'][] =
            'Inscripción registrada correctamente para ' . count($groupIds) . ' grupo(s).';

        return $result;
    }

    private function dayLabel(string $field): string
    {
        return match ($field) {
            'hor_lun' => 'lunes',
            'hor_mar' => 'martes',
            'hor_mie' => 'miércoles',
            'hor_jue' => 'jueves',
            'hor_vie' => 'viernes',
            default   => 'día',
        };
    }
}
