<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/PeriodModel.php';
require_once __DIR__ . '/../models/GroupModel.php';

/**
 * GroupController
 *
 * Controlador para periodos y grupos.
 */
class GroupController
{
    private PeriodModel $periodModel;
    private GroupModel $groupModel;

    public function __construct()
    {
        $this->periodModel = new PeriodModel();
        $this->groupModel  = new GroupModel();
    }

    /**
     * Muestra periodos y grupos del periodo seleccionado.
     */
    public function index(): void
    {
        $periods = $this->periodModel->getAll();

        if (empty($periods)) {
            $selectedPeriod = null;
            $groups         = [];
        } else {
            // Tomar el id_periodo desde GET o el primero de la lista
            $idPeriodo = isset($_GET['id_periodo'])
                ? (string)$_GET['id_periodo']
                : (string)$periods[0]['id_periodo'];

            $selectedPeriod = $this->periodModel->getById($idPeriodo);
            if ($selectedPeriod === null) {
                // Si el periodo no existe, usar el primero
                $selectedPeriod = $periods[0];
                $idPeriodo      = (string)$selectedPeriod['id_periodo'];
            }

            $groups = $this->groupModel->getByPeriod($idPeriodo);
        }

        $pageTitle    = 'Periodos y grupos';
        $pageSubtitle = 'Consulta de periodos lectivos y grupos por semestre';

        require __DIR__ . '/../views/layout/header.php';
        require __DIR__ . '/../views/groups/index.php';
        require __DIR__ . '/../views/layout/footer.php';
    }

    /**
     * Muestra el horario de un grupo.
     */
    public function schedule(): void
    {
        $idGrupo = isset($_GET['id_grupo']) ? (int)$_GET['id_grupo'] : 0;

        if ($idGrupo <= 0) {
            $error = 'ID de grupo inválido.';
            header('Location: index.php?m=grupos&a=index&error=' . urlencode($error));
            exit;
        }

        $groupInfo = $this->groupModel->getGroupWithPeriod($idGrupo);

        if ($groupInfo === null) {
            $error = 'El grupo especificado no existe.';
            header('Location: index.php?m=grupos&a=index&error=' . urlencode($error));
            exit;
        }

        $schedule = $this->groupModel->getSchedule($idGrupo);
        $blocks   = $this->groupModel->getAllBlocks();

        $pageTitle    = 'Horario de grupo';
        $pageSubtitle = 'Consulta del horario del grupo seleccionado';

        require __DIR__ . '/../views/layout/header.php';
        require __DIR__ . '/../views/groups/schedule.php';
        require __DIR__ . '/../views/layout/footer.php';
    }
}
