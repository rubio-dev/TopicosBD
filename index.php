<?php
declare(strict_types=1);

/**
 * TopicosBD — Punto de entrada único (front controller + dashboard)
 */

$modulo = $_GET['m'] ?? null;
$accion = $_GET['a'] ?? null;

if ($modulo !== null && $accion !== null) {
    // Enrutamiento por módulo
    if ($modulo === 'alumnos') {
        require_once __DIR__ . '/src/controllers/StudentController.php';
        $controller = new StudentController();
    } elseif ($modulo === 'grupos') {
        require_once __DIR__ . '/src/controllers/GroupController.php';
        $controller = new GroupController();
    } elseif ($modulo === 'cargas') {
        require_once __DIR__ . '/src/controllers/EnrollmentController.php';
        $controller = new EnrollmentController();
    } elseif ($modulo === 'historial') {
        require_once __DIR__ . '/src/controllers/HistoryController.php';
        $controller = new HistoryController();
    } else {
        // Otros módulos todavía no implementados: placeholder
        $moduloSeguro = htmlspecialchars($modulo, ENT_QUOTES, 'UTF-8');
        $accionSegura = htmlspecialchars($accion, ENT_QUOTES, 'UTF-8');
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>TopicosBD · Módulo en construcción</title>
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link rel="stylesheet" href="assets/css/topicosbd.css">
        </head>
        <body class="tb-body tb-body-simple">
        <div class="tb-placeholder">
            <h1>TopicosBD</h1>
            <p>
                El módulo <strong><?= $moduloSeguro ?></strong> y la acción
                <strong><?= $accionSegura ?></strong> aún están en construcción.
            </p>
            <a class="tb-btn" href="index.php">Volver al dashboard</a>
        </div>
        </body>
        </html>
        <?php
        exit;
    }

    if (!method_exists($controller, $accion)) {
        http_response_code(404);
        echo 'Acción no encontrada.';
        exit;
    }

    $controller->{$accion}();
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TopicosBD · Control de sistemas escolares</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/topicosbd.css">
</head>
<body class="tb-body">

<header class="tb-header">
    <div class="tb-header-left">
        <span class="tb-logo">TB</span>
        <div>
            <h1 class="tb-title">TopicosBD</h1>
            <p class="tb-subtitle">Control de horarios y cargas académicas</p>
        </div>
    </div>
    <div class="tb-header-right">
        <span class="tb-badge">Dashboard principal</span>
    </div>
</header>

<main class="tb-main">
    <section class="tb-section">
        <h2 class="tb-section-title">Módulos principales</h2>

        <div class="tb-cards-grid">

            <!-- ALUMNOS (Students) -->
            <article class="tb-card tb-card-pink">
                <div class="tb-card-header">
                    <h3>Alumnos</h3>
                    <span class="tb-card-tag">Gestión básica</span>
                </div>
                <p class="tb-card-text">
                    Alta, edición y consulta de alumnos.
                    Define semestre actual y ciclo de ingreso para preparar sus cargas académicas.
                </p>
                <div class="tb-card-actions">
                    <a href="?m=alumnos&a=index" class="tb-btn">Ver alumnos</a>
                    <a href="?m=alumnos&a=create" class="tb-btn tb-btn-outline">Nuevo alumno</a>
                </div>
            </article>

            <!-- PERIODOS Y GRUPOS -->
            <article class="tb-card tb-card-teal">
                <div class="tb-card-header">
                    <h3>Periodos y grupos</h3>
                    <span class="tb-card-tag">Oferta académica</span>
                </div>
                <p class="tb-card-text">
                    Administra periodos lectivos y grupos por semestre.
                    Consulta la oferta y organiza los grupos disponibles en cada periodo.
                </p>
                <div class="tb-card-actions">
                    <a href="?m=grupos&a=index" class="tb-btn">Periodos y grupos</a>
                    <a href="?m=grupos&a=index" class="tb-btn tb-btn-outline">Ver horarios</a>
                </div>
            </article>

            <!-- CARGAS ACADÉMICAS -->
            <article class="tb-card tb-card-peach">
                <div class="tb-card-header">
                    <h3>Cargas académicas</h3>
                    <span class="tb-card-tag">Asignación de grupo</span>
                </div>
                <p class="tb-card-text">
                    Selecciona alumno y periodo, genera su carga automática según el grupo
                    o ajusta materias de forma manual cuando sea necesario.
                </p>
                <div class="tb-card-actions">
                    <a href="?m=cargas&a=index" class="tb-btn">Administrar cargas</a>
                    <a href="?m=cargas&a=index" class="tb-btn tb-btn-outline">Buscar alumno</a>
                </div>
            </article>

            <!-- HISTORIAL / CALIFICACIONES -->
            <article class="tb-card tb-card-sand">
                <div class="tb-card-header">
                    <h3>Historial y calificaciones</h3>
                    <span class="tb-card-tag">Kardex</span>
                </div>
                <p class="tb-card-text">
                    Consulta el historial por periodo y registra calificaciones para actualizar
                    el estatus de las materias (aprobada o reprobada).
                </p>
                <div class="tb-card-actions">
                    <a href="?m=historial&a=index" class="tb-btn">Ver historial</a>
                    <a href="?m=historial&a=index" class="tb-btn tb-btn-outline">Capturar calificaciones</a>
                </div>
            </article>

        </div>
    </section>
</main>

<footer class="tb-footer">
    <span>TopicosBD · Proyecto de control de sistemas escolares</span>
</footer>

</body>
</html>
