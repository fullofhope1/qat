<?php
require 'c:/xampp/htdocs/qat/config/db.php';
$pdo->exec("UPDATE users SET role = 'admin' WHERE username = 'admin'");
$pdo->exec("UPDATE users SET role = 'super_admin' WHERE username = 'super' OR username = 'superadmin' OR username = 'super_admin'");
echo "Roles corrected";
