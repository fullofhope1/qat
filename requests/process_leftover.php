<?php
require_once '../config/db.php';
require_once '../includes/Autoloader.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $purchaseRepo = new PurchaseRepository($pdo);
        $leftoverRepo = new LeftoverRepository($pdo);
        $service = new LeftoverService($leftoverRepo, $purchaseRepo);

        $purchaseId = $_POST['purchase_id'];
        $weightKg = $_POST['weight_kg'];
        $action = $_POST['action']; // 'Drop' or 'SellNextDay'
        $notes = $_POST['notes'] ?? '';

        if ($action === 'Drop') {
            $service->markAsWaste($purchaseId, $weightKg, $notes);
        } else {
            $service->transferToNextDay($purchaseId, $weightKg, $notes);
        }

        header("Location: ../leftovers.php?success=1");
        exit;
    } catch (Exception $e) {
        die("Error processing leftover: " . $e->getMessage());
    }
}
