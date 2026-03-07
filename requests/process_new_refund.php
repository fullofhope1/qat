<?php
// requests/process_new_refund.php
require '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        die("Unauthorized");
    }

    $customer_id = (int)$_POST['customer_id'];
    $amount      = (float)$_POST['amount'];
    $type        = $_POST['refund_type'];
    $reason      = $_POST['reason'];
    $user_id     = $_SESSION['user_id'];

    if (!$customer_id || !$amount || !$type) {
        header("Location: ../refunds.php?error=" . urlencode("بيانات غير مكتملة"));
        exit;
    }

    $refundRepo = new RefundRepository($pdo);
    $customerRepo = new CustomerRepository($pdo);
    $service = new RefundService($refundRepo, $customerRepo);

    $data = [
        'customer_id' => $_POST['customer_id'],
        'amount' => $_POST['amount'],
        'refund_type' => $_POST['refund_type'],
        'reason' => $_POST['reason']
    ];

    try {
        if ($service->processRefund($data)) {
            header("Location: ../refunds.php?success=1");
            exit;
        }
    } catch (Exception $e) {
        header("Location: ../refunds.php?error=" . urlencode($e->getMessage()));
        exit;
    }
}
