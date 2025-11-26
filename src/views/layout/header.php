<?php
declare(strict_types=1);

/**
 * Layout principal - encabezado
 *
 * Variables esperadas (opcionales):
 *  - $pageTitle    : Título de la página
 *  - $pageSubtitle : Subtítulo o descripción corta
 */

if (!isset($pageTitle)) {
    $pageTitle = 'TopicosBD';
}
if (!isset($pageSubtitle)) {
    $pageSubtitle = '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> · TopicosBD</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/topicosbd.css">
    <!-- JS global del proyecto -->
    <script src="assets/js/app.js" defer></script>
</head>
<body class="tb-body">

<header class="tb-header">
    <div class="tb-header-left">
        <span class="tb-logo">TB</span>
        <div>
            <h1 class="tb-title">TopicosBD</h1>
            <p class="tb-subtitle">
                <?= htmlspecialchars(
                    $pageSubtitle !== '' ? $pageSubtitle : 'Control de horarios y cargas académicas',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>
        </div>
    </div>
    <div class="tb-header-right">
        <nav>
            <a href="index.php" class="tb-nav-link">Inicio</a>
            <a href="index.php?m=alumnos&a=index" class="tb-nav-link">Alumnos</a>
            <a href="index.php?m=grupos&a=index" class="tb-nav-link">Grupos</a>
            <a href="index.php?m=cargas&a=index" class="tb-nav-link">Cargas</a>
            <a href="index.php?m=historial&a=index" class="tb-nav-link">Historial</a>
        </nav>
    </div>
</header>

<main class="tb-main">
