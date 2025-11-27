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
     * - Carga automática (Selección de paquete A, B, C)
     * - Carga manual (agregar materias, obligatorio si hay reprobadas)
     */
    public function index(): void
    {
        // 1. Obtener lista de alumnos para el combo de selección
        $students = $this->studentModel->getAll();

        // 2. Obtener lista de periodos disponibles
        $periods = $this->periodModel->getAll();

        // 3. Obtener parámetros de la URL (si existen)
        $idAlumno   = isset($_GET['id_alumno']) ? trim((string)$_GET['id_alumno']) : '';
        $idPeriodo  = isset($_GET['id_periodo']) ? trim((string)$_GET['id_periodo']) : '';
        $errorMsg   = isset($_GET['error']) ? (string)$_GET['error'] : '';
        $successMsg = isset($_GET['success']) ? (string)$_GET['success'] : '';

        // Inicializar variables para la vista
        $selectedStudent   = null;
        $selectedPeriod    = null;
        $activeLoad        = null;
        $loadDetails       = [];
        $groupsForSemester = []; // Grupos disponibles (Paquetes A, B, C)
        $retakeOptions     = []; // Materias reprobadas (Recursamiento)
        $newOptions        = []; // Materias nuevas disponibles
        $isIrregular       = false; // Bandera para indicar si el alumno es irregular

        // 4. Si se seleccionó alumno y periodo, cargar información detallada
        if ($idAlumno !== '' && $idPeriodo !== '') {
            // Buscar alumno y periodo válidos en la BD
            $selectedStudent = $this->studentModel->getById($idAlumno);
            $selectedPeriod  = $this->periodModel->getById($idPeriodo);

            if ($selectedStudent === null) {
                $errorMsg = 'El alumno especificado no existe.';
            } elseif ($selectedPeriod === null) {
                $errorMsg = 'El periodo especificado no existe.';
            } else {
                // 5. Buscar si el alumno ya tiene una carga activa en este periodo
                $activeLoad = $this->enrollmentModel
                    ->findActiveByStudentAndPeriod($idAlumno, $idPeriodo);

                if ($activeLoad !== null) {
                    // Si hay carga, obtener el detalle de materias inscritas
                    $loadDetails = $this->enrollmentModel
                        ->getDetails((int)$activeLoad['id_carga']);
                }

                // 6. Obtener grupos disponibles para el semestre del alumno (Paquetes A, B, C)
                // Esto sirve para la carga automática
                $semestreAlumno    = (int)$selectedStudent['semestre_actual'];
                $groupsForSemester = $this->groupModel
                    ->getByPeriodAndSemester($idPeriodo, $semestreAlumno);

                // 7. Obtener opciones para carga manual (recursamiento y nuevas)
                $retakeOptions = $this->enrollmentModel
                    ->getRetakeOptions($idAlumno, $idPeriodo);

                $newOptions = $this->enrollmentModel
                    ->getNewSubjectOptions($idAlumno, $idPeriodo, $semestreAlumno);
                
                // 7.1 Formatear horarios para mostrar en la vista
                $blocksMap = $this->enrollmentModel->getAllBlocks();
                
                $formatSchedule = function(&$options) use ($blocksMap) {
                    foreach ($options as &$opt) {
                        $parts = [];
                        $days = ['lun'=>'Lunes', 'mar'=>'Martes', 'mie'=>'Miércoles', 'jue'=>'Jueves', 'vie'=>'Viernes'];
                        foreach ($days as $k => $dayName) {
                            $bid = $opt['id_bloque_' . $k] ?? null;
                            if ($bid && isset($blocksMap[$bid])) {
                                // Ejemplo: Lunes 08:00-09:00
                                // O simplificado: Lun: 08-09
                                $parts[] = ucfirst($k) . ': ' . $blocksMap[$bid];
                            }
                        }
                        $opt['schedule_text'] = implode(', ', $parts);
                    }
                };
                
                $formatSchedule($retakeOptions);
                $formatSchedule($newOptions);

                // 8. Lógica especial para 1er Semestre (Asignación Automática)
                $suggestedGroup1 = null;
                if ($semestreAlumno === 1 && $activeLoad === null) {
                    $suggestedGroup1 = $this->enrollmentModel->suggestGroupForFirstSemester($idPeriodo);
                }

                // 9. Determinar si el alumno es irregular
                if (!empty($retakeOptions)) {
                    $isIrregular = true;
                    if (empty($errorMsg) && empty($successMsg) && $activeLoad === null) {
                        $errorMsg = 'Atención: El alumno tiene materias reprobadas. Debe realizar Carga Manual obligatoriamente.';
                    }
                }
            }
        }

        $pageTitle    = 'Cargas académicas';
        $pageSubtitle = 'Gestión de cargas automáticas y manuales por alumno';

        require __DIR__ . '/../views/layout/header.php';
        require __DIR__ . '/../views/enrollments/index.php';
        require __DIR__ . '/../views/layout/footer.php';
    }

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

        $active = $this->enrollmentModel->findActiveByStudentAndPeriod($idAlumno, $idPeriodo);

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
            $msg = 'No fue posible crear la carga automática: ' . $e->getMessage();
            $url = 'index.php?m=cargas&a=index'
                 . '&id_alumno=' . urlencode($idAlumno)
                 . '&id_periodo=' . urlencode($idPeriodo)
                 . '&error=' . urlencode($msg);
            header('Location: ' . $url);
        }
        exit;
    }

    public function addManual(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?m=cargas&a=index');
            exit;
        }

        $idAlumno  = isset($_POST['id_alumno']) ? trim((string)$_POST['id_alumno']) : '';
        $idPeriodo = isset($_POST['id_periodo']) ? trim((string)$_POST['id_periodo']) : '';
        
        // Recibir gm como array (puede ser asociativo si usamos radios name="gm[CLAVE]")
        // o indexado si usamos checkboxes name="gm[]"
        $gmRaw = $_POST['gm'] ?? [];

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
        $gmIds = array_unique($gmIds);

        if (empty($gmIds)) {
             $msg = 'No seleccionaste ninguna materia válida.';
             $url = 'index.php?m=cargas&a=index'
                  . '&id_alumno=' . urlencode($idAlumno)
                  . '&id_periodo=' . urlencode($idPeriodo)
                  . '&error=' . urlencode($msg);
             header('Location: ' . $url);
             exit;
        }

        $active = $this->enrollmentModel->findActiveByStudentAndPeriod($idAlumno, $idPeriodo);

        if ($active !== null) {
            $idCarga = (int)$active['id_carga'];
        } else {
            $idCarga = $this->enrollmentModel->createEmptyLoad($idAlumno, $idPeriodo);
        }

        try {
            $this->enrollmentModel->addSubjectsToLoad($idCarga, $idAlumno, $idPeriodo, $gmIds);
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
