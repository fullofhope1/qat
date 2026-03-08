<?php
require 'c:/xampp/htdocs/qat/config/db.php';
$tables = ['purchases', 'products'];
foreach ($tables as $table) {
    echo "--- Schema for $table ---\n";
    $stmt = $pdo->query("DESCRIBE $table");
    while ($row = $stmt->fetch()) {
        print_r($row);
    }
}
