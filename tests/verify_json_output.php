<?php
// Mocking the environment
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'super_admin';
$_SESSION['sub_role'] = 'full';
$_GET['action'] = 'list';

// Prevent actual session start and redirects if any dependencies try them
define('BYPASS_AUTH', true);

ob_start();
// Include the database connection but we might need to mock $pdo if it fails
try {
    require_once 'config/db.php';
} catch (Exception $e) {
    echo "DB Connection failed, skipping live data check.\n";
    exit;
}

// Now require the target script
chdir('requests');
// We need to bypass the session check if we want success: true in a CLI environment without a real session
// But our hardened check uses $_SESSION directly.

require 'manage_users.php';
$output = ob_get_clean();

$data = json_decode($output, true);
if (json_last_error() === JSON_ERROR_NONE) {
    if ($data['success']) {
        echo "VERIFICATION SUCCESS: Valid JSON and Success=True\n";
    } else {
        echo "VERIFICATION PARTIAL: Valid JSON but Error=" . ($data['error'] ?? 'Unknown') . "\n";
    }
} else {
    echo "VERIFICATION FAILED: Invalid JSON output.\n";
    echo "RAW OUTPUT: [" . $output . "]\n";
}
