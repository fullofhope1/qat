<?php
require_once '../config/db.php';
require_once '../includes/Autoloader.php';
require_once '../includes/error_page.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $repo = new SaleRepository($pdo);
        $purchaseRepo = new PurchaseRepository($pdo);
        $customerRepo = new CustomerRepository($pdo);
        $leftoverRepo = new LeftoverRepository($pdo);
        $service = new SaleService($repo, $purchaseRepo, $customerRepo, $leftoverRepo);

        $data = [
            'sale_date' => $_POST['sale_date'],
            'customer_id' => !empty($_POST['customer_id']) ? $_POST['customer_id'] : null,
            'qat_type_id' => $_POST['qat_type_id'],
            'purchase_id' => !empty($_POST['purchase_id']) ? $_POST['purchase_id'] : null,
            'leftover_id' => !empty($_POST['leftover_id']) ? $_POST['leftover_id'] : null,
            'qat_status' => !empty($_POST['qat_status']) ? $_POST['qat_status'] : 'Tari',
            'unit_type' => $_POST['unit_type'] ?? 'weight',
            'weight_grams' => (float)($_POST['weight_grams'] ?? 0),
            'quantity_units' => (int)($_POST['quantity_units'] ?? 0),
            'price' => (float)$_POST['price'],
            'payment_method' => $_POST['payment_method'],
            'transfer_sender' => !empty($_POST['transfer_sender']) ? $_POST['transfer_sender'] : null,
            'transfer_receiver' => !empty($_POST['transfer_receiver']) ? $_POST['transfer_receiver'] : null,
            'transfer_number' => !empty($_POST['transfer_number']) ? $_POST['transfer_number'] : null,
            'transfer_company' => !empty($_POST['transfer_company']) ? $_POST['transfer_company'] : null,
            'is_paid' => ($_POST['payment_method'] === 'Debt') ? 0 : 1,
            'debt_type' => ($_POST['payment_method'] === 'Debt') ? (!empty($_POST['debt_type']) ? $_POST['debt_type'] : 'Daily') : null,
            'notes' => !empty($_POST['notes']) ? $_POST['notes'] : ''
        ];

        $service->processSale($data);

        $source = !empty($_POST['source_page']) ? $_POST['source_page'] : '';
        if ($data['leftover_id'] || $source === 'leftovers') {
            header("Location: ../sales_leftovers.php?success=1");
        } else {
            header("Location: ../sales.php?success=1");
        }
        exit;
    } catch (Exception $e) {
        $parts = explode('|', $e->getMessage());
        $errorCode = $parts[0];

        if ($errorCode === 'InventoryExceeded') {
            showErrorPage("عذراً، الكمية غير متوفرة", "لقد طلبت كمية أكبر من المخزون المتاح لهذا المورد.", "المتاح: {$parts[1]} <br> المطلوب: {$parts[2]}");
        } elseif ($errorCode === 'LeftoverExceeded') {
            showErrorPage("عذراً، الكمية غير متوفرة (بقايا)", "الكمية المطلوبة من البقايا غير متوفرة.", "المتاح: {$parts[1]} <br> المطلوب: {$parts[2]}");
        } elseif ($errorCode === 'CreditLimitExceeded') {
            showErrorPage("تم تجاوز سقف الدين!", "لا يمكن إتمام العملية لأن الزبون تجاوز الحد المسموح للدين.", "Limit: " . number_format($parts[1]) . " YER <br> Current Debt: " . number_format($parts[2]) . " YER");
        } else {
            showErrorPage("حدث خطأ في النظام", "فشلت عملية البيع بسبب خطأ غير متوقع.", $e->getMessage());
        }
    }
}
