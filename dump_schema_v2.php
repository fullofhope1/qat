<?php
require 'c:/xampp/htdocs/qat/config/db.php';
$tables = ['purchases', 'qat_types', 'leftovers', 'providers'];
foreach ($tables as $table) {
    echo "--- Schema for $table ---\n";
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        while ($row = $stmt->fetch()) {
            echo "Field: {$row['Field']} | Type: {$row['Type']} | Null: {$row['Null']} | Key: {$row['Key']} | Default: {$row['Default']}\n";
        }
    } catch (Exception $e) {
        echo "Error describing $table: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
