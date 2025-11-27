/* ============================================================
   01_estructura_tablas.sql
   Estructura base de la BD (MariaDB / XAMPP)
   ============================================================ */

CREATE DATABASE IF NOT EXISTS control_horarios
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;

USE control_horarios;

/* ============================
   1. Catálogo de SEMESTRES
   ============================ */

CREATE TABLE semestre (
    semestre TINYINT NOT NULL,
    PRIMARY KEY (semestre),
    CONSTRAINT ck_semestre_valido CHECK (semestre IN (1,3,5))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* ============================
   2. Catálogo de PERIODOS
   ============================ */

CREATE TABLE periodo_lectivo (
    id_periodo   CHAR(6)  NOT NULL,
    anio         SMALLINT NOT NULL,
    ciclo        CHAR(2)  NOT NULL,
    descripcion  VARCHAR(50) NOT NULL,
    fecha_inicio DATE      NULL,
    fecha_fin    DATE      NULL,
    PRIMARY KEY (id_periodo),
    CONSTRAINT ck_periodo_ciclo CHECK (ciclo = '02')   -- solo AGO-DIC
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* ============================
   3. Catálogo de MATERIAS
   ============================ */

CREATE TABLE materia (
    clave_materia CHAR(2)     NOT NULL,
    nombre        VARCHAR(80) NOT NULL,
    semestre      TINYINT     NOT NULL,
    creditos      TINYINT     NULL,
    PRIMARY KEY (clave_materia),
    CONSTRAINT fk_materia_semestre
        FOREIGN KEY (semestre) REFERENCES semestre(semestre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* ============================
   4. Prerrequisitos de MATERIAS
   ============================ */

CREATE TABLE materia_prerequisito (
    clave_materia        CHAR(2) NOT NULL,
    clave_materia_prereq CHAR(2) NOT NULL,
    es_obligatorio       TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (clave_materia, clave_materia_prereq),
    CONSTRAINT fk_mp_materia
        FOREIGN KEY (clave_materia) REFERENCES materia(clave_materia),
    CONSTRAINT fk_mp_materia_pre
        FOREIGN KEY (clave_materia_prereq) REFERENCES materia(clave_materia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* ============================
   5. ALUMNOS
   ============================ */

CREATE TABLE alumno (
    id_alumno       CHAR(8)      NOT NULL,
    anio_ingreso    SMALLINT     NOT NULL,
    ciclo_ingreso   CHAR(2)      NOT NULL,
    nombre          VARCHAR(100) NOT NULL,
    semestre_actual TINYINT      NOT NULL,
    PRIMARY KEY (id_alumno),
    CONSTRAINT ck_alumno_ciclo
        CHECK (ciclo_ingreso IN ('01','02')),
    CONSTRAINT ck_alumno_sem
        CHECK (semestre_actual IN (1,3,5))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* ============================
   6. HISTORIAL / CALIFICACIONES
   ============================ */

CREATE TABLE alumno_materia (
    id_alumno_materia INT NOT NULL AUTO_INCREMENT,
    id_alumno         CHAR(8) NOT NULL,
    clave_materia     CHAR(2) NOT NULL,
    id_periodo        CHAR(6) NOT NULL,
    calificacion      TINYINT NULL,
    tipo_acred        TINYINT NULL,
    estatus           CHAR(1) NOT NULL,  -- 'C','A','R'
    PRIMARY KEY (id_alumno_materia),
    CONSTRAINT fk_am_alumno
        FOREIGN KEY (id_alumno) REFERENCES alumno(id_alumno),
    CONSTRAINT fk_am_materia
        FOREIGN KEY (clave_materia) REFERENCES materia(clave_materia),
    CONSTRAINT fk_am_periodo
        FOREIGN KEY (id_periodo) REFERENCES periodo_lectivo(id_periodo),
    CONSTRAINT ck_am_estatus
        CHECK (estatus IN ('C','A','R'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX ix_am_alumno_materia
    ON alumno_materia (id_alumno, clave_materia, estatus);

/* ============================
   7. BLOQUES HORARIOS
   ============================ */

CREATE TABLE bloque_horario (
    id_bloque   TINYINT  NOT NULL,
    hora_inicio TIME     NOT NULL,
    hora_fin    TIME     NOT NULL,
    etiqueta    CHAR(5)  NOT NULL,
    PRIMARY KEY (id_bloque)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* ============================
   8. GRUPOS
   ============================ */

CREATE TABLE grupo (
    id_grupo    INT NOT NULL AUTO_INCREMENT,
    id_periodo  CHAR(6) NOT NULL,
    semestre    TINYINT NOT NULL,
    paquete     CHAR(2) NOT NULL,
    letra_grupo CHAR(1) NOT NULL,
    PRIMARY KEY (id_grupo),
    CONSTRAINT fk_grupo_periodo
        FOREIGN KEY (id_periodo) REFERENCES periodo_lectivo(id_periodo),
    CONSTRAINT fk_grupo_semestre
        FOREIGN KEY (semestre) REFERENCES semestre(semestre),
    CONSTRAINT uq_grupo UNIQUE (id_periodo, semestre, paquete, letra_grupo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* ============================
   9. GRUPO-MATERIA con horarios
   ============================ */

CREATE TABLE grupo_materia (
    id_grupo_materia INT NOT NULL AUTO_INCREMENT,
    id_grupo         INT     NOT NULL,
    clave_materia    CHAR(2) NOT NULL,
    id_bloque_lun    TINYINT NOT NULL,
    id_bloque_mar    TINYINT NOT NULL,
    id_bloque_mie    TINYINT NOT NULL,
    id_bloque_jue    TINYINT NULL,
    id_bloque_vie    TINYINT NULL,
    PRIMARY KEY (id_grupo_materia),
    CONSTRAINT fk_gm_grupo
        FOREIGN KEY (id_grupo) REFERENCES grupo(id_grupo),
    CONSTRAINT fk_gm_materia
        FOREIGN KEY (clave_materia) REFERENCES materia(clave_materia),
    CONSTRAINT fk_gm_lun  FOREIGN KEY (id_bloque_lun) REFERENCES bloque_horario(id_bloque),
    CONSTRAINT fk_gm_mar  FOREIGN KEY (id_bloque_mar) REFERENCES bloque_horario(id_bloque),
    CONSTRAINT fk_gm_mie  FOREIGN KEY (id_bloque_mie) REFERENCES bloque_horario(id_bloque),
    CONSTRAINT fk_gm_jue  FOREIGN KEY (id_bloque_jue) REFERENCES bloque_horario(id_bloque),
    CONSTRAINT fk_gm_vie  FOREIGN KEY (id_bloque_vie) REFERENCES bloque_horario(id_bloque),
    CONSTRAINT uq_gm UNIQUE (id_grupo, clave_materia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* ============================
   10. CARGA ACADEMICA (encabezado)
   ============================ */

CREATE TABLE carga_academica (
    id_carga   INT NOT NULL AUTO_INCREMENT,
    id_alumno  CHAR(8) NOT NULL,
    id_periodo CHAR(6) NOT NULL,
    fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estatus    CHAR(1) NOT NULL DEFAULT 'A',   -- 'A','C'
    PRIMARY KEY (id_carga),
    CONSTRAINT fk_ca_alumno
        FOREIGN KEY (id_alumno) REFERENCES alumno(id_alumno),
    CONSTRAINT fk_ca_periodo
        FOREIGN KEY (id_periodo) REFERENCES periodo_lectivo(id_periodo),
    CONSTRAINT ck_ca_estatus CHECK (estatus IN ('A','C')),
    CONSTRAINT uq_ca_alumno_periodo UNIQUE (id_alumno, id_periodo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* ============================
   11. CARGA DETALLE
   ============================ */

CREATE TABLE carga_detalle (
    id_carga_det     INT NOT NULL AUTO_INCREMENT,
    id_carga         INT NOT NULL,
    id_grupo_materia INT NOT NULL,
    PRIMARY KEY (id_carga_det),
    CONSTRAINT fk_cd_carga
        FOREIGN KEY (id_carga) REFERENCES carga_academica(id_carga),
    CONSTRAINT fk_cd_grupo_materia
        FOREIGN KEY (id_grupo_materia) REFERENCES grupo_materia(id_grupo_materia),
    CONSTRAINT uq_cd UNIQUE (id_carga, id_grupo_materia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
