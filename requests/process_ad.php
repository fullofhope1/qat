<?php
require_once '../config/db.php';
require_once '../includes/Autoloader.php';

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin')) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productRepo = new ProductRepository($pdo);
    $adRepo = new AdRepository($pdo);
    $service = new InventoryService($productRepo, $adRepo);

    $action = $_POST['action'] ?? '';

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
        'id' => $_POST['id'] ?? null,
        'client_name' => trim($_POST['client_name'] ?? ''),
        'title' => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'image_url' => trim($_POST['image_url'] ?? ''),
        'link_url' => trim($_POST['link_url'] ?? ''),
        'status' => $_POST['status'] ?? 'Active'
    ];

    if ($media_path) {
        $data['media_path'] = $media_path;
    }

    if ($service->processAd($action, $data)) {
        header("Location: ../manage_ads.php?success=1");
        exit;
    }
}
header("Location: ../manage_ads.php");
exit;
