<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'super_admin';
$_SESSION['sub_role'] = 'full';
$_GET['action'] = 'list';
ob_start();
require 'manage_users.php';
$output = ob_get_clean();
echo "START_OUTPUT\n";
echo $output;
echo "\nEND_OUTPUT\n";
