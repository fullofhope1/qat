<?php
// test_db_connection.php
require_once __DIR__ . '/config/db.php';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->query('SELECT COUNT(*) AS cnt FROM users');
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Connection OK – Users table rows: " . $row['cnt'] . PHP_EOL;
} catch (PDOException $e) {
    echo "❌ DB connection error: " . $e->getMessage() . PHP_EOL;
}
