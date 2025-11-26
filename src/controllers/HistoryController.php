<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/StudentModel.php';
require_once __DIR__ . '/../models/HistoryModel.php';

/**
 * HistoryController
 *
 * Controlador para historial académico (solo lectura).
 */
class HistoryController
{
    private StudentModel $studentModel;
    private HistoryModel $historyModel;

    public function __construct()
    {
        $this->studentModel = new StudentModel();
        $this->historyModel = new HistoryModel();
    }

    /**
     * Pantalla principal:
     * - Seleccionar alumno
     * - Ver historial completo
     */
    public function index(): void
    {
        $students = $this->studentModel->getAll();

        $idAlumno   = isset($_GET['id_alumno']) ? trim((string)$_GET['id_alumno']) : '';
        $errorMsg   = isset($_GET['error']) ? (string)$_GET['error'] : '';
        $successMsg = isset($_GET['success']) ? (string)$_GET['success'] : '';

        $selectedStudent = null;
        $history         = [];

        if ($idAlumno !== '') {
            $selectedStudent = $this->studentModel->getById($idAlumno);
            if ($selectedStudent === null) {
                $errorMsg = 'El alumno especificado no existe.';
            } else {
                $history = $this->historyModel->getHistory($idAlumno);
            }
        }

        $pageTitle    = 'Historial Académico';
        $pageSubtitle = 'Consulta de materias cursadas y calificaciones.';

        require __DIR__ . '/../views/layout/header.php';
        require __DIR__ . '/../views/history/index.php';
        require __DIR__ . '/../views/layout/footer.php';
    }
}
