<?php
// requests/add_provider.php
require '../config/db.php';
require_once '../includes/Autoloader.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $name  = trim($_POST['name']  ?? '');
    $phone = trim($_POST['phone'] ?? '');

    try {
        $repo = new ProviderRepository($pdo);
        $service = new ProviderService($repo);

        $id = $service->addProvider($name, $phone, $_SESSION['user_id']);

        echo json_encode([
            'success'  => true,
            'provider' => ['id' => $id, 'name' => $name, 'phone' => $phone]
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
