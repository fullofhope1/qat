<?php
require 'config/db.php';
$stmt = $pdo->query("DESCRIBE purchases");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
