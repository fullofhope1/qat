<?php
// requests/link_transfer_ajax.php
require_once '../config/db.php';
require_once '../includes/Autoloader.php';

session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transferId = $_POST['transfer_id'] ?? null;
    $customerId = $_POST['customer_id'] ?? null;

    if (!$transferId || !$customerId) {
        echo json_encode(['success' => false, 'message' => 'بيانات ناقصة.']);
        exit;
    }

    $commRepo = new CommunicationRepository($pdo);
    $service = new CommunicationService($commRepo);

    if ($service->linkTransferToCustomer($transferId, $customerId)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'فشل في ربط التحويل.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
