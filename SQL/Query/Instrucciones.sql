USE proyecto_horarios;

-- =========================================================
-- 1. CATÁLOGOS BÁSICOS (Materias, Retícula, Periodos, Grupos)
-- =========================================================

INSERT INTO materia (clave_materia, nombre, semestre_sugerido, es_obligatoria) VALUES
('A','Matemáticas I',      1, 1),
('B','Matemáticas II',     3, 1),
('C','Historia',           1, 1),
('D','Ética',              3, 1),
('E','Estadística',        5, 1),
('F','Programación I',     1, 1),
('G','Programación II',    3, 1),
('H','Dibujo',             3, 1),
('I','Diseño artístico',   5, 1),
('J','Música',             1, 1),
('K','Taller de lectura',  5, 1),
('L','Base de datos',      5, 1);

INSERT INTO reticula (clave_materia, clave_materia_prerrequisito) VALUES
('B','A'),
('E','B'),
('G','F'),
('I','H');

INSERT INTO periodo (clave_periodo, descripcion) VALUES
('E-J 01', 'Enero-Junio 2001, histórico'),
('A-D 01', 'Agosto-Diciembre 2001, actual');

SET @idPeriodoHist := (SELECT id_periodo FROM periodo WHERE clave_periodo = 'E-J 01');
SET @idPeriodoAct  := (SELECT id_periodo FROM periodo WHERE clave_periodo = 'A-D 01');

-- Oferta de grupos (misma idea que en SQL Server)
INSERT INTO grupo
(id_periodo, paquete, clave_materia, grupo, hor_lun, hor_mar, hor_mie, hor_jue, hor_vie) VALUES
-- Paquete 1: semestre 1
(@idPeriodoAct, '1A', 'A', 'A', '08-09','08-09','08-09','08-09','08-09'),
(@idPeriodoAct, '1A', 'C', 'A', '09-10','09-10','09-10','09-10','09-10'),
(@idPeriodoAct, '1A', 'F', 'A', '10-11','10-11','10-11','10-11','10-11'),
(@idPeriodoAct, '1A', 'J', 'A', '11-12','11-12','11-12','11-12','11-12'),

(@idPeriodoAct, '1B', 'A', 'B', '11-12','11-12','11-12','11-12','11-12'),
(@idPeriodoAct, '1B', 'C', 'B', '08-09','08-09','08-09','08-09','08-09'),
(@idPeriodoAct, '1B', 'F', 'B', '09-10','09-10','09-10','09-10','09-10'),
(@idPeriodoAct, '1B', 'J', 'B', '10-11','10-11','10-11','10-11','10-11'),

(@idPeriodoAct, '1C', 'A', 'C', '10-11','10-11','10-11','10-11','10-11'),
(@idPeriodoAct, '1C', 'C', 'C', '11-12','11-12','11-12','11-12','11-12'),
(@idPeriodoAct, '1C', 'F', 'C', '08-09','08-09','08-09','08-09','08-09'),
(@idPeriodoAct, '1C', 'J', 'C', '09-10','09-10','09-10','09-10','09-10'),

-- Paquete 2: semestre 3
(@idPeriodoAct, '2A', 'B', 'A', '08-09','08-09','08-09','08-09','08-09'),
(@idPeriodoAct, '2A', 'D', 'A', '09-10','09-10','09-10','09-10','09-10'),
(@idPeriodoAct, '2A', 'G', 'A', '10-11','10-11','10-11','10-11','10-11'),
(@idPeriodoAct, '2A', 'H', 'A', '11-12','11-12','11-12','11-12','11-12'),

(@idPeriodoAct, '2B', 'B', 'B', '10-11','10-11','10-11','10-11','10-11'),
(@idPeriodoAct, '2B', 'D', 'B', '11-12','11-12','11-12','11-12','11-12'),
(@idPeriodoAct, '2B', 'G', 'B', '08-09','08-09','08-09','08-09','08-09'),
(@idPeriodoAct, '2B', 'H', 'B', '09-10','09-10','09-10','09-10','09-10'),

-- Paquete 3: semestre 5
(@idPeriodoAct, '3A', 'E', 'A', '08-09','08-09','08-09','08-09','08-09'),
(@idPeriodoAct, '3A', 'K', 'A', '09-10','09-10','09-10','09-10','09-10'),
(@idPeriodoAct, '3A', 'L', 'A', '10-11','10-11','10-11','10-11','10-11'),
(@idPeriodoAct, '3A', 'I', 'A', '11-12','11-12','11-12','11-12','11-12');

-- =========================================================
-- 2. ALUMNOS (30 alumnos: semestres 1, 3 y 5)
-- =========================================================

INSERT INTO alumno (num_control, nombre, semestre_actual, estatus) VALUES
('E001','Alumno 001',1,'A'),
('E002','Alumno 002',1,'A'),
('E003','Alumno 003',1,'A'),
('E004','Alumno 004',1,'A'),
('E005','Alumno 005',1,'A'),
('E006','Alumno 006',1,'A'),
('E007','Alumno 007',1,'A'),
('E008','Alumno 008',1,'A'),
('E009','Alumno 009',1,'A'),
('E010','Alumno 010',1,'A'),
('E011','Alumno 011',3,'A'),
('E012','Alumno 012',3,'A'),
('E013','Alumno 013',3,'A'),
('E014','Alumno 014',3,'A'),
('E015','Alumno 015',3,'A'),
('E016','Alumno 016',3,'A'),
('E017','Alumno 017',3,'A'),
('E018','Alumno 018',3,'A'),
('E019','Alumno 019',3,'A'),
('E020','Alumno 020',3,'A'),
('E021','Alumno 021',5,'A'),
('E022','Alumno 022',5,'A'),
('E023','Alumno 023',5,'A'),
('E024','Alumno 024',5,'A'),
('E025','Alumno 025',5,'A'),
('E026','Alumno 026',5,'A'),
('E027','Alumno 027',5,'A'),
('E028','Alumno 028',5,'A'),
('E029','Alumno 029',5,'A'),
('E030','Alumno 030',5,'A');

