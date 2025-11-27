/* ============================================================
   02_datos_iniciales.sql
   Inserción de datos de ejemplo y catálogos (Optimizado)
   ============================================================ */

USE control_horarios;

/* Desactivar checks de llaves foráneas para limpiar tablas */
SET FOREIGN_KEY_CHECKS = 0;

/* Limpiar tablas (TRUNCATE) para reiniciar datos */
TRUNCATE TABLE carga_detalle;
TRUNCATE TABLE carga_academica;
TRUNCATE TABLE grupo_materia;
TRUNCATE TABLE grupo;
TRUNCATE TABLE alumno_materia;
TRUNCATE TABLE alumno;
TRUNCATE TABLE bloque_horario;
TRUNCATE TABLE materia_prerequisito;
TRUNCATE TABLE materia;
TRUNCATE TABLE periodo_lectivo;
TRUNCATE TABLE semestre;

/* Reactivar checks */
SET FOREIGN_KEY_CHECKS = 1;

/* Iniciar transacción para atomicidad */
START TRANSACTION;

/* ============================
   1. Catálogos Básicos (Semestres, Periodos, Materias)
   ============================ */

INSERT INTO semestre (semestre) VALUES 
(1), (3), (5);

INSERT INTO periodo_lectivo (id_periodo, anio, ciclo, descripcion, fecha_inicio, fecha_fin) VALUES
('202302', 2023, '02', 'AGO-DIC 2023', NULL, NULL),
('202402', 2024, '02', 'AGO-DIC 2024', NULL, NULL),
('202502', 2025, '02', 'AGO-DIC 2025', NULL, NULL);

INSERT INTO materia (clave_materia, nombre, semestre, creditos) VALUES
/* Semestre 1 */
('A', 'Matematicas I',      1, 5),
('C', 'Historia',           1, 4),
('F', 'Programacion I',     1, 5),
('J', 'Musica',             1, 3),
/* Semestre 3 */
('B', 'Matematicas II',     3, 5),
('D', 'Etica',              3, 3),
('G', 'Programacion II',    3, 5),
('H', 'Dibujo',             3, 3),
/* Semestre 5 */
('E', 'Estadistica',        5, 5),
('K', 'Taller de lectura',  5, 3),
('L', 'Base de datos',      5, 5),
('I', 'Diseno artistico',   5, 3);

INSERT INTO materia_prerequisito (clave_materia, clave_materia_prereq) VALUES
('B','A'),
('G','F'),
('E','B'),
('L','G');

/* Bloques Horarios (Expandidos para Paquetes A, B, C) */
INSERT INTO bloque_horario (id_bloque, hora_inicio, hora_fin, etiqueta) VALUES
(1, '08:00', '09:00', '08-09'),
(2, '09:00', '10:00', '09-10'),
(3, '10:00', '11:00', '10-11'),
(4, '11:00', '12:00', '11-12'),
(5, '12:00', '13:00', '12-13'),
(6, '13:00', '14:00', '13-14');

/* ============================
   2. Alumnos
   ============================ */

