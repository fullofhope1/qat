<?php
// requests/add_staff.php
require '../config/db.php';
require_once '../includes/Autoloader.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        die("Unauthorized");
    }

    try {
        $repo = new StaffRepository($pdo);
        $service = new StaffService($repo);

        $data = [
            'name' => $_POST['name'],
            'role' => $_POST['role'] ?? 'Employee',
            'daily_salary' => $_POST['daily_salary'] ?? 0,
            'withdrawal_limit' => !empty($_POST['withdrawal_limit']) ? $_POST['withdrawal_limit'] : null,
            'created_by' => $_SESSION['user_id']
        ];

        $service->addStaff($data);
        header("Location: ../staff.php?success=1");
    } catch (Exception $e) {
        header("Location: ../staff.php?error=" . urlencode($e->getMessage()));
    }
}
