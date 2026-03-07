<?php
require_once '../config/db.php';
require_once '../includes/Autoloader.php';

session_start();

$debtRepo = new DebtRepository($pdo);
$service = new DebtService($debtRepo);

try {
    if ($service->reconcile()) {
        header("Location: ../debts.php?reconciled=1");
        exit;
    }
} catch (Exception $e) {
    header("Location: ../debts.php?error=" . urlencode($e->getMessage()));
    exit;
}