INSERT INTO alumno (id_alumno, anio_ingreso, ciclo_ingreso, nombre, semestre_actual) VALUES
/* Semestre 1 (Ingreso 2025-02) */
('25020001', 2025, '02', 'Carlos Lopez',       1),
('25020002', 2025, '02', 'Ana Ramirez',        1),
('25020003', 2025, '02', 'Luis Sanchez',       1),
('25020004', 2025, '02', 'Maria Torres',       1),
('25020005', 2025, '02', 'Jorge Fernandez',    1),
('25020006', 2025, '02', 'Lucia Hernandez',    1),
('25020007', 2025, '02', 'Pedro Garcia',       1),
('25020008', 2025, '02', 'Elena Morales',      1),
('25020009', 2025, '02', 'Diego Cruz',         1),
('25020010', 2025, '02', 'Sara Vega',          1),
/* Semestre 3 (Ingreso 2024-02) */
('24020001', 2024, '02', 'Andres Navarro',     3),
('24020002', 2024, '02', 'Patricia Flores',    3),
('24020003', 2024, '02', 'Ruben Castro',       3),
('24020004', 2024, '02', 'Monica Delgado',     3),
('24020005', 2024, '02', 'Hector Rios',        3),
('24020006', 2024, '02', 'Daniela Luna',       3),
('24020007', 2024, '02', 'Sergio Campos',      3),
('24020008', 2024, '02', 'Claudia Guzman',     3),
('24020009', 2024, '02', 'Ivan Mendoza',       3),
('24020010', 2024, '02', 'Alejandra Soto',     3),
/* Semestre 5 (Ingreso 2023-02) */
('23020001', 2023, '02', 'Fernando Ortiz',     5),
('23020002', 2023, '02', 'Gabriela Silva',     5),
('23020003', 2023, '02', 'Rafael Pineda',      5),
('23020004', 2023, '02', 'Veronica Reyes',     5),
('23020005', 2023, '02', 'Eduardo Aguilar',    5),
('23020006', 2023, '02', 'Paola Carrillo',     5),
('23020007', 2023, '02', 'Miguel Dominguez',   5),
('23020008', 2023, '02', 'Liliana Bravo',      5),
('23020009', 2023, '02', 'Ricardo Estrada',    5),
('23020010', 2023, '02', 'Adriana Nunez',      5);

/* ============================
   3. Historial Académico (Calificaciones)
   ============================ */

/* Historial para alumnos de 3er semestre (Aprobaron 1ro) */
INSERT INTO alumno_materia (id_alumno, clave_materia, id_periodo, calificacion, tipo_acred, estatus)
SELECT id_alumno, m.clave_materia, '202402', 
       FLOOR(80 + (RAND() * 20)), -- Calificación aleatoria entre 80 y 100
       1, 'A'
FROM alumno a
JOIN materia m ON m.semestre = 1
WHERE a.semestre_actual = 3;

/* Historial para alumnos de 5to semestre (Aprobaron 1ro y 3ro) */
-- 1er semestre (en 2023)
INSERT INTO alumno_materia (id_alumno, clave_materia, id_periodo, calificacion, tipo_acred, estatus)
SELECT id_alumno, m.clave_materia, '202302', 
       FLOOR(80 + (RAND() * 20)), 
       1, 'A'
FROM alumno a
JOIN materia m ON m.semestre = 1
WHERE a.semestre_actual = 5;

-- 3er semestre (en 2024)
INSERT INTO alumno_materia (id_alumno, clave_materia, id_periodo, calificacion, tipo_acred, estatus)
SELECT id_alumno, m.clave_materia, '202402', 
       FLOOR(80 + (RAND() * 20)), 
       1, 'A'
FROM alumno a
JOIN materia m ON m.semestre = 3
WHERE a.semestre_actual = 5;

/* ============================
   4. Grupos y Horarios (Paquetes A, B, C)
   ============================ */

/* Grupos */
INSERT INTO grupo (id_periodo, semestre, paquete, letra_grupo) VALUES
/* Semestre 1 */
('202502', 1, '1A', 'A'),
('202502', 1, '1B', 'B'),
('202502', 1, '1C', 'C'),
/* Semestre 3 */
('202502', 3, '3A', 'A'),
('202502', 3, '3B', 'B'),
('202502', 3, '3C', 'C'),
/* Semestre 5 */
('202502', 5, '5A', 'A'),
('202502', 5, '5B', 'B'),
('202502', 5, '5C', 'C');

