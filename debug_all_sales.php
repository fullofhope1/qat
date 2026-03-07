<?php
require 'config/db.php';
$date = $_GET['date'] ?? date('Y-m-d');

echo "--- ALL SALES FOR DATE: $date ---\n";
$sql = "SELECT id, customer_id, weight_kg, price, payment_method, debt_type, is_paid, sale_date, due_date 
        FROM sales 
        WHERE sale_date = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$date]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($results);

echo "\n--- SUMMARY BY TYPE ---\n";
$sql = "SELECT payment_method, debt_type, is_paid, COUNT(*) as count, SUM(price) as total 
        FROM sales 
        WHERE sale_date = ? 
        GROUP BY payment_method, debt_type, is_paid";
$stmt = $pdo->prepare($sql);
$stmt->execute([$date]);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
