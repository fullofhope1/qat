<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'super_admin';
$_SESSION['sub_role'] = 'full';
$_GET['action'] = 'list';

ob_start();
require __DIR__ . '/requests/manage_users.php';
$out = ob_get_clean();
echo "START_OUTPUT\n";
var_dump($out);
echo "\nEND_OUTPUT\n";
