<?php
require '../config/db.php';
require_once '../includes/Autoloader.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Harden environment for AJAX
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Ensure only super admin (full) can manage users
$sub_role = $_SESSION['sub_role'] ?? 'unset';
$role = $_SESSION['role'] ?? 'unset';
$user_id = $_SESSION['user_id'] ?? 'unset';

if ($user_id === 'unset' || $role !== 'super_admin' || $sub_role !== 'full') {
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access Denied: Insufficient Permissions']);
    exit;
}

// Clear buffer and send JSON
while (ob_get_level() > 1) ob_end_clean();
header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Initialization via Clean Architecture
$userRepo = new UserRepository($pdo);
$service = new UserService($userRepo);

try {
    if ($action === 'list') {
        $users = $service->listUsers();
        echo json_encode(['success' => true, 'data' => $users]);
        exit;
    }

    if ($action === 'add') {
        $data = [
            'username'     => trim($_POST['username'] ?? ''),
            'display_name' => trim($_POST['display_name'] ?? ''),
            'phone'        => trim($_POST['phone'] ?? ''),
            'password'     => trim($_POST['password'] ?? ''),
            'role_group'   => trim($_POST['role_group'] ?? '')
        ];

        if (!$data['username'] || !$data['password'] || !$data['role_group']) {
            throw new Exception("Missing required fields.");
        }

        if ($service->usernameExists($data['username'])) {
            throw new Exception("اسم المستخدم مسجل مسبقاً.");
        }

        if ($service->addUser($data)) {
            echo json_encode(['success' => true, 'message' => 'تم إضافة المستخدم بنجاح.']);
        } else {
            throw new Exception("Error adding user.");
        }
        exit;
    }

    if ($action === 'edit') {
        $target_user_id = (int)($_POST['user_id'] ?? 0);
        $data = [
            'display_name' => trim($_POST['display_name'] ?? ''),
            'phone'        => trim($_POST['phone'] ?? ''),
            'role_group'   => trim($_POST['role_group'] ?? ''),
            'password'     => trim($_POST['password'] ?? '')
        ];

        if (!$target_user_id || !$data['role_group']) {
            throw new Exception("Missing required fields.");
        }

        $target_user = $service->getUser($target_user_id);
        if (!$target_user) {
            throw new Exception("المستخدم غير موجود.");
        }

        if ($target_user['role'] === 'admin') {
            throw new Exception("لا يمكنك تعديل صلاحيات حساب المستلم الأساسي (admin).");
        }

        if ($target_user_id == $_SESSION['user_id']) {
            throw new Exception("لا يمكنك تعديل صلاحياتك الشخصية من هذه الشاشة. استخدم إعدادات الحساب.");
        }

        if ($service->updateUser($target_user_id, $data)) {
            echo json_encode(['success' => true, 'message' => 'تم تحديث بيانات المستخدم.']);
        } else {
            throw new Exception("Error updating user.");
        }
        exit;
    }

    if ($action === 'delete') {
        $target_user_id = (int)($_POST['user_id'] ?? 0);

        if (!$target_user_id) throw new Exception("Invalid user ID.");
        if ($target_user_id == $_SESSION['user_id']) throw new Exception("لا يمكنك حذف حسابك الشخصي.");

        $target_user = $service->getUser($target_user_id);
        if ($target_user && $target_user['role'] === 'admin') {
            throw new Exception("لا يمكنك حذف حساب المستلم الأساسي (admin).");
        }

        if ($service->deleteUser($target_user_id)) {
            echo json_encode(['success' => true, 'message' => 'تم حذف المستخدم.']);
        } else {
            throw new Exception("Error deleting user.");
        }
        exit;
    }

    throw new Exception("Invalid action.");
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
