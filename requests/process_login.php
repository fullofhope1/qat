<?php
require_once '../config/db.php';
require_once '../includes/Autoloader.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $userRepo = new UserRepository($pdo);
    $service = new UserService($userRepo);

    $user = $service->login($username, $password);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['sub_role'] = $user['sub_role'] ?? 'full';

        if ($user['role'] === 'user') {
            header("Location: ../index.php");
        } else {
            require_once '../includes/auth.php';
            $home = getHomeLink();
            header("Location: ../" . $home);
        }
        exit;
    } else {
        header("Location: ../index.php?error=1");
        exit;
    }
}
header("Location: ../index.php");
exit;
