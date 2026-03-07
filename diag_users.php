<?php
require 'config/db.php';
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $user) {
    $json = json_encode($user);
    if ($json === false) {
        echo "JSON ERROR for User ID " . $user['id'] . ": " . json_last_error_msg() . "\n";
        print_r($user);
    } else {
        echo "User ID " . $user['id'] . ": OK\n";
    }
}
echo "Total Users: " . count($users) . "\n";
