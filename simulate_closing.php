<?php
require 'config/db.php';
$today = '2026-03-07';

$stmtDebt = $pdo->prepare("SELECT COUNT(*) as count, SUM(price) as total FROM sales WHERE sale_date = ? AND payment_method = 'Debt' AND debt_type = 'Daily' AND is_paid = 0");
$stmtDebt->execute([$today]);
$debtStats = $stmtDebt->fetch(PDO::FETCH_ASSOC);

echo "Query: SELECT COUNT(*) as count, SUM(price) as total FROM sales WHERE sale_date = '$today' AND payment_method = 'Debt' AND debt_type = 'Daily' AND is_paid = 0\n";
print_r($debtStats);

echo "\n--- RECORDS MATCHING PAYMENT='Debt', IS_PAID=0, DATE='$today' ---\n";
$sql = "SELECT id, debt_type, price FROM sales WHERE sale_date = ? AND payment_method = 'Debt' AND is_paid = 0";
$stmt = $pdo->prepare($sql);
$stmt->execute([$today]);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
