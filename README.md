# TopicosBD - Sistema de Control Escolar

Bienvenido a **TopicosBD**, un sistema integral para la gestión de información escolar. Este proyecto está diseñado para administrar alumnos, grupos, cargas académicas e historiales de calificaciones de manera eficiente.

## Características Principales

El sistema cuenta con un dashboard principal que centraliza el acceso a los siguientes módulos:

*   **Alumnos**: Gestión completa de estudiantes (alta, baja, modificación). Permite definir semestre actual y ciclo de ingreso.
*   **Periodos y Grupos**: Administración de la oferta académica, creación de grupos por semestre y gestión de horarios.
*   **Cargas Académicas**: Asignación de materias a alumnos, generación automática de cargas y ajustes manuales.
*   **Historial**: Consulta de kardex y captura de calificaciones para actualizar el estatus de las materias.

## Tecnologías Utilizadas

*   **Lenguaje**: PHP 8.x (Nativo, sin frameworks pesados).
*   **Base de Datos**: MySQL / MariaDB.
*   **Frontend**: HTML5, CSS3 (Diseño responsivo y moderno).
*   **Servidor Web**: Apache (XAMPP recomendado).

## Instalación y Configuración

1.  **Clonar/Copiar**: Coloca la carpeta del proyecto `TopicosBD` en tu directorio de servidor web (ej. `C:\xampp\htdocs\TopicosBD`).
2.  **Base de Datos**:
    *   Abre tu gestor de base de datos (ej. phpMyAdmin).
    *   Crea una base de datos llamada `control_horarios` (o la que definas en la configuración).
    *   Importa los scripts SQL ubicados en la carpeta `sql/` en el siguiente orden:
        1.  `01_estructura_tablas.sql`
        2.  `02_datos_iniciales.sql`
        3.  `03_procedimientos.sql`
3.  **Configuración**:
    *   Revisa el archivo `config/config.php` para ajustar las credenciales de conexión a la base de datos si es necesario (Host, Usuario, Contraseña).
4.  **Ejecución**:
    *   Abre tu navegador y ve a: `http://localhost/TopicosBD/`

## Integrantes del Equipo

*   Rubio Figueroa José E. - 22211924
*   Sanchez Cibrian Omar - 22211928

---
*Proyecto desarrollado para la materia de Tópicos de Base de Datos.*
