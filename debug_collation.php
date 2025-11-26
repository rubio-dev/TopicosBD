<?php
require_once __DIR__ . '/config/db.php';

try {
    ob_start();
    $pdo = db();
    echo "=== CONNECTION COLLATION ===\n";
    $stmt = $pdo->query("SHOW VARIABLES LIKE 'collation%'");
    while ($row = $stmt->fetch()) {
        echo $row['Variable_name'] . ": " . $row['Value'] . "\n";
    }

    echo "\n=== TABLE COLLATION (periodo_lectivo) ===\n";
    $stmt = $pdo->query("SHOW TABLE STATUS LIKE 'periodo_lectivo'");
    $row = $stmt->fetch();
    echo "Collation: " . $row['Collation'] . "\n";

    echo "\n=== SP DEFINITION ===\n";
    $stmt = $pdo->query("SHOW CREATE PROCEDURE sp_crear_carga_automatica");
    $row = $stmt->fetch();
    echo "Database Collation: " . $row['Database Collation'] . "\n";
    echo "Collation Connection: " . $row['Collation Connection'] . "\n";
    echo "Create Procedure: \n" . $row['Create Procedure'] . "\n";

    $output = ob_get_clean();
    file_put_contents(__DIR__ . '/debug_output.log', $output);
    echo "Output written to debug_output.log\n";

} catch (PDOException $e) {
    file_put_contents(__DIR__ . '/debug_output.log', "Error: " . $e->getMessage() . "\n");
}
