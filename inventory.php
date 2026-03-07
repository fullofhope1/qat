<?php
require 'config/db.php';
require 'includes/Autoloader.php';

header('Content-Type: text/plain; charset=utf-8');

$productRepo = new ProductRepository($pdo);
$adRepo = new AdRepository($pdo);
$service = new InventoryService($productRepo, $adRepo);

echo "--- جرد الديون اليومية ---\n";
$dailyDebts = $service->getInventoryDailyDebts();

if (empty($dailyDebts)) {
    echo "لا توجد ديون يومية غير مدفوعة.\n";
} else {
    foreach ($dailyDebts as $r) {
        echo "التاريخ: {$r['sale_date']} | العدد: {$r['count']} | الإجمالي: {$r['total']}\n";
    }
}

echo "\n--- جرد المشتريات ---\n";
$purchases = $service->getInventoryPurchaseStats();
foreach ($purchases as $r) {
    echo "التاريخ: {$r['purchase_date']} | العدد: {$r['count']} | الحالة: {$r['status']}\n";
}
echo "\n=== END ===\n";
