/* ============================================================
   02_datos_iniciales.sql
   Inserción de datos de ejemplo y catálogos
   ============================================================ */

USE control_horarios;

/* ============================
   Semestres
   ============================ */

INSERT INTO semestre (semestre) VALUES (1),(3),(5);

/* ============================
   Periodo AGO-DIC 2025
   ============================ */

INSERT INTO periodo_lectivo (id_periodo, anio, ciclo, descripcion, fecha_inicio, fecha_fin)
VALUES ('202502', 2025, '02', 'AGO-DIC 2025', NULL, NULL);

/* ============================
   Materias semestre 1
   ============================ */

INSERT INTO materia (clave_materia, nombre, semestre, creditos) VALUES
('A', 'Matematicas I',      1, 5),
('C', 'Historia',           1, 4),
('F', 'Programacion I',     1, 5),
('J', 'Musica',             1, 3);

/* ============================
   Materias semestre 3
   ============================ */

INSERT INTO materia (clave_materia, nombre, semestre, creditos) VALUES
('B', 'Matematicas II',     3, 5),
('D', 'Etica',              3, 3),
('G', 'Programacion II',    3, 5),
('H', 'Dibujo',             3, 3);

/* ============================
   Materias semestre 5
   ============================ */

INSERT INTO materia (clave_materia, nombre, semestre, creditos) VALUES
('E', 'Estadistica',        5, 5),
('K', 'Taller de lectura',  5, 3),
('L', 'Base de datos',      5, 5),
('I', 'Diseno artistico',   5, 3);

/* ============================
   Prerrequisitos
   ============================ */

INSERT INTO materia_prerequisito (clave_materia, clave_materia_prereq) VALUES
('B','A'),
('G','F'),
('E','B'),
('L','G');

/* ============================
   30 alumnos de ejemplo
   ============================ */

INSERT INTO alumno (id_alumno, anio_ingreso, ciclo_ingreso, nombre, semestre_actual) VALUES
-- Semestre 1, ingreso 2025-02
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

-- Semestre 3, ingreso 2024-02
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

-- Semestre 5, ingreso 2023-02
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
   Bloques horarios
   ============================ */

INSERT INTO bloque_horario (id_bloque, hora_inicio, hora_fin, etiqueta) VALUES
(1, '08:00', '09:00', '08-09'),
(2, '09:00', '10:00', '09-10'),
(3, '10:00', '11:00', '10-11'),
(4, '11:00', '12:00', '11-12');

/* ============================
   Grupos por semestre para el periodo 202502
   ============================ */

INSERT INTO grupo (id_periodo, semestre, paquete, letra_grupo) VALUES
('202502', 1, '1A', 'A'),
('202502', 3, '3A', 'A'),
('202502', 5, '5A', 'A');

/* ============================
   Horarios de las materias de cada grupo
   ============================ */

-- Grupo de 1er semestre:
--  A -> bloque 1 (08-09)
--  C -> bloque 2 (09-10)
--  F -> bloque 3 (10-11)
--  J -> bloque 4 (11-12)
INSERT INTO grupo_materia (id_grupo, clave_materia,
                           id_bloque_lun, id_bloque_mar, id_bloque_mie, id_bloque_jue, id_bloque_vie)
SELECT 
    g.id_grupo,
    m.clave_materia,
    CASE m.clave_materia
        WHEN 'A' THEN 1
        WHEN 'C' THEN 2
        WHEN 'F' THEN 3
        WHEN 'J' THEN 4
    END AS id_bloque_lun,
    CASE m.clave_materia
        WHEN 'A' THEN 1
        WHEN 'C' THEN 2
        WHEN 'F' THEN 3
        WHEN 'J' THEN 4
    END AS id_bloque_mar,
    CASE m.clave_materia
        WHEN 'A' THEN 1
        WHEN 'C' THEN 2
        WHEN 'F' THEN 3
        WHEN 'J' THEN 4
    END AS id_bloque_mie,
    CASE m.clave_materia
        WHEN 'A' THEN 1
        WHEN 'C' THEN 2
        WHEN 'F' THEN 3
        WHEN 'J' THEN 4
    END AS id_bloque_jue,
    CASE m.clave_materia
        WHEN 'A' THEN 1
        WHEN 'C' THEN 2
        WHEN 'F' THEN 3
        WHEN 'J' THEN 4
    END AS id_bloque_vie
FROM grupo g
JOIN materia m ON m.semestre = 1
WHERE g.semestre = 1;

-- Grupo de 3er semestre:
--  B -> bloque 1
--  D -> bloque 2
--  G -> bloque 3
--  H -> bloque 4
INSERT INTO grupo_materia (id_grupo, clave_materia,
                           id_bloque_lun, id_bloque_mar, id_bloque_mie, id_bloque_jue, id_bloque_vie)
SELECT 
    g.id_grupo,
    m.clave_materia,
    CASE m.clave_materia
        WHEN 'B' THEN 1
        WHEN 'D' THEN 2
        WHEN 'G' THEN 3
        WHEN 'H' THEN 4
    END AS id_bloque_lun,
    CASE m.clave_materia
        WHEN 'B' THEN 1
        WHEN 'D' THEN 2
        WHEN 'G' THEN 3
        WHEN 'H' THEN 4
    END AS id_bloque_mar,
    CASE m.clave_materia
        WHEN 'B' THEN 1
        WHEN 'D' THEN 2
        WHEN 'G' THEN 3
        WHEN 'H' THEN 4
    END AS id_bloque_mie,
    CASE m.clave_materia
        WHEN 'B' THEN 1
        WHEN 'D' THEN 2
        WHEN 'G' THEN 3
        WHEN 'H' THEN 4
    END AS id_bloque_jue,
    CASE m.clave_materia
        WHEN 'B' THEN 1
        WHEN 'D' THEN 2
        WHEN 'G' THEN 3
        WHEN 'H' THEN 4
    END AS id_bloque_vie
FROM grupo g
JOIN materia m ON m.semestre = 3
WHERE g.semestre = 3;

-- Grupo de 5to semestre:
--  E -> bloque 1
--  K -> bloque 2
--  L -> bloque 3
--  I -> bloque 4
INSERT INTO grupo_materia (id_grupo, clave_materia,
                           id_bloque_lun, id_bloque_mar, id_bloque_mie, id_bloque_jue, id_bloque_vie)
SELECT 
    g.id_grupo,
    m.clave_materia,
    CASE m.clave_materia
        WHEN 'E' THEN 1
        WHEN 'K' THEN 2
        WHEN 'L' THEN 3
        WHEN 'I' THEN 4
    END AS id_bloque_lun,
    CASE m.clave_materia
        WHEN 'E' THEN 1
        WHEN 'K' THEN 2
        WHEN 'L' THEN 3
        WHEN 'I' THEN 4
    END AS id_bloque_mar,
    CASE m.clave_materia
        WHEN 'E' THEN 1
        WHEN 'K' THEN 2
        WHEN 'L' THEN 3
        WHEN 'I' THEN 4
    END AS id_bloque_mie,
    CASE m.clave_materia
        WHEN 'E' THEN 1
        WHEN 'K' THEN 2
        WHEN 'L' THEN 3
        WHEN 'I' THEN 4
    END AS id_bloque_jue,
    CASE m.clave_materia
        WHEN 'E' THEN 1
        WHEN 'K' THEN 2
        WHEN 'L' THEN 3
        WHEN 'I' THEN 4
    END AS id_bloque_vie
FROM grupo g
JOIN materia m ON m.semestre = 5
WHERE g.semestre = 5;
