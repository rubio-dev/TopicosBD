<?php
/** @var string      $pageTitle */
/** @var array       $students */
/** @var array       $offer */
/** @var array       $periods */
/** @var array       $currentEnrollment */
/** @var string      $baseUrl */
/** @var string      $controlNumber */
/** @var string      $periodCode */
/** @var string|null $message */
/** @var string|null $messageType */
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Josefin+Sans:wght@500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/style.css">
</head>
<body>

<header>
    <div class="header-title">
        <h1>TopicosBD · Horario Studio</h1>
        <span>Simulador de servicios escolares</span>
    </div>
    <div class="header-badge">
        Proyecto final · Semestres 1 · 3 · 5
    </div>
</header>

<main>
    <?php require __DIR__ . '/../dashboard/Index.php'; ?>
</main>

<script src="<?php echo $baseUrl; ?>/js/app.js"></script>
</body>
</html>
