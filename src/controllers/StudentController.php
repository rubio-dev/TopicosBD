<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/StudentModel.php';

/**
 * StudentController
 *
 * Controlador para las operaciones de gestión de alumnos.
 */
class StudentController
{
    private StudentModel $model;

    public function __construct()
    {
        $this->model = new StudentModel();
    }

    /**
     * Lista de alumnos.
     */
    public function index(): void
    {
        $students = $this->model->getAll();
        $mensaje  = isset($_GET['mensaje']) ? (string)$_GET['mensaje'] : null;
        $error    = isset($_GET['error']) ? (string)$_GET['error'] : null;

        // Variables para el layout
        $pageTitle    = 'Alumnos';
        $pageSubtitle = 'Listado de alumnos';

        require __DIR__ . '/../views/layout/header.php';
        require __DIR__ . '/../views/students/index.php';
        require __DIR__ . '/../views/layout/footer.php';
    }

    /**
     * Formulario de alta de alumno.
     */
    public function create(): void
    {
        // Valores por defecto para el formulario
        $anioActual = (int)date('Y');

        $student = [
            'id_alumno'       => '',
            'anio_ingreso'    => $anioActual,
            'ciclo_ingreso'   => '02',
            'nombre'          => '',
            'semestre_actual' => 1,
        ];
        $errors   = [];
        $isEdit   = false;

        $pageTitle    = 'Alumnos';
        $pageSubtitle = 'Nuevo alumno';

        require __DIR__ . '/../views/layout/header.php';
        require __DIR__ . '/../views/students/form.php';
        require __DIR__ . '/../views/layout/footer.php';
    }

    /**
     * Procesa el alta de un alumno (POST).
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?m=alumnos&a=index');
            exit;
        }

        $data = [
            'anio_ingreso'    => $_POST['anio_ingreso'] ?? '',
            'ciclo_ingreso'   => $_POST['ciclo_ingreso'] ?? '',
            'nombre'          => $_POST['nombre'] ?? '',
            'semestre_actual' => $_POST['semestre_actual'] ?? '',
        ];

        try {
            $idAlumno = $this->model->create($data);

            $mensaje = 'Alumno creado correctamente con ID ' . $idAlumno;
            header('Location: index.php?m=alumnos&a=index&mensaje=' . urlencode($mensaje));
            exit;

        } catch (InvalidArgumentException $ex) {
            // Error de validación de negocio
            $errors  = [$ex->getMessage()];
            $student = [
                'id_alumno'       => '',
                'anio_ingreso'    => $data['anio_ingreso'],
                'ciclo_ingreso'   => $data['ciclo_ingreso'],
                'nombre'          => $data['nombre'],
                'semestre_actual' => $data['semestre_actual'],
            ];
            $isEdit = false;

            $pageTitle    = 'Alumnos';
            $pageSubtitle = 'Nuevo alumno';

            require __DIR__ . '/../views/layout/header.php';
            require __DIR__ . '/../views/students/form.php';
            require __DIR__ . '/../views/layout/footer.php';

        } catch (PDOException $ex) {
            // Error técnico de base de datos
            $errors  = ['Ocurrió un error al guardar el alumno en la base de datos.'];
            $student = [
                'id_alumno'       => '',
                'anio_ingreso'    => $data['anio_ingreso'],
                'ciclo_ingreso'   => $data['ciclo_ingreso'],
                'nombre'          => $data['nombre'],
                'semestre_actual' => $data['semestre_actual'],
            ];
            $isEdit = false;

            $pageTitle    = 'Alumnos';
            $pageSubtitle = 'Nuevo alumno';

            require __DIR__ . '/../views/layout/header.php';
            require __DIR__ . '/../views/students/form.php';
            require __DIR__ . '/../views/layout/footer.php';
        }
    }

    /**
     * Formulario de edición de un alumno.
     */
    public function edit(): void
    {
        $idAlumno = isset($_GET['id']) ? (string)$_GET['id'] : '';

        if ($idAlumno === '') {
            $error = 'ID de alumno inválido.';
            header('Location: index.php?m=alumnos&a=index&error=' . urlencode($error));
            exit;
        }

        $student = $this->model->getById($idAlumno);

        if ($student === null) {
            $error = 'El alumno no existe.';
            header('Location: index.php?m=alumnos&a=index&error=' . urlencode($error));
            exit;
        }

        $errors = [];
        $isEdit = true;

        $pageTitle    = 'Alumnos';
        $pageSubtitle = 'Editar alumno';

        require __DIR__ . '/../views/layout/header.php';
        require __DIR__ . '/../views/students/form.php';
        require __DIR__ . '/../views/layout/footer.php';
    }

