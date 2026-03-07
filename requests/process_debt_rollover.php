<?php
require_once '../config/db.php';
require_once '../includes/Autoloader.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $debtRepo = new DebtRepository($pdo);
    $service = new DebtService($debtRepo);

    $customer_id = $_POST['customer_id'];
    $debt_type = $_POST['debt_type'];

    try {
        if ($service->rollover($customer_id, $debt_type)) {
            header("Location: ../debts.php?type=$debt_type&msg=rolled_over");
            exit;
        }
    } catch (Exception $e) {
        header("Location: ../debts.php?error=" . urlencode($e->getMessage()));
        exit;
    }
}
