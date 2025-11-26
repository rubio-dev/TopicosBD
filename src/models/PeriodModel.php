<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';

/**
 * PeriodModel
 *
 * Acceso a los periodos lectivos (tabla periodo_lectivo).
 */
class PeriodModel
{
    /**
     * Devuelve todos los periodos ordenados del más reciente al más antiguo.
     *
     * @return array
     */
    public function getAll(): array
    {
        $pdo = db();
        $sql = "SELECT id_periodo, anio, ciclo, descripcion, fecha_inicio, fecha_fin
                FROM periodo_lectivo
                ORDER BY anio DESC, ciclo DESC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un periodo por su id.
     *
     * @param string $idPeriodo
     * @return array|null
     */
    public function getById(string $idPeriodo): ?array
    {
        $pdo = db();
        $sql = "SELECT id_periodo, anio, ciclo, descripcion, fecha_inicio, fecha_fin
                FROM periodo_lectivo
                WHERE id_periodo = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $idPeriodo]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
