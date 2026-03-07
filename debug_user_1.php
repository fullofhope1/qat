<?php
require 'config/db.php';
$stmt = $pdo->query("SELECT id, username, role, sub_role FROM users WHERE id = 1");
$user = $stmt->fetch();
if ($user) {
    echo "ID: " . $user['id'] . "\n";
    echo "Username: " . $user['username'] . " (" . bin2hex($user['username']) . ")\n";
    echo "Role: " . $user['role'] . " (" . bin2hex($user['role']) . ")\n";
    echo "SubRole: " . $user['sub_role'] . " (" . bin2hex($user['sub_role']) . ")\n";

    // Check comparison
    $is_role_ok = ($user['role'] === 'super_admin');
    $is_sub_role_ok = ($user['sub_role'] === 'full');
    echo "Role match 'super_admin': " . ($is_role_ok ? "YES" : "NO") . "\n";
    echo "SubRole match 'full': " . ($is_sub_role_ok ? "YES" : "NO") . "\n";
} else {
    echo "User ID 1 not found!\n";
}
