<?php
require 'c:/xampp/htdocs/qat/config/db.php';
$stmt = $pdo->query('SELECT username, role, sub_role FROM users');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
