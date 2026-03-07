<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=qat_erp;charset=utf8", 'root', '');
    echo "Connected successfully to qat_erp\n";
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    print_r($tables);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
try {
    $pdo = new PDO("mysql:host=localhost;dbname=qat;charset=utf8", 'root', '');
    echo "Connected successfully to qat\n";
} catch (PDOException $e) {
    echo "Connection failed for 'qat': " . $e->getMessage() . "\n";
}
