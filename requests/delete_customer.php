<?php
require '../config/db.php';
require_once '../includes/Autoloader.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    if (!empty($id)) {
        try {
            $repo = new CustomerRepository($pdo);
            $service = new CustomerService($repo);
            $service->removeCustomer($id);

            header("Location: ../customers.php?deleted=1");
            exit;
        } catch (Exception $e) {
            $error = urlencode("Could not delete customer. Error: " . $e->getMessage());
            header("Location: ../customers.php?error=$error");
            exit;
        }
    }
}
header("Location: ../customers.php");
exit;