/* Horarios: Paquete A (Normal: 8-12) */
INSERT INTO grupo_materia (id_grupo, clave_materia, id_bloque_lun, id_bloque_mar, id_bloque_mie, id_bloque_jue, id_bloque_vie)
SELECT g.id_grupo, m.clave_materia,
    -- Lunes
    CASE m.clave_materia 
        WHEN 'A' THEN 1 WHEN 'C' THEN 2 WHEN 'F' THEN 3 WHEN 'J' THEN 4 
        WHEN 'B' THEN 1 WHEN 'D' THEN 2 WHEN 'G' THEN 3 WHEN 'H' THEN 4 
        WHEN 'E' THEN 1 WHEN 'K' THEN 2 WHEN 'L' THEN 3 WHEN 'I' THEN 4 
    END,
    -- Martes
    CASE m.clave_materia 
        WHEN 'A' THEN 1 WHEN 'C' THEN 2 WHEN 'F' THEN 3 WHEN 'J' THEN 4 
        WHEN 'B' THEN 1 WHEN 'D' THEN 2 WHEN 'G' THEN 3 WHEN 'H' THEN 4 
        WHEN 'E' THEN 1 WHEN 'K' THEN 2 WHEN 'L' THEN 3 WHEN 'I' THEN 4 
    END,
    -- Miercoles
    CASE m.clave_materia 
        WHEN 'A' THEN 1 WHEN 'C' THEN 2 WHEN 'F' THEN 3 WHEN 'J' THEN 4 
        WHEN 'B' THEN 1 WHEN 'D' THEN 2 WHEN 'G' THEN 3 WHEN 'H' THEN 4 
        WHEN 'E' THEN 1 WHEN 'K' THEN 2 WHEN 'L' THEN 3 WHEN 'I' THEN 4 
    END,
    -- Jueves
    CASE m.clave_materia 
        WHEN 'A' THEN 1 WHEN 'C' THEN 2 WHEN 'F' THEN 3 WHEN 'J' THEN 4 
        WHEN 'B' THEN 1 WHEN 'D' THEN 2 WHEN 'G' THEN 3 WHEN 'H' THEN 4 
        WHEN 'E' THEN 1 WHEN 'K' THEN 2 WHEN 'L' THEN 3 WHEN 'I' THEN 4 
    END,
    -- Viernes
    CASE m.clave_materia 
        WHEN 'A' THEN 1 WHEN 'C' THEN 2 WHEN 'F' THEN 3 WHEN 'J' THEN 4 
        WHEN 'B' THEN 1 WHEN 'D' THEN 2 WHEN 'G' THEN 3 WHEN 'H' THEN 4 
        WHEN 'E' THEN 1 WHEN 'K' THEN 2 WHEN 'L' THEN 3 WHEN 'I' THEN 4 
    END
FROM grupo g
JOIN materia m ON m.semestre = g.semestre
WHERE g.paquete LIKE '%A';

/* Horarios: Paquete B (Shift +1: 9-13) */
INSERT INTO grupo_materia (id_grupo, clave_materia, id_bloque_lun, id_bloque_mar, id_bloque_mie, id_bloque_jue, id_bloque_vie)
SELECT g.id_grupo, m.clave_materia,
    -- Lunes (+1 bloque)
    CASE m.clave_materia 
        WHEN 'A' THEN 2 WHEN 'C' THEN 3 WHEN 'F' THEN 4 WHEN 'J' THEN 5 
        WHEN 'B' THEN 2 WHEN 'D' THEN 3 WHEN 'G' THEN 4 WHEN 'H' THEN 5 
        WHEN 'E' THEN 2 WHEN 'K' THEN 3 WHEN 'L' THEN 4 WHEN 'I' THEN 5 
    END,
    -- Martes
    CASE m.clave_materia 
        WHEN 'A' THEN 2 WHEN 'C' THEN 3 WHEN 'F' THEN 4 WHEN 'J' THEN 5 
        WHEN 'B' THEN 2 WHEN 'D' THEN 3 WHEN 'G' THEN 4 WHEN 'H' THEN 5 
        WHEN 'E' THEN 2 WHEN 'K' THEN 3 WHEN 'L' THEN 4 WHEN 'I' THEN 5 
    END,
    -- Miercoles
    CASE m.clave_materia 
        WHEN 'A' THEN 2 WHEN 'C' THEN 3 WHEN 'F' THEN 4 WHEN 'J' THEN 5 
        WHEN 'B' THEN 2 WHEN 'D' THEN 3 WHEN 'G' THEN 4 WHEN 'H' THEN 5 
        WHEN 'E' THEN 2 WHEN 'K' THEN 3 WHEN 'L' THEN 4 WHEN 'I' THEN 5 
    END,
    -- Jueves
    CASE m.clave_materia 
        WHEN 'A' THEN 2 WHEN 'C' THEN 3 WHEN 'F' THEN 4 WHEN 'J' THEN 5 
        WHEN 'B' THEN 2 WHEN 'D' THEN 3 WHEN 'G' THEN 4 WHEN 'H' THEN 5 
        WHEN 'E' THEN 2 WHEN 'K' THEN 3 WHEN 'L' THEN 4 WHEN 'I' THEN 5 
    END,
    -- Viernes
    CASE m.clave_materia 
        WHEN 'A' THEN 2 WHEN 'C' THEN 3 WHEN 'F' THEN 4 WHEN 'J' THEN 5 
        WHEN 'B' THEN 2 WHEN 'D' THEN 3 WHEN 'G' THEN 4 WHEN 'H' THEN 5 
        WHEN 'E' THEN 2 WHEN 'K' THEN 3 WHEN 'L' THEN 4 WHEN 'I' THEN 5 
    END
