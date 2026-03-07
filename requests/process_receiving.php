<?php
// requests/process_receiving.php
require_once '../config/db.php';
require_once '../includes/Autoloader.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $purchaseRepo = new PurchaseRepository($pdo);
        $productRepo = new ProductRepository($pdo);
        $service = new PurchaseService($purchaseRepo, $productRepo);

        $service->receiveShipment($_POST['purchase_id'], $_POST['received_weight_grams']);

        header("Location: ../purchases.php?success=received");
        exit;
    } catch (Exception $e) {
        $errorMsg = urlencode($e->getMessage());
        header("Location: ../purchases.php?error=$errorMsg");
        exit;
    }
} else {
    header("Location: ../purchases.php");
    exit;
}
