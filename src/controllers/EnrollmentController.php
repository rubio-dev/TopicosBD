<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/StudentModel.php';
require_once __DIR__ . '/../models/PeriodModel.php';
require_once __DIR__ . '/../models/GroupModel.php';
require_once __DIR__ . '/../models/EnrollmentModel.php';

/**
 * EnrollmentController
 *
 * Controlador para gestión de cargas académicas.
 */
class EnrollmentController
{
    private StudentModel $studentModel;
    private PeriodModel $periodModel;
    private GroupModel $groupModel;
    private EnrollmentModel $enrollmentModel;

    public function __construct()
    {
        $this->studentModel    = new StudentModel();
        $this->periodModel     = new PeriodModel();
        $this->groupModel      = new GroupModel();
        $this->enrollmentModel = new EnrollmentModel();
    }

    /**
     * Pantalla principal:
     * - Seleccionar alumno (lista) y periodo
     * - Ver si ya hay carga activa
     * - Ofrecer creación de carga automática
     */
    public function index(): void
    {
        // Lista de alumnos para el combo
        $students = $this->studentModel->getAll();

        // Periodos para el combo
        $periods = $this->periodModel->getAll();

        $idAlumno   = isset($_GET['id_alumno']) ? trim((string)$_GET['id_alumno']) : '';
        $idPeriodo  = isset($_GET['id_periodo']) ? trim((string)$_GET['id_periodo']) : '';
        $errorMsg   = isset($_GET['error']) ? (string)$_GET['error'] : '';
        $successMsg = isset($_GET['success']) ? (string)$_GET['success'] : '';

        $selectedStudent   = null;
        $selectedPeriod    = null;
        $activeLoad        = null;
        $loadDetails       = [];
        $groupsForSemester = [];

        if ($idAlumno !== '' && $idPeriodo !== '') {
            // Buscar alumno y periodo válidos
            $selectedStudent = $this->studentModel->getById($idAlumno);
            $selectedPeriod  = $this->periodModel->getById($idPeriodo);

            if ($selectedStudent === null) {
                $errorMsg = 'El alumno especificado no existe.';
            } elseif ($selectedPeriod === null) {
                $errorMsg = 'El periodo especificado no existe.';
            } else {
                // Buscar carga activa
                $activeLoad = $this->enrollmentModel
                    ->findActiveByStudentAndPeriod($idAlumno, $idPeriodo);

                if ($activeLoad !== null) {
                    $loadDetails = $this->enrollmentModel
                        ->getDetails((int)$activeLoad['id_carga']);
                }

                // Grupos disponibles en ese periodo para el semestre del alumno
                $semestreAlumno    = (int)$selectedStudent['semestre_actual'];
                $groupsForSemester = $this->groupModel
                    ->getByPeriodAndSemester($idPeriodo, $semestreAlumno);
            }
        }

        $pageTitle    = 'Cargas académicas';
        $pageSubtitle = 'Gestión de cargas automáticas y manuales por alumno';

        require __DIR__ . '/../views/layout/header.php';
        require __DIR__ . '/../views/enrollments/index.php';
        require __DIR__ . '/../views/layout/footer.php';
    }

    /**
     * Acción para crear la carga automática.
     * Llama al SP sp_crear_carga_automatica.
     */
    public function createAuto(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?m=cargas&a=index');
            exit;
        }

        $idAlumno  = isset($_POST['id_alumno']) ? trim((string)$_POST['id_alumno']) : '';
        $idPeriodo = isset($_POST['id_periodo']) ? trim((string)$_POST['id_periodo']) : '';
        $idGrupo   = isset($_POST['id_grupo']) ? (int)$_POST['id_grupo'] : 0;

        if ($idAlumno === '' || $idPeriodo === '' || $idGrupo <= 0) {
            $msg = 'Faltan datos para crear la carga automática.';
            header('Location: index.php?m=cargas&a=index&error=' . urlencode($msg));
            exit;
        }

        // Verificar que no exista ya una carga activa
        $active = $this->enrollmentModel
            ->findActiveByStudentAndPeriod($idAlumno, $idPeriodo);

        if ($active !== null) {
            $msg = 'El alumno ya tiene una carga activa en ese periodo.';
            $url = 'index.php?m=cargas&a=index'
                 . '&id_alumno=' . urlencode($idAlumno)
                 . '&id_periodo=' . urlencode($idPeriodo)
                 . '&error=' . urlencode($msg);
            header('Location: ' . $url);
            exit;
        }

        try {
            $this->enrollmentModel->createAutomaticLoad($idAlumno, $idPeriodo, $idGrupo);
            $msg = 'Carga automática generada correctamente.';
            $url = 'index.php?m=cargas&a=index'
                 . '&id_alumno=' . urlencode($idAlumno)
                 . '&id_periodo=' . urlencode($idPeriodo)
                 . '&success=' . urlencode($msg);
            header('Location: ' . $url);
        } catch (\PDOException $e) {
            // El SP puede lanzar SIGNAL con mensajes de negocio
            $msg = 'No fue posible crear la carga automática: ' . $e->getMessage();
            $url = 'index.php?m=cargas&a=index'
                 . '&id_alumno=' . urlencode($idAlumno)
                 . '&id_periodo=' . urlencode($idPeriodo)
                 . '&error=' . urlencode($msg);
            header('Location: ' . $url);
        }
        exit;
    }
}
