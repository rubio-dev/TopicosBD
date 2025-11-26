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
     * - Ver si hay carga activa
     * - Carga automática
     * - Carga manual (agregar materias)
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
        $retakeOptions     = [];
        $newOptions        = [];

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

                // Grupos disponibles en ese periodo para el semestre del alumno (para carga auto)
                $semestreAlumno    = (int)$selectedStudent['semestre_actual'];
                $groupsForSemester = $this->groupModel
                    ->getByPeriodAndSemester($idPeriodo, $semestreAlumno);

                // Opciones para carga manual
                $retakeOptions = $this->enrollmentModel
                    ->getRetakeOptions($idAlumno, $idPeriodo);

                $newOptions = $this->enrollmentModel
                    ->getNewSubjectOptions($idAlumno, $idPeriodo, $semestreAlumno);
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
        } catch (\Throwable $e) {
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

    /**
     * Acción para agregar materias manualmente a la carga.
     * Si no existe carga activa, crea una carga vacía primero.
     */
    public function addManual(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?m=cargas&a=index');
            exit;
        }

        $idAlumno  = isset($_POST['id_alumno']) ? trim((string)$_POST['id_alumno']) : '';
        $idPeriodo = isset($_POST['id_periodo']) ? trim((string)$_POST['id_periodo']) : '';
        $gmRaw     = $_POST['gm'] ?? [];

        if ($idAlumno === '' || $idPeriodo === '') {
            $msg = 'Faltan datos para la carga manual.';
            header('Location: index.php?m=cargas&a=index&error=' . urlencode($msg));
            exit;
        }

        if (!is_array($gmRaw) || count($gmRaw) === 0) {
            $msg = 'No seleccionaste ninguna materia para agregar.';
            $url = 'index.php?m=cargas&a=index'
                 . '&id_alumno=' . urlencode($idAlumno)
                 . '&id_periodo=' . urlencode($idPeriodo)
                 . '&error=' . urlencode($msg);
            header('Location: ' . $url);
            exit;
        }

        // Normalizar ids de grupo_materia
        $gmIds = [];
        foreach ($gmRaw as $v) {
            $n = (int)$v;
            if ($n > 0) {
                $gmIds[] = $n;
            }
        }

        if (empty($gmIds)) {
            $msg = 'Ninguna materia seleccionada es válida.';
            $url = 'index.php?m=cargas&a=index'
                 . '&id_alumno=' . urlencode($idAlumno)
                 . '&id_periodo=' . urlencode($idPeriodo)
                 . '&error=' . urlencode($msg);
            header('Location: ' . $url);
            exit;
        }

        // Buscar o crear carga activa
        $active = $this->enrollmentModel
            ->findActiveByStudentAndPeriod($idAlumno, $idPeriodo);

        if ($active !== null) {
            $idCarga = (int)$active['id_carga'];
        } else {
            // crear carga vacía (manual)
            $idCarga = $this->enrollmentModel->createEmptyLoad($idAlumno, $idPeriodo);
        }

        try {
            $this->enrollmentModel
                ->addSubjectsToLoad($idCarga, $idAlumno, $idPeriodo, $gmIds);

            $msg = 'Materias agregadas a la carga académica.';
            $url = 'index.php?m=cargas&a=index'
                 . '&id_alumno=' . urlencode($idAlumno)
                 . '&id_periodo=' . urlencode($idPeriodo)
                 . '&success=' . urlencode($msg);
            header('Location: ' . $url);
        } catch (\Throwable $e) {
            $msg = 'No fue posible agregar las materias: ' . $e->getMessage();
            $url = 'index.php?m=cargas&a=index'
                 . '&id_alumno=' . urlencode($idAlumno)
                 . '&id_periodo=' . urlencode($idPeriodo)
                 . '&error=' . urlencode($msg);
            header('Location: ' . $url);
        }
        exit;
    }

    /**
     * Acción para cancelar una carga activa.
     */
    public function cancel(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?m=cargas&a=index');
            exit;
        }

        $idCarga   = isset($_POST['id_carga']) ? (int)$_POST['id_carga'] : 0;
        $idAlumno  = isset($_POST['id_alumno']) ? trim((string)$_POST['id_alumno']) : '';
        $idPeriodo = isset($_POST['id_periodo']) ? trim((string)$_POST['id_periodo']) : '';

        if ($idCarga <= 0 || $idAlumno === '' || $idPeriodo === '') {
            $msg = 'Faltan datos para cancelar la carga.';
            header('Location: index.php?m=cargas&a=index&error=' . urlencode($msg));
            exit;
        }

        try {
            $this->enrollmentModel->cancelLoad($idCarga, $idAlumno, $idPeriodo);
            $msg = 'La carga académica fue cancelada correctamente.';
            $url = 'index.php?m=cargas&a=index'
                 . '&id_alumno=' . urlencode($idAlumno)
                 . '&id_periodo=' . urlencode($idPeriodo)
                 . '&success=' . urlencode($msg);
            header('Location: ' . $url);
        } catch (\Throwable $e) {
            $msg = 'No fue posible cancelar la carga: ' . $e->getMessage();
            $url = 'index.php?m=cargas&a=index'
                 . '&id_alumno=' . urlencode($idAlumno)
                 . '&id_periodo=' . urlencode($idPeriodo)
                 . '&error=' . urlencode($msg);
            header('Location: ' . $url);
        }
        exit;
    }
}
