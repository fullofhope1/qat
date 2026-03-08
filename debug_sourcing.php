<?php
// debug_sourcing.php - Run this to diagnose the missing shipment issue
require_once 'config/db.php';
session_start();

echo "<h2>🔍 Sourcing Debug Report</h2>";

// 1. Check migration status
echo "<h3>1. Migration Status (New Columns)</h3>";
$columns = ['unit_type', 'source_units', 'price_per_unit'];
foreach ($columns as $col) {
    $result = $pdo->query("SHOW COLUMNS FROM purchases LIKE '$col'")->fetch();
    if ($result) {
        echo "<p style='color:green'>✅ Column <b>$col</b> exists in purchases</p>";
    } else {
        echo "<p style='color:red'>❌ Column <b>$col</b> MISSING — run migration_unit_mode.php first!</p>";
    }
}

// 2. Check current date/time
echo "<h3>2. Date & Time</h3>";
$phpDate = date('Y-m-d H:i:s');
$dbDate = $pdo->query("SELECT NOW() as now, CURDATE() as today")->fetch();
echo "<p>PHP date: <b>$phpDate</b></p>";
echo "<p>DB NOW(): <b>{$dbDate['now']}</b></p>";
echo "<p>DB CURDATE(): <b>{$dbDate['today']}</b></p>";

// 3. Check session
echo "<h3>3. Session</h3>";
$userId = $_SESSION['user_id'] ?? 'NOT SET';
echo "<p>Logged in user_id: <b>$userId</b></p>";

// 4. All recent purchases
echo "<h3>4. Last 5 Purchases in DB (any user, any date)</h3>";
$rows = $pdo->query("SELECT id, purchase_date, created_by, source_weight_grams, unit_type, source_units, is_received, created_at FROM purchases ORDER BY id DESC LIMIT 5")->fetchAll();
if ($rows) {
    echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>purchase_date</th><th>created_by</th><th>weight_g</th><th>unit_type</th><th>source_units</th><th>is_received</th><th>created_at</th></tr>";
    foreach ($rows as $r) {
        echo "<tr><td>{$r['id']}</td><td>{$r['purchase_date']}</td><td>{$r['created_by']}</td><td>{$r['source_weight_grams']}</td><td>{$r['unit_type']}</td><td>{$r['source_units']}</td><td>{$r['is_received']}</td><td>{$r['created_at']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red'>❌ No purchases found at all in the DB!</p>";
}

// 5. Today's purchases for current user
echo "<h3>5. Today's Purchases for User $userId</h3>";
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT id, purchase_date, created_by FROM purchases WHERE purchase_date = ? AND created_by = ?");
$stmt->execute([$today, $userId]);
$rows = $stmt->fetchAll();
echo "<p>Searching for: purchase_date = <b>$today</b>, created_by = <b>$userId</b></p>";
if ($rows) {
    echo "<p style='color:green'>✅ Found " . count($rows) . " record(s) for today and this user.</p>";
} else {
    echo "<p style='color:red'>❌ No records found for today and user $userId.</p>";
}
