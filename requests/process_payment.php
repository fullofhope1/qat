<?php
require_once '../config/db.php';
require_once '../includes/Autoloader.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = (int)$_POST['customer_id'];
    $amount = (float)$_POST['amount'];
    $note = $_POST['note'] ?? '';
    $back = $_POST['back'] ?? 'customers';

    $debtRepo = new DebtRepository($pdo);
    $service = new DebtService($debtRepo);
    $customerRepo = new CustomerRepository($pdo);
    $customerService = new CustomerService($customerRepo);

    try {
        $cust = $customerService->getCustomer($customer_id);
        if (!$cust) {
            throw new Exception("العميل غير موجود");
        }

        if ($amount <= 0) {
            throw new Exception("المبلغ يجب أن يكون أكبر من صفر");
        }

        if ($amount > $cust['total_debt']) {
            throw new Exception("مبلغ السداد (" . number_format($amount) . ") أكبر من الدين الحالي (" . number_format($cust['total_debt']) . ")");
        }

        if ($service->recordPayment($customer_id, $amount, $note)) {
            header("Location: ../customer_details.php?id=$customer_id&back=$back&success=1");
            exit;
        }
    } catch (Exception $e) {
        $error = urlencode($e->getMessage());
        header("Location: ../customer_details.php?id=$customer_id&back=$back&pay_error=$error");
        exit;
    }
}
header("Location: ../index.php");
exit;
