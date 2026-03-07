<?php
require 'config/db.php';
$stmt = $pdo->query("SELECT id, username, role, sub_role FROM users");
$output = "";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $output .= "ID: {$row['id']} | User: {$row['username']} | Role: {$row['role']} | SubRole: {$row['sub_role']}\n";
}
file_put_contents('audit_results.txt', $output);
echo "Audit complete. Results in audit_results.txt\n";
