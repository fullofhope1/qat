<?php
require 'config/db.php';
try {
    $pdo->exec("ALTER TABLE sales MODIFY COLUMN debt_type ENUM('Daily', 'Monthly', 'Yearly', 'Deferred')");
    echo "Success: Added 'Deferred' to sales.debt_type enum.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
