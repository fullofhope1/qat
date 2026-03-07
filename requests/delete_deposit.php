<?php
require_once '../config/db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        $expenseRepo = new ExpenseRepository($pdo);
        $depositRepo = new DepositRepository($pdo);
        $staffRepo = new StaffRepository($pdo);
        $service = new ExpenseService($expenseRepo, $depositRepo, $staffRepo);

        $service->deleteDeposit($_POST['id']);
        header("Location: ../expenses.php?success=deleted");
    } catch (Exception $e) {
        header("Location: ../expenses.php?error=" . urlencode($e->getMessage()));
    }
    exit;
}
header("Location: ../expenses.php");
exit;
