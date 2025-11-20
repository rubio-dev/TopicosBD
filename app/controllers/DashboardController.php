<?php
declare(strict_types=1);

require_once __DIR__ . '/../services/ScheduleService.php';
require_once __DIR__ . '/../config/Config.php';

class DashboardController
{
    private ScheduleService $service;

    public function __construct()
    {
        $this->service = new ScheduleService();
    }

    public function index(): void
    {
        // Primero leer student/period de POST (inscripción) o de GET (filtros)
        $controlNumber = $_POST['student'] ?? ($_GET['student'] ?? 'E001');
        $periodCode    = $_POST['period']  ?? ($_GET['period']  ?? 'A-D 01');

        $message     = null;
        $messageType = null; // 'success' | 'error'

        // Si viene POST con grupos seleccionados: intentar inscribir
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $selected = $_POST['groups'] ?? [];
            if (!is_array($selected)) {
                $selected = [];
            }

            $result = $this->service->registerSelection($controlNumber, $periodCode, $selected);

            $message     = implode(' ', $result['messages']);
            $messageType = $result['success'] ? 'success' : 'error';
        }

        $students          = $this->service->getStudentList();
        $offer             = $this->service->getScheduleOffer($controlNumber, $periodCode);
        $periods           = $this->service->getPeriodList();
        $currentEnrollment = $this->service->getCurrentEnrollment($controlNumber, $periodCode);

        $pageTitle = 'TopicosBD · Simulador de horarios';
        $baseUrl   = Config::BASE_URL;

        require __DIR__ . '/../views/layout/MainLayout.php';
    }
}
