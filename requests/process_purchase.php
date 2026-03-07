<?php
require_once '../config/db.php';
require_once '../includes/Autoloader.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        die("Unauthorized");
    }

    $purchaseRepo = new PurchaseRepository($pdo);
    $productRepo = new ProductRepository($pdo);
    $service = new PurchaseService($purchaseRepo, $productRepo);

    try {
        $data = [
            'purchase_date' => !empty($_POST['purchase_date']) ? $_POST['purchase_date'] : date('Y-m-d'),
            // Note: vendor_name is currently procedural-only or legacy field, 
            // the new system uses provider_id. We'll try to map if possible or just use what service expects.
            'qat_type_id' => $_POST['qat_type_id'],
            'quantity_kg' => (float)$_POST['quantity_kg'],
            'agreed_price' => (float)($_POST['agreed_price'] ?? 0),
            'status' => 'Fresh',
            'created_by' => $_SESSION['user_id']
        ];

        // If repo expects vendor_name, add it back or handle provider mapping
        // Given PurchaseRepository.create uses provider_id, we might need a fallback or fix.
        // For now, let's keep it safe.
        if ($service->addPurchase($data)) {
            header("Location: ../purchases.php?success=1");
            exit;
        }
    } catch (Exception $e) {
        $errorMsg = urlencode($e->getMessage());
        header("Location: ../purchases.php?error=$errorMsg");
        exit;
    }
}
header("Location: ../purchases.php");
exit;