-- =========================================================
-- 3. CALIFICACIONES DE EJEMPLO PARA E001–E003
-- =========================================================

INSERT INTO calificacion (num_control, clave_materia, id_periodo, calificacion, tipo_acreditacion) VALUES
('E001','A', @idPeriodoHist,  0, 2),
('E001','C', @idPeriodoHist,100, 1),
('E001','F', @idPeriodoHist, 80, 2),
('E001','J', @idPeriodoHist,  0, 2),

('E002','A', @idPeriodoHist, 90, 1),
('E002','C', @idPeriodoHist,  0, 2),
('E002','F', @idPeriodoHist,  0, 2),
('E002','J', @idPeriodoHist,100, 1),

('E003','A', @idPeriodoHist, 80, 1),
('E003','C', @idPeriodoHist, 90, 2),
('E003','F', @idPeriodoHist,100, 1),
('E003','J', @idPeriodoHist, 85, 1);

-- =========================================================
-- 4. PRIORIDADES DE MATERIAS (ejemplo para E001)
-- =========================================================

INSERT INTO prioridad_alumno_materia (num_control, clave_materia, prioridad) VALUES
('E001','A',1),
('E001','J',1),
('E001','F',2),
('E001','C',2),
('E001','B',2),
('E001','G',2),
('E001','H',3),
('E001','E',3),
('E001','K',3),
('E001','L',3),
('E001','I',3);

-- =========================================================
-- 5. FUNCIONES EN MARIADB
-- =========================================================

DELIMITER $$

CREATE FUNCTION fn_es_calif_aprobatoria(
    p_calif TINYINT,
    p_tipo  TINYINT
)
RETURNS TINYINT(1)
DETERMINISTIC
BEGIN
    DECLARE v_res TINYINT(1) DEFAULT 0;

    IF p_calif >= 70 THEN
        SET v_res = 1;
    END IF;

    RETURN v_res;
END$$

CREATE FUNCTION fn_materia_aprobada(
    p_num_control   CHAR(5),
    p_clave_materia CHAR(1)
)
RETURNS TINYINT(1)
DETERMINISTIC
BEGIN
    DECLARE v_res TINYINT(1) DEFAULT 0;

    IF EXISTS (
        SELECT 1
        FROM calificacion c
        WHERE c.num_control   = p_num_control
          AND c.clave_materia = p_clave_materia
          AND fn_es_calif_aprobatoria(c.calificacion, c.tipo_acreditacion) = 1
    ) THEN
        SET v_res = 1;
    END IF;

    RETURN v_res;
END$$

CREATE FUNCTION fn_puede_cursar_materia(
    p_num_control   CHAR(5),
    p_clave_materia CHAR(1)
)
RETURNS TINYINT(1)
DETERMINISTIC
BEGIN
    DECLARE v_res TINYINT(1) DEFAULT 1;

    -- Si ya está aprobada, no se puede recursar
    IF fn_materia_aprobada(p_num_control, p_clave_materia) = 1 THEN
        SET v_res = 0;
        RETURN v_res;
    END IF;

    -- Validar prerrequisitos
    IF EXISTS (
        SELECT 1
        FROM reticula r
        WHERE r.clave_materia = p_clave_materia
          AND fn_materia_aprobada(p_num_control, r.clave_materia_prerrequisito) = 0
    ) THEN
        SET v_res = 0;
    END IF;

    RETURN v_res;
END$$

-- =========================================================
-- 6. PROCEDIMIENTO: OFERTA DE MATERIAS Y GRUPOS PARA UN ALUMNO
--    (aplica seriación y prioridad)
-- =========================================================

CREATE PROCEDURE sp_oferta_materias_alumno(
    IN p_num_control   CHAR(5),
    IN p_clave_periodo VARCHAR(10)
)
BEGIN
    DECLARE v_id_periodo INT;

    SELECT id_periodo
      INTO v_id_periodo
      FROM periodo
     WHERE clave_periodo = p_clave_periodo
     LIMIT 1;

    IF v_id_periodo IS NULL THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Periodo no encontrado';
    END IF;

    -- CTE disponible en MariaDB 10.2+; si tu versión es vieja puedes reemplazarlo por una subconsulta.
    WITH materias_posibles AS (
        SELECT
            m.clave_materia,
            m.nombre,
            m.semestre_sugerido,
            COALESCE(p.prioridad, 2) AS prioridad
        FROM materia m
        LEFT JOIN prioridad_alumno_materia p
               ON p.clave_materia = m.clave_materia
              AND p.num_control   = p_num_control
        WHERE fn_puede_cursar_materia(p_num_control, m.clave_materia) = 1
    )
    SELECT
        mp.clave_materia,
        mp.nombre,
        mp.semestre_sugerido,
        mp.prioridad,
        g.id_grupo,
        g.paquete,
        g.grupo,
        g.hor_lun,
        g.hor_mar,
        g.hor_mie,
        g.hor_jue,
        g.hor_vie
    FROM materias_posibles mp
    JOIN grupo g
      ON g.clave_materia = mp.clave_materia
     AND g.id_periodo    = v_id_periodo
    ORDER BY
        mp.prioridad,
        mp.semestre_sugerido,
        mp.clave_materia,
        g.paquete,
        g.grupo;
END$$

DELIMITER ;
