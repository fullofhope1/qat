<?php
// migration_unit_mode.php - Run ONCE to add unit-based product support
require_once 'config/db.php';

echo "<h2>🚀 Running Unit Mode Migration...</h2>";

$migrations = [
    // purchases table
    "ALTER TABLE `purchases` ADD COLUMN `unit_type` ENUM('weight','قبضة','قرطاس') NOT NULL DEFAULT 'weight' AFTER `price_per_kilo`",
    "ALTER TABLE `purchases` ADD COLUMN `source_units` INT NOT NULL DEFAULT 0 AFTER `unit_type`",
    "ALTER TABLE `purchases` ADD COLUMN `price_per_unit` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `source_units`",

    // leftovers table
    "ALTER TABLE `leftovers` ADD COLUMN `quantity_units` INT NOT NULL DEFAULT 0 AFTER `weight_kg`",

    // sales table
    "ALTER TABLE `sales` ADD COLUMN `unit_type` ENUM('weight','قبضة','قرطاس') NOT NULL DEFAULT 'weight' AFTER `weight_kg`",
    "ALTER TABLE `sales` ADD COLUMN `quantity_units` INT NOT NULL DEFAULT 0 AFTER `unit_type`",
];

$success = 0;
$skipped = 0;

foreach ($migrations as $sql) {
    // Extract column name for display
    preg_match('/ADD COLUMN `(\w+)`/', $sql, $match);
    $colName = $match[1] ?? 'unknown';

    try {
        $pdo->exec($sql);
        echo "<p style='color:green'>✅ Added column: <b>$colName</b></p>";
        $success++;
    } catch (PDOException $e) {
        // Error 1060 = Duplicate column name (already exists)
        if ($e->getCode() == '42S21' || strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "<p style='color:orange'>⚠️ Column <b>$colName</b> already exists — skipped.</p>";
            $skipped++;
        } else {
            echo "<p style='color:red'>❌ Error on <b>$colName</b>: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}

echo "<hr>";
echo "<h3 style='color:green'>✅ Migration complete! Added: $success | Skipped: $skipped</h3>";
echo "<p><a href='sourcing.php'>← Return to Sourcing</a></p>";
