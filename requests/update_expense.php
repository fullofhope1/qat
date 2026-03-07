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
            'category' => $_POST['category'],
            'description' => $_POST['description'] ?? '',
            'amount' => (float)$_POST['amount'],
            'staff_id' => !empty($_POST['staff_id']) ? (int)$_POST['staff_id'] : null
        ];

        $service->updateExpense($id, $data);
        header("Location: ../expenses.php?success=1");
    } catch (Exception $e) {
        header("Location: ../expenses.php?error=" . urlencode($e->getMessage()));
    }
}
