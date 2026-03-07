<?php
// verify_rollover.php
require_once __DIR__ . '/config/db.php';

function logResult($msg)
{
    echo $msg . PHP_EOL;
}

try {
    // 1. Create a dummy customer
    $pdo->exec("INSERT INTO customers (name, phone, total_debt) VALUES ('Test Customer', '123456789', 1000)");
    $customer_id = $pdo->lastInsertId();
    logResult("✅ Created dummy customer ID: $customer_id");

    // 2. Insert dummy sales (Daily, Monthly, Yearly) with past due dates
    $past_date_1 = date('Y-m-d', strtotime('-5 days'));
    $past_date_2 = date('Y-m-d', strtotime('-10 days'));
    $past_date_3 = date('Y-m-d', strtotime('-15 days'));

    $pdo->prepare("INSERT INTO sales (customer_id, price, paid_amount, is_paid, due_date, debt_type) VALUES (?, 500, 0, 0, ?, 'Daily')")->execute([$customer_id, $past_date_1]);
    $pdo->prepare("INSERT INTO sales (customer_id, price, paid_amount, is_paid, due_date, debt_type) VALUES (?, 300, 0, 0, ?, 'Monthly')")->execute([$customer_id, $past_date_2]);
    $pdo->prepare("INSERT INTO sales (customer_id, price, paid_amount, is_paid, due_date, debt_type) VALUES (?, 200, 0, 0, ?, 'Yearly')")->execute([$customer_id, $past_date_3]);
    logResult("✅ Inserted 3 dummy sales records with past due dates.");

    // 3. Define a helper to simulate process_debt_rollover.php
    function simulateRollover($pdo, $cid, $dtype)
    {
        $customer_id = $cid;
        $debt_type = $dtype;

        switch ($debt_type) {
            case 'Monthly':
                $new_due_date = date('Y-m-d', strtotime('+1 month'));
                break;
            case 'Yearly':
                $new_due_date = date('Y-m-d', strtotime('+1 year'));
                break;
            default: // Daily
                $new_due_date = date('Y-m-d', strtotime('+1 day'));
                break;
        }

        $sql = "UPDATE sales
                SET due_date = :newDate
                WHERE customer_id = :cid
                  AND debt_type = :dtype
                  AND is_paid = 0
                  AND due_date <= CURDATE()";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':newDate' => $new_due_date,
            ':cid'     => $customer_id,
            ':dtype'   => $debt_type
        ]);
        return $new_due_date;
    }

    // 4. Test Daily Rollover
    $expected_daily = date('Y-m-d', strtotime('+1 day'));
    simulateRollover($pdo, $customer_id, 'Daily');
    $res = $pdo->query("SELECT due_date FROM sales WHERE customer_id = $customer_id AND debt_type = 'Daily'")->fetch();
    if ($res['due_date'] === $expected_daily) {
        logResult("✅ Daily Rollover Success: New due date is " . $res['due_date']);
    } else {
        logResult("❌ Daily Rollover Failed: Expected $expected_daily, got " . $res['due_date']);
    }

    // 5. Test Monthly Rollover
    $expected_monthly = date('Y-m-d', strtotime('+1 month'));
    simulateRollover($pdo, $customer_id, 'Monthly');
    $res = $pdo->query("SELECT due_date FROM sales WHERE customer_id = $customer_id AND debt_type = 'Monthly'")->fetch();
    if ($res['due_date'] === $expected_monthly) {
        logResult("✅ Monthly Rollover Success: New due date is " . $res['due_date']);
    } else {
        logResult("❌ Monthly Rollover Failed: Expected $expected_monthly, got " . $res['due_date']);
    }

    // 6. Test Yearly Rollover
    $expected_yearly = date('Y-m-d', strtotime('+1 year'));
    simulateRollover($pdo, $customer_id, 'Yearly');
    $res = $pdo->query("SELECT due_date FROM sales WHERE customer_id = $customer_id AND debt_type = 'Yearly'")->fetch();
    if ($res['due_date'] === $expected_yearly) {
        logResult("✅ Yearly Rollover Success: New due date is " . $res['due_date']);
    } else {
        logResult("❌ Yearly Rollover Failed: Expected $expected_yearly, got " . $res['due_date']);
    }

    // 7. Cleanup
    $pdo->exec("DELETE FROM sales WHERE customer_id = $customer_id");
    $pdo->exec("DELETE FROM customers WHERE id = $customer_id");
    logResult("✅ Cleanup complete.");
} catch (Exception $e) {
    logResult("❌ Test Error: " . $e->getMessage());
}
