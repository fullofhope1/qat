<?php
require_once '../config/db.php';
require_once '../includes/Autoloader.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php?auth=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userRepo = new UserRepository($pdo);
    $service = new UserService($userRepo);
    $user_id = $_SESSION['user_id'];
    $action = $_POST['action'] ?? 'password';

    try {
        if ($action === 'username') {
            $new_username = trim($_POST['new_username']);
            $confirm_pass = $_POST['confirm_password_username'];

            if (strlen($new_username) < 3) {
                throw new Exception("اسم المستخدم قصير جداً (3 أحرف على الأقل)");
            }

            if ($service->verifyPassword($user_id, $confirm_pass)) {
                if ($service->usernameExists($new_username)) {
                    throw new Exception("اسم المستخدم مستخدم بالفعل");
                }
                $service->changeUsername($user_id, $new_username);
                $_SESSION['username'] = $new_username;
                header("Location: ../settings.php?success=username");
            } else {
                throw new Exception("كلمة المرور غير صحيحة");
            }
            exit;
        }

        // Default: password change
        $current_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        if ($new_pass !== $confirm_pass) {
            throw new Exception("كلمتا المرور غير متطابقتين");
        }

        if ($service->verifyPassword($user_id, $current_pass)) {
            $service->changePassword($user_id, $new_pass);
            header("Location: ../settings.php?success=password");
        } else {
            throw new Exception("كلمة المرور الحالية غير صحيحة");
        }
    } catch (Exception $e) {
        header("Location: ../settings.php?error=" . urlencode($e->getMessage()));
    }
} else {
    header("Location: ../settings.php");
}
exit;
