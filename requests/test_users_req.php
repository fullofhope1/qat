<?php
session_start();
$_SESSION = ["user_id" => 1, "username" => "super admin", "role" => "super_admin", "sub_role" => "full"];
$_GET['action'] = 'list';
require 'manage_users.php';
