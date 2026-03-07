<?php
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'super_admin';
$_SESSION['sub_role'] = 'full';
$_GET['action'] = 'list';

ob_start();
chdir('requests');
require 'manage_users.php';
$output = ob_get_flush(); // This will print it and also return it

echo "\n--- HEX DUMP OF OUTPUT ---\n";
echo bin2hex($output) . "\n";
