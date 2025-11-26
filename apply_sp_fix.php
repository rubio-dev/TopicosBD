<?php
require_once __DIR__ . '/config/db.php';

try {
    $pdo = db();
    echo "Conectado a la base de datos.\n";

    // 1. Eliminar el procedimiento existente
    $pdo->exec("DROP PROCEDURE IF EXISTS sp_crear_carga_automatica");
    echo "Procedimiento anterior eliminado.\n";

    // 2. Leer el nuevo código del archivo SQL
    $sqlFile = file_get_contents(__DIR__ . '/sql/03_procedimientos.sql');
    
    // Extraer el cuerpo del procedimiento
    if (preg_match('/CREATE PROCEDURE[\s\S]+?END\$\$/', $sqlFile, $matches)) {
        $procSql = $matches[0];
        $procSql = str_replace('$$', '', $procSql);
        
        $pdo->exec($procSql);
        echo "Nuevo procedimiento creado correctamente con collation utf8mb4_general_ci.\n";
    } else {
        echo "Error: No se pudo extraer el código del procedimiento del archivo SQL.\n";
    }

} catch (PDOException $e) {
    echo "Error de BD: " . $e->getMessage() . "\n";
}
