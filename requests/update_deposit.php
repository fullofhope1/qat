<?php
require '../config/db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $expenseRepo = new ExpenseRepository($pdo);
        $depositRepo = new DepositRepository($pdo);
        $staffRepo = new StaffRepository($pdo);
        $service = new ExpenseService($expenseRepo, $depositRepo, $staffRepo);

        $id = (int)$_POST['id'];
        $data = [
            'recipient' => $_POST['recipient'],
            'currency' => $_POST['currency'],
            'amount' => (float)$_POST['amount'],
            'notes' => $_POST['notes'] ?? ''
        ];

        $service->updateDeposit($id, $data);
        header("Location: ../expenses.php?tab=deposit");
    } catch (Exception $e) {
        header("Location: ../expenses.php?error=" . urlencode($e->getMessage()));
    }
}
