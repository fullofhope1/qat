<?php
require 'config/db.php';
$stmt = $pdo->query('SHOW COLUMNS FROM users');
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo $row['Field'] . "\n";
}
