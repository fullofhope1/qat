<?php
// requests/update_staff.php
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $repo = new StaffRepository($pdo);
        $service = new StaffService($repo);

        $id = $_POST['id'];
        $data = [
            'daily_salary' => $_POST['daily_salary'],
            'withdrawal_limit' => !empty($_POST['withdrawal_limit']) ? $_POST['withdrawal_limit'] : null
        ];

        $service->updateStaff($id, $data);
        header("Location: ../staff_details.php?id=$id&success=1");
    } catch (Exception $e) {
        $error = urlencode($e->getMessage());
        header("Location: ../staff_details.php?id=$id&error=$error");
    }
}
