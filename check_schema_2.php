<?php
require 'config/db.php';
$stmt = $pdo->query("DESCRIBE sales");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
