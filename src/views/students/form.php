<?php
declare(strict_types=1);

/**
 * Vista: formulario de alumnos (alta / edición)
 *
 * Variables esperadas:
 *  - $student : array con datos del alumno
 *  - $errors  : array de mensajes de error (puede estar vacío)
 *  - $isEdit  : bool, true si es edición, false si es alta
 */

$idAlumno       = $student['id_alumno']       ?? '';
$anioIngreso    = $student['anio_ingreso']    ?? '';
$cicloIngreso   = $student['ciclo_ingreso']   ?? '02';
$nombre         = $student['nombre']          ?? '';
$semestreActual = $student['semestre_actual'] ?? 1;

$actionUrl = $isEdit
    ? 'index.php?m=alumnos&a=update'
    : 'index.php?m=alumnos&a=store';

$tituloFormulario = $isEdit ? 'Editar alumno' : 'Nuevo alumno';
?>
<section class="tb-section">
    <h2 class="tb-section-title"><?= htmlspecialchars($tituloFormulario, ENT_QUOTES, 'UTF-8') ?></h2>

    <?php if (!empty($errors)): ?>
        <div class="tb-alert tb-alert-error">
            <ul class="tb-error-list">
                <?php foreach ($errors as $mensajeError): ?>
                    <li><?= htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= htmlspecialchars($actionUrl, ENT_QUOTES, 'UTF-8') ?>" method="post" class="tb-form">
        <?php if ($isEdit && $idAlumno !== ''): ?>
            <div class="tb-form-row">
                <label for="id_alumno" class="tb-label">ID alumno</label>
                <input type="text"
                       id="id_alumno"
                       name="id_alumno"
                       class="tb-input"
                       value="<?= htmlspecialchars($idAlumno, ENT_QUOTES, 'UTF-8') ?>"
                       readonly>
            </div>
        <?php endif; ?>

        <?php if (!$isEdit): ?>
            <div class="tb-form-row">
                <label for="anio_ingreso" class="tb-label">Año de ingreso</label>
                <input type="number"
                       id="anio_ingreso"
                       name="anio_ingreso"
                       class="tb-input"
                       min="2000"
                       max="2100"
                       value="<?= htmlspecialchars((string)$anioIngreso, ENT_QUOTES, 'UTF-8') ?>"
                       required>
            </div>

            <div class="tb-form-row">
                <label for="ciclo_ingreso" class="tb-label">Ciclo de ingreso</label>
                <select id="ciclo_ingreso" name="ciclo_ingreso" class="tb-input" required>
                    <option value="">-- Selecciona --</option>
                    <option value="01" <?= $cicloIngreso === '01' ? 'selected' : '' ?>>01</option>
                    <option value="02" <?= $cicloIngreso === '02' ? 'selected' : '' ?>>02</option>
                </select>
            </div>
        <?php else: ?>
            <div class="tb-form-row">
                <label class="tb-label">Año y ciclo de ingreso</label>
                <div class="tb-inline-fields">
                    <input type="text"
                           class="tb-input tb-input-small"
                           value="<?= htmlspecialchars((string)$anioIngreso, ENT_QUOTES, 'UTF-8') ?>"
                           readonly>
                    <input type="text"
                           class="tb-input tb-input-small"
                           value="<?= htmlspecialchars((string)$cicloIngreso, ENT_QUOTES, 'UTF-8') ?>"
                           readonly>
                </div>
            </div>
        <?php endif; ?>

        <div class="tb-form-row">
            <label for="nombre" class="tb-label">Nombre</label>
            <input type="text"
                   id="nombre"
                   name="nombre"
                   class="tb-input"
                   maxlength="100"
                   value="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>"
                   required>
        </div>

        <div class="tb-form-row">
            <label for="semestre_actual" class="tb-label">Semestre actual</label>
            <select id="semestre_actual" name="semestre_actual" class="tb-input" required>
                <option value="">-- Selecciona --</option>
                <option value="1" <?= (int)$semestreActual === 1 ? 'selected' : '' ?>>1</option>
                <option value="3" <?= (int)$semestreActual === 3 ? 'selected' : '' ?>>3</option>
                <option value="5" <?= (int)$semestreActual === 5 ? 'selected' : '' ?>>5</option>
            </select>
        </div>

        <div class="tb-form-actions">
            <button type="submit" class="tb-btn">
                <?= $isEdit ? 'Guardar cambios' : 'Crear alumno' ?>
            </button>
            <a href="index.php?m=alumnos&a=index" class="tb-btn tb-btn-outline">
                Cancelar
            </a>
        </div>
    </form>
</section>
