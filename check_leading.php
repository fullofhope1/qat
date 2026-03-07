<?php
$files = ['config/db.php', 'requests/manage_users.php'];
foreach ($files as $file) {
    $content = file_get_contents($file);
    echo "Checking $file...\n";
    if (strpos($content, '<?php') !== 0) {
        echo "WARNING: $file does NOT start with <?php. It starts with: " . bin2hex(substr($content, 0, 10)) . "\n";
    } else {
        echo "OK: $file starts with <?php\n";
    }
}