    /**
     * Procesa la edición de un alumno (POST).
     */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?m=alumnos&a=index');
            exit;
        }

        $idAlumno = isset($_POST['id_alumno']) ? (string)$_POST['id_alumno'] : '';

        if ($idAlumno === '') {
            $error = 'ID de alumno inválido.';
            header('Location: index.php?m=alumnos&a=index&error=' . urlencode($error));
            exit;
        }

        $data = [
            'nombre'          => $_POST['nombre'] ?? '',
            'semestre_actual' => $_POST['semestre_actual'] ?? '',
        ];

        try {
            $this->model->update($idAlumno, $data);

            $mensaje = 'Alumno actualizado correctamente.';
            header('Location: index.php?m=alumnos&a=index&mensaje=' . urlencode($mensaje));
            exit;

        } catch (InvalidArgumentException $ex) {
            $errors  = [$ex->getMessage()];
            $student = $this->model->getById($idAlumno);
            if ($student) {
                $student['nombre']          = $data['nombre'];
                $student['semestre_actual'] = $data['semestre_actual'];
            }

            $isEdit       = true;
            $pageTitle    = 'Alumnos';
            $pageSubtitle = 'Editar alumno';

            require __DIR__ . '/../views/layout/header.php';
            require __DIR__ . '/../views/students/form.php';
            require __DIR__ . '/../views/layout/footer.php';

        } catch (PDOException $ex) {
            $errors  = ['Ocurrió un error al actualizar el alumno en la base de datos.'];
            $student = $this->model->getById($idAlumno);
            if ($student) {
                $student['nombre']          = $data['nombre'];
                $student['semestre_actual'] = $data['semestre_actual'];
            }

            $isEdit       = true;
            $pageTitle    = 'Alumnos';
            $pageSubtitle = 'Editar alumno';

            require __DIR__ . '/../views/layout/header.php';
            require __DIR__ . '/../views/students/form.php';
            require __DIR__ . '/../views/layout/footer.php';
        }
    }

    /**
     * Elimina un alumno si no tiene cargas ni historial.
     */
    public function delete(): void
    {
        $idAlumno = isset($_GET['id']) ? (string)$_GET['id'] : '';

        if ($idAlumno === '') {
            $error = 'ID de alumno inválido.';
            header('Location: index.php?m=alumnos&a=index&error=' . urlencode($error));
            exit;
        }

        // Verificar si se puede eliminar
        if (!$this->model->canDelete($idAlumno)) {
            $error = 'No se puede eliminar el alumno porque tiene cargas académicas o historial de materias.';
            header('Location: index.php?m=alumnos&a=index&error=' . urlencode($error));
            exit;
        }

        try {
            $this->model->delete($idAlumno);
            $mensaje = 'Alumno eliminado correctamente.';
            header('Location: index.php?m=alumnos&a=index&mensaje=' . urlencode($mensaje));
            exit;

        } catch (PDOException $ex) {
            $error = 'Ocurrió un error al eliminar el alumno en la base de datos.';
            header('Location: index.php?m=alumnos&a=index&error=' . urlencode($error));
            exit;
        }
    }
}
