<?php
require 'config/db.php';
$date = $_GET['date'] ?? date('Y-m-d');

echo "--- DEBT RECORDS FOR DATE: $date ---\n";
$sql = "SELECT id, customer_id, weight_kg, price, payment_method, debt_type, is_paid, sale_date, due_date 
        FROM sales 
        WHERE sale_date = ? AND payment_method = 'Debt' AND debt_type = 'Daily'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$date]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($results)) {
    echo "No daily debts found for this date.\n";
} else {
    foreach ($results as $r) {
        echo "ID: {$r['id']} | Cust: {$r['customer_id']} | Price: {$r['price']} | Paid: {$r['is_paid']} | SaleDate: {$r['sale_date']} | DueDate: {$r['due_date']}\n";
    }
}

echo "\n--- ALL UNPAID DAILY DEBTS (any date) ---\n";
$sql = "SELECT id, customer_id, price, sale_date, due_date 
        FROM sales 
        WHERE payment_method = 'Debt' AND debt_type = 'Daily' AND is_paid = 0";
$stmt = $pdo->query($sql);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
