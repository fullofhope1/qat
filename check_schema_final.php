<?php
require 'config/db.php';

foreach (['leftovers', 'sales', 'purchases'] as $t) {
    echo "\n--- $t ---\n";
    $cols = $pdo->query("DESCRIBE $t")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        echo "{$c['Field']} ({$c['Type']})\n";
    }
}
