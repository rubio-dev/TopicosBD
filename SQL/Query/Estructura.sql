-- =========================================================
-- ARCHIVO A  -  Estructura del proyecto de horarios
-- Base de datos: proyecto_horarios
-- Motor: MariaDB / MySQL
-- =========================================================

DROP DATABASE IF EXISTS proyecto_horarios;

CREATE DATABASE proyecto_horarios
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE proyecto_horarios;

-- =======================
-- CATÁLOGOS BÁSICOS
-- =======================

CREATE TABLE alumno (
    num_control      CHAR(5)      NOT NULL,    -- E001, E002, ...
    nombre           VARCHAR(80)  NOT NULL,
    semestre_actual  TINYINT      NOT NULL,
    estatus          CHAR(1)      NOT NULL DEFAULT 'A',  -- A = Activo
    CONSTRAINT pk_alumno PRIMARY KEY (num_control)
);

CREATE TABLE materia (
    clave_materia       CHAR(1)      NOT NULL,     -- A..L
    nombre              VARCHAR(60)  NOT NULL,
    semestre_sugerido   TINYINT      NOT NULL,     -- 1, 3, 5...
    es_obligatoria      TINYINT(1)   NOT NULL DEFAULT 1,
    CONSTRAINT pk_materia PRIMARY KEY (clave_materia)
);

-- Retícula / Seriación (prerrequisitos)
CREATE TABLE reticula (
    clave_materia               CHAR(1) NOT NULL,
    clave_materia_prerrequisito CHAR(1) NOT NULL,
    CONSTRAINT pk_reticula PRIMARY KEY (clave_materia, clave_materia_prerrequisito),
    CONSTRAINT fk_reticula_materia
        FOREIGN KEY (clave_materia) REFERENCES materia (clave_materia),
    CONSTRAINT fk_reticula_prerrequisito
        FOREIGN KEY (clave_materia_prerrequisito) REFERENCES materia (clave_materia)
);

-- Periodos: A-D 01, E-J 01...
CREATE TABLE periodo (
    id_periodo     INT          NOT NULL AUTO_INCREMENT,
    clave_periodo  VARCHAR(10)  NOT NULL,
    descripcion    VARCHAR(100) NULL,
    fecha_inicio   DATE         NULL,
    fecha_fin      DATE         NULL,
    CONSTRAINT pk_periodo PRIMARY KEY (id_periodo),
    CONSTRAINT uq_periodo_clave UNIQUE (clave_periodo)
);

-- Oferta de grupos con horario
CREATE TABLE grupo (
    id_grupo      INT          NOT NULL AUTO_INCREMENT,
    id_periodo    INT          NOT NULL,
    paquete       VARCHAR(3)   NOT NULL,   -- 1A, 1B, 2A, 2B, 3A...
    clave_materia CHAR(1)      NOT NULL,
    grupo         CHAR(1)      NOT NULL,   -- A, B, C...
    hor_lun       VARCHAR(5)   NULL,
    hor_mar       VARCHAR(5)   NULL,
    hor_mie       VARCHAR(5)   NULL,
    hor_jue       VARCHAR(5)   NULL,
    hor_vie       VARCHAR(5)   NULL,
    CONSTRAINT pk_grupo PRIMARY KEY (id_grupo),
    CONSTRAINT fk_grupo_periodo
        FOREIGN KEY (id_periodo) REFERENCES periodo (id_periodo),
    CONSTRAINT fk_grupo_materia
        FOREIGN KEY (clave_materia) REFERENCES materia (clave_materia),
    CONSTRAINT uq_grupo UNIQUE (id_periodo, clave_materia, grupo)
);

-- Historial de calificaciones
CREATE TABLE calificacion (
    id_calificacion    INT         NOT NULL AUTO_INCREMENT,
    num_control        CHAR(5)     NOT NULL,
    clave_materia      CHAR(1)     NOT NULL,
    id_periodo         INT         NOT NULL,
    calificacion       TINYINT     NOT NULL,
    tipo_acreditacion  TINYINT     NOT NULL,  -- 1 = Ordinario, 2 = Repetición...
    CONSTRAINT pk_calificacion PRIMARY KEY (id_calificacion),
    CONSTRAINT fk_calif_alumno
        FOREIGN KEY (num_control)   REFERENCES alumno  (num_control),
    CONSTRAINT fk_calif_materia
        FOREIGN KEY (clave_materia) REFERENCES materia (clave_materia),
    CONSTRAINT fk_calif_periodo
        FOREIGN KEY (id_periodo)    REFERENCES periodo (id_periodo),
    CONSTRAINT uq_calif_alumno_mat_periodo
        UNIQUE (num_control, clave_materia, id_periodo)
);

-- Inscripciones finales a grupos
CREATE TABLE inscripcion_grupo (
    id_inscripcion    INT        NOT NULL AUTO_INCREMENT,
    num_control       CHAR(5)    NOT NULL,
    id_grupo          INT        NOT NULL,
    fecha_inscripcion DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_inscripcion PRIMARY KEY (id_inscripcion),
    CONSTRAINT fk_insc_alumno
        FOREIGN KEY (num_control) REFERENCES alumno (num_control),
    CONSTRAINT fk_insc_grupo
        FOREIGN KEY (id_grupo)    REFERENCES grupo (id_grupo),
    CONSTRAINT uq_insc_alumno_grupo UNIQUE (num_control, id_grupo)
);

-- Prioridad de materias por alumno (1 alta, 2 normal, 3 baja/adelanto)
CREATE TABLE prioridad_alumno_materia (
    num_control   CHAR(5)   NOT NULL,
    clave_materia CHAR(1)   NOT NULL,
    prioridad     TINYINT   NOT NULL,
    CONSTRAINT pk_prioridad PRIMARY KEY (num_control, clave_materia),
    CONSTRAINT fk_prioridad_alumno
        FOREIGN KEY (num_control)   REFERENCES alumno (num_control),
    CONSTRAINT fk_prioridad_materia
        FOREIGN KEY (clave_materia) REFERENCES materia (clave_materia)
);
