<?php
require '../config/db.php';
require_once '../includes/Autoloader.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'] ?? '';

    try {
        $repo = new CustomerRepository($pdo);
        $service = new CustomerService($repo);

        $id = $service->addCustomer($name, $phone);

        echo json_encode(['success' => true, 'id' => $id]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
