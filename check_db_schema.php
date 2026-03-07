<?php
require 'config/db.php';
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo "--- TABLES ---\n";
print_r($tables);

foreach (['leftovers', 'sales', 'purchases', 'customers', 'damaged'] as $t) {
    echo "\n--- $t ---\n";
    if (in_array($t, $tables)) {
        $cols = $pdo->query("DESCRIBE $t")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            echo "{$c['Field']} ({$c['Type']})\n";
        }
    } else {
        echo "Table $t not found!\n";
    }
}
