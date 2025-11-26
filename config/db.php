<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Devuelve una conexión PDO reutilizable a la base de datos control_horarios.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . TB_DB_HOST . ';dbname=' . TB_DB_NAME . ';charset=' . TB_DB_CHARSET;

        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $pdo = new PDO($dsn, TB_DB_USER, TB_DB_PASS, $opciones);
        
        // Asegurar que la conexión use el mismo collation que las tablas (utf8mb4_general_ci)
        // para evitar error "Illegal mix of collations" en Stored Procedures.
        $pdo->exec("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci'");
    }

    return $pdo;
}
