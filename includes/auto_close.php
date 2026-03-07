<?php
require_once __DIR__ . '/Autoloader.php';

/**
 * Automatically closes all days prior to (or including) a target date.
 * If no target date is provided, it closes everything up to Yesterday.
 */
function trigger_auto_closing($pdo, $targetDate = null)
{
    $limitDate = $targetDate ?: date('Y-m-d', strtotime('-1 day'));

    // Find the oldest date that has unclosed activity (up to $limitDate)
    // Using COALESCE to handle NULL purchase_date/sale_date by falling back to created_at
    $stmt = $pdo->prepare("SELECT MIN(d) FROM (
        SELECT MIN(COALESCE(purchase_date, DATE(created_at))) as d FROM purchases WHERE status IN ('Fresh', 'Momsi') AND (purchase_date <= ? OR (purchase_date IS NULL AND DATE(created_at) <= ?))
        UNION
        SELECT MIN(COALESCE(sale_date, DATE(created_at))) as d FROM sales WHERE payment_method = 'Debt' AND debt_type = 'Daily' AND is_paid = 0 AND (sale_date <= ? OR (sale_date IS NULL AND DATE(created_at) <= ?))
        UNION
        SELECT MIN(sale_date) as d FROM leftovers WHERE status = 'Transferred_Next_Day' AND sale_date <= ?
    ) as unclosed_dates");
    $stmt->execute([$limitDate, $limitDate, $limitDate, $limitDate, $limitDate]);

    $oldest_unclosed = $stmt->fetchColumn();

    if (!$oldest_unclosed || $oldest_unclosed > $limitDate) {
        return; // Everything is up to date relative to the limit
    }

    $current = $oldest_unclosed;
    while ($current <= $limitDate) {
        /**
         * Clean Architecture Wrapper for Daily Closing.
         * This function maintains backward compatibility while using the new isolated logical layer.
         */
        try {
            $repository = new DailyCloseRepository($pdo);
            $service = new DailyCloseService($repository);
            $service->closeDay($current);
        } catch (Exception $e) {
            error_log("Auto-close failed for date $current: " . $e->getMessage());
            throw $e;
        }

        $current = date('Y-m-d', strtotime($current . ' +1 day'));
    }
}
