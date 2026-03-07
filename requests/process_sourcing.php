<?php
// requests/process_sourcing.php
require_once '../config/db.php';
require_once '../includes/Autoloader.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        die("Unauthorized");
    }

    try {
        $purchaseRepo = new PurchaseRepository($pdo);
        $productRepo = new ProductRepository($pdo);
        $service = new PurchaseService($purchaseRepo, $productRepo);

        // Helper to handle uploads
        $media_path = null;
        if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            $file_name = time() . '_' . basename($_FILES['media']['name']);
            $target_file = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['media']['tmp_name'], $target_file)) {
                $media_path = 'uploads/' . $file_name;
            }
        }

        $data = [
            'purchase_date' => $_POST['purchase_date'] ?? date('Y-m-d'),
            'provider_id' => $_POST['provider_id'],
            'type_name' => trim($_POST['type_name']),
            'source_weight_grams' => $_POST['source_weight_grams'],
            'price_per_kilo' => $_POST['price_per_kilo'],
            'media_path' => $media_path,
            'created_by' => $_SESSION['user_id']
        ];

        $service->sourceShipment($data);

        header("Location: ../sourcing.php?success=1");
        exit;
    } catch (Exception $e) {
        $errorMsg = urlencode($e->getMessage());
        header("Location: ../sourcing.php?error=$errorMsg");
        exit;
    }
} else {
    header("Location: ../sourcing.php");
    exit;
}
