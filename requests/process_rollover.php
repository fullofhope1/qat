<?php
require '../config/db.php';
require '../includes/Autoloader.php';

if (isset($_GET['sale_id'])) {
    $sale_id = $_GET['sale_id'];

    $debtRepo = new DebtRepository($pdo);
    $service = new DebtService($debtRepo);

    if ($service->rolloverSale($sale_id)) {
        header("Location: ../reports.php?success=RolloverDone");
        exit;
    }
}
header("Location: ../reports.php");
exit;
