/* ============================================================
   03_procedimientos.sql
   Procedimientos almacenados (MariaDB)
   ============================================================ */

USE control_horarios;

DELIMITER $$

DROP PROCEDURE IF EXISTS sp_crear_carga_automatica$$

/**
 * sp_crear_carga_automatica
 * 
 * Genera una carga académica automática para un alumno en un periodo,
 * asignando todas las materias de un grupo específico (paquete).
 * 
 * Validaciones:
 * 1. El periodo debe existir.
 * 2. El grupo debe pertenecer al periodo.
 * 3. El alumno no debe tener carga ACTIVA en ese periodo.
 * 4. El alumno debe cumplir prerequisitos de las materias del grupo.
 * 
 * Lógica:
 * - Si existe una carga CANCELADA previa, la reactiva y limpia sus detalles.
 * - Si no, crea una nueva carga.
 * - Inserta los detalles del grupo en carga_detalle.
 * - Inserta las materias en alumno_materia con estatus 'C' (Cursando).
 */
CREATE PROCEDURE sp_crear_carga_automatica(
    IN p_id_alumno  CHAR(8) COLLATE utf8mb4_general_ci,
    IN p_id_periodo CHAR(6) COLLATE utf8mb4_general_ci,
    IN p_id_grupo   INT
)
BEGIN
    DECLARE v_id_carga INT;

    /* Handler para manejo de errores y rollback de transacción */
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    /* 1. Validación de existencia de periodo */
    IF NOT EXISTS (
        SELECT 1 FROM periodo_lectivo
        WHERE id_periodo = p_id_periodo
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El periodo no existe';
    END IF;

    /* 2. Validación de pertenencia del grupo al periodo */
    IF NOT EXISTS (
        SELECT 1 FROM grupo
        WHERE id_grupo = p_id_grupo
          AND id_periodo = p_id_periodo
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El grupo no pertenece al periodo indicado';
    END IF;

    /* 3. Validación de carga previa ACTIVA del alumno en ese periodo */
    IF EXISTS (
        SELECT 1 FROM carga_academica
        WHERE id_alumno = p_id_alumno
          AND id_periodo = p_id_periodo
          AND estatus = 'A'
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El alumno ya tiene una carga activa en este periodo';
    END IF;

    /* 4. Validación de prerequisitos */
    /* Verifica si hay alguna materia en el grupo para la cual el alumno NO tiene el prerequisito aprobado */
    IF EXISTS (
        SELECT 1
        FROM materia_prerequisito mp
        JOIN grupo_materia gm
             ON gm.clave_materia = mp.clave_materia
        WHERE gm.id_grupo = p_id_grupo
          AND NOT EXISTS (
              SELECT 1
              FROM alumno_materia am
              WHERE am.id_alumno = p_id_alumno
                AND am.clave_materia = mp.clave_materia_prereq
                AND am.estatus = 'A' -- Debe tenerla Aprobada
          )
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El alumno no cumple con los prerequisitos de alguna materia del grupo';
    END IF;

    /* 5. Inicio de Transacción */
    START TRANSACTION;

    /* 6. Crear o Reactivar Carga */
    -- Buscamos si ya existe un registro de carga (aunque esté cancelado 'C')
    SELECT id_carga INTO v_id_carga
    FROM carga_academica
    WHERE id_alumno = p_id_alumno
      AND id_periodo = p_id_periodo
    LIMIT 1;

    IF v_id_carga IS NOT NULL THEN
        -- Si existe (estatus 'C'), la reactivamos a 'A'
        UPDATE carga_academica
        SET estatus = 'A', 
            fecha_alta = CURRENT_TIMESTAMP
        WHERE id_carga = v_id_carga;
        
        -- Aseguramos que no tenga detalles residuales (limpieza defensiva)
        DELETE FROM carga_detalle WHERE id_carga = v_id_carga;
    ELSE
        -- Si no existe, creamos una nueva
        INSERT INTO carga_academica (id_alumno, id_periodo, estatus)
        VALUES (p_id_alumno, p_id_periodo, 'A');
        
        SET v_id_carga = LAST_INSERT_ID();
    END IF;

    /* 7. Insertar Detalle de Carga (Materias del Grupo) */
    INSERT INTO carga_detalle (id_carga, id_grupo_materia)
    SELECT v_id_carga, gm.id_grupo_materia
    FROM grupo_materia gm
    WHERE gm.id_grupo = p_id_grupo;

    /* 8. Actualizar Historial (Alumno Materia) */
    -- Insertamos las materias en el historial como 'C' (Cursando)
    -- Usamos INSERT IGNORE o NOT EXISTS para evitar duplicados si ya existía un registro previo
    INSERT INTO alumno_materia (id_alumno, clave_materia, id_periodo, calificacion, tipo_acred, estatus)
    SELECT p_id_alumno, gm.clave_materia, p_id_periodo, NULL, NULL, 'C'
    FROM grupo_materia gm
    WHERE gm.id_grupo = p_id_grupo
      AND NOT EXISTS (
          SELECT 1 FROM alumno_materia am 
          WHERE am.id_alumno = p_id_alumno 
            AND am.clave_materia = gm.clave_materia 
            AND am.id_periodo = p_id_periodo
      );

    COMMIT;
END$$

DELIMITER ;
