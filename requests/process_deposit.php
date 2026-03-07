<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $expenseRepo = new ExpenseRepository($pdo);
        $depositRepo = new DepositRepository($pdo);
        $staffRepo = new StaffRepository($pdo);
        $service = new ExpenseService($expenseRepo, $depositRepo, $staffRepo);

        $data = [
            'deposit_date' => $_POST['deposit_date'] ?? date('Y-m-d'),
            'currency' => $_POST['currency'] ?? 'YER',
            'amount' => $_POST['amount'] ?? 0,
            'recipient' => $_POST['recipient'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'created_by' => $_SESSION['user_id']
        ];

        if ($data['amount'] <= 0 || empty($data['recipient'])) {
            throw new Exception("يرجى إدخال المبلغ والجهة المستلمة");
        }

        $service->addDeposit($data);
        header("Location: ../expenses.php?success=1");
    } catch (Exception $e) {
        header("Location: ../expenses.php?error=" . urlencode($e->getMessage()));
    }
}