FROM grupo g
JOIN materia m ON m.semestre = g.semestre
WHERE g.paquete LIKE '%B';

/* Horarios: Paquete C (Shift +2: 10-14) */
INSERT INTO grupo_materia (id_grupo, clave_materia, id_bloque_lun, id_bloque_mar, id_bloque_mie, id_bloque_jue, id_bloque_vie)
SELECT g.id_grupo, m.clave_materia,
    -- Lunes (+2 bloques)
    CASE m.clave_materia 
        WHEN 'A' THEN 3 WHEN 'C' THEN 4 WHEN 'F' THEN 5 WHEN 'J' THEN 6 
        WHEN 'B' THEN 3 WHEN 'D' THEN 4 WHEN 'G' THEN 5 WHEN 'H' THEN 6 
        WHEN 'E' THEN 3 WHEN 'K' THEN 4 WHEN 'L' THEN 5 WHEN 'I' THEN 6 
    END,
    -- Martes
    CASE m.clave_materia 
        WHEN 'A' THEN 3 WHEN 'C' THEN 4 WHEN 'F' THEN 5 WHEN 'J' THEN 6 
        WHEN 'B' THEN 3 WHEN 'D' THEN 4 WHEN 'G' THEN 5 WHEN 'H' THEN 6 
        WHEN 'E' THEN 3 WHEN 'K' THEN 4 WHEN 'L' THEN 5 WHEN 'I' THEN 6 
    END,
    -- Miercoles
    CASE m.clave_materia 
        WHEN 'A' THEN 3 WHEN 'C' THEN 4 WHEN 'F' THEN 5 WHEN 'J' THEN 6 
        WHEN 'B' THEN 3 WHEN 'D' THEN 4 WHEN 'G' THEN 5 WHEN 'H' THEN 6 
        WHEN 'E' THEN 3 WHEN 'K' THEN 4 WHEN 'L' THEN 5 WHEN 'I' THEN 6 
    END,
    -- Jueves
    CASE m.clave_materia 
        WHEN 'A' THEN 3 WHEN 'C' THEN 4 WHEN 'F' THEN 5 WHEN 'J' THEN 6 
        WHEN 'B' THEN 3 WHEN 'D' THEN 4 WHEN 'G' THEN 5 WHEN 'H' THEN 6 
        WHEN 'E' THEN 3 WHEN 'K' THEN 4 WHEN 'L' THEN 5 WHEN 'I' THEN 6 
    END,
    -- Viernes
    CASE m.clave_materia 
        WHEN 'A' THEN 3 WHEN 'C' THEN 4 WHEN 'F' THEN 5 WHEN 'J' THEN 6 
        WHEN 'B' THEN 3 WHEN 'D' THEN 4 WHEN 'G' THEN 5 WHEN 'H' THEN 6 
        WHEN 'E' THEN 3 WHEN 'K' THEN 4 WHEN 'L' THEN 5 WHEN 'I' THEN 6 
    END
FROM grupo g
JOIN materia m ON m.semestre = g.semestre
WHERE g.paquete LIKE '%C';

COMMIT;
