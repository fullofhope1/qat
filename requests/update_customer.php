<?php
require '../config/db.php';
require_once '../includes/Autoloader.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../customers.php');
    exit;
}

$id = (int)$_POST['id'];
$name = trim($_POST['name']);
$phone = trim($_POST['phone']);
$debt_limit = $_POST['debt_limit'];

if ($name === '') {
    header('Location: ../edit_customer.php?id=' . $id . '&error=NameRequired');
    exit;
}

if ($debt_limit === '' || $debt_limit === 'No Limit') {
    $debt_limit = null;
}

try {
    $repo = new CustomerRepository($pdo);
    $service = new CustomerService($repo);
    $service->updateCustomer($id, $name, $phone, $debt_limit);

    header('Location: ../customers.php');
} catch (Exception $e) {
    header('Location: ../edit_customer.php?id=' . $id . '&error=' . urlencode($e->getMessage()));
}
exit;
