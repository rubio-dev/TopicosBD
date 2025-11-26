/* ============================================================
   03_procedimientos.sql
   Procedimientos almacenados (MariaDB)
   ============================================================ */

USE control_horarios;

DELIMITER $$

CREATE PROCEDURE sp_crear_carga_automatica(
    IN p_id_alumno  CHAR(8),
    IN p_id_periodo CHAR(6),
    IN p_id_grupo   INT
)
BEGIN
    DECLARE v_id_carga   INT;
    DECLARE v_faltantes  INT DEFAULT 0;

    /* Validación de existencia de periodo */
    IF NOT EXISTS (
        SELECT 1 FROM periodo_lectivo
        WHERE id_periodo = p_id_periodo
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El periodo no existe';
    END IF;

    /* Validación de pertenencia del grupo al periodo */
    IF NOT EXISTS (
        SELECT 1 FROM grupo
        WHERE id_grupo = p_id_grupo
          AND id_periodo = p_id_periodo
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El grupo no pertenece al periodo indicado';
    END IF;

    /* Validación de carga previa del alumno en ese periodo */
    IF EXISTS (
        SELECT 1 FROM carga_academica
        WHERE id_alumno = p_id_alumno
          AND id_periodo = p_id_periodo
          AND estatus = 'A'
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El alumno ya tiene una carga activa en este periodo';
    END IF;

    /* Validación de prerequisitos */
    SELECT COUNT(*) INTO v_faltantes
    FROM materia_prerequisito mp
    JOIN grupo_materia gm
         ON gm.clave_materia = mp.clave_materia
    WHERE gm.id_grupo = p_id_grupo
      AND NOT EXISTS (
          SELECT 1
          FROM alumno_materia am
          WHERE am.id_alumno = p_id_alumno
            AND am.clave_materia = mp.clave_materia_prereq
            AND am.estatus = 'A'
      );

    IF v_faltantes > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El alumno no cumple con los prerequisitos de alguna materia';
    END IF;

    /* Creación de carga y detalle */

    START TRANSACTION;

    INSERT INTO carga_academica (id_alumno, id_periodo)
    VALUES (p_id_alumno, p_id_periodo);

    SET v_id_carga = LAST_INSERT_ID();

    INSERT INTO carga_detalle (id_carga, id_grupo_materia)
    SELECT v_id_carga, gm.id_grupo_materia
    FROM grupo_materia gm
    WHERE gm.id_grupo = p_id_grupo;

    INSERT INTO alumno_materia (id_alumno, clave_materia, id_periodo,
                                calificacion, tipo_acred, estatus)
    SELECT p_id_alumno, gm.clave_materia, p_id_periodo,
           NULL, NULL, 'C'
    FROM grupo_materia gm
    WHERE gm.id_grupo = p_id_grupo;

    COMMIT;
END$$

DELIMITER ;
