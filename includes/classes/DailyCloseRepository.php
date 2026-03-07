<?php
// includes/classes/DailyCloseRepository.php

class DailyCloseRepository extends BaseRepository
{
    /**
     * STEP 1: Identification of ANY active leftover items that must be cleared/trashed.
     * We look for EVERYTHING currently active in 'Momsi' or 'Leftover' state 
     * to ensure a fresh start.
     */
    public function getActiveMomsiStock()
    {
        // Find ALL active Momsi records regardless of date
        $sql = "SELECT id, qat_type_id, quantity_kg, purchase_date FROM purchases 
                WHERE status = 'Momsi'";
        return $this->fetchAll($sql);
    }

    public function getActiveManualLeftovers()
    {
        // Find ALL active manual leftovers regardless of date
        $sql = "SELECT id, purchase_id, qat_type_id, weight_kg, source_date 
                FROM leftovers 
                WHERE status IN ('Transferred_Next_Day', 'Auto_Momsi')";
        return $this->fetchAll($sql);
    }

    public function trashLeftover($leftoverId, $currentDate)
    {
        // Get details for waste recording
        $stmt = $this->pdo->prepare("SELECT * FROM leftovers WHERE id = ?");
        $stmt->execute([$leftoverId]);
        $l = $stmt->fetch();
        if (!$l) return;

        // Calculate sold weight
        $sold = $this->fetchColumn("SELECT SUM(weight_kg) FROM sales WHERE leftover_id = ?", [$leftoverId]) ?: 0;
        $surplus = (float)$l['weight_kg'] - (float)$sold;

        if ($surplus > 0.001) {
            // Record as Waste (Auto_Dropped)
            $sql = "INSERT INTO leftovers (source_date, purchase_id, qat_type_id, weight_kg, status, decision_date, sale_date) 
                    VALUES (?, ?, ?, ?, 'Auto_Dropped', ?, ?)";
            $this->pdo->prepare($sql)->execute([$l['source_date'], $l['purchase_id'], $l['qat_type_id'], $surplus, $currentDate, $currentDate]);
        }

        // Close the record
        return $this->pdo->prepare("UPDATE leftovers SET status = 'Dropped' WHERE id = ?")->execute([$leftoverId]);
    }

    public function trashMomsiPurchase($purchaseId, $currentDate)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM purchases WHERE id = ?");
        $stmt->execute([$purchaseId]);
        $p = $stmt->fetch();
        if (!$p) return;

        // Calculate sold/managed weights
        $stmtW = $this->pdo->prepare("SELECT 
            (SELECT SUM(weight_kg) FROM sales WHERE purchase_id = ?) as sold,
            (SELECT SUM(weight_kg) FROM leftovers WHERE purchase_id = ? AND status IN ('Dropped', 'Transferred_Next_Day')) as managed");
        $stmtW->execute([$purchaseId, $purchaseId]);
        $row = $stmtW->fetch();

        $sold = $row['sold'] ?: 0;
        $managed = $row['managed'] ?: 0;
        $surplus = (float)$p['quantity_kg'] - (float)$sold - (float)$managed;

        if ($surplus > 0.001) {
            // Record as Waste
            $sql = "INSERT INTO leftovers (source_date, purchase_id, qat_type_id, weight_kg, status, decision_date, sale_date) 
                    VALUES (?, ?, ?, ?, 'Auto_Dropped', ?, ?)";
            $this->pdo->prepare($sql)->execute([$p['purchase_date'], $purchaseId, $p['qat_type_id'], $surplus, $currentDate, $currentDate]);
        }

        // Close it
        return $this->pdo->prepare("UPDATE purchases SET status = 'Closed' WHERE id = ?")->execute([$purchaseId]);
    }

    /**
     * STEP 2: Moving today's surplus Fresh stock.
     */
    public function getDayFreshStock($currentDate)
    {
        $stmt = $this->pdo->prepare("SELECT id, qat_type_id, quantity_kg FROM purchases 
                                    WHERE purchase_date = ? AND status = 'Fresh'");
        $stmt->execute([$currentDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSoldAndManagedWeightForPurchase($purchaseId)
    {
        $stmt = $this->pdo->prepare("SELECT 
            (SELECT SUM(weight_kg) FROM sales WHERE purchase_id = ?) as sold,
            (SELECT SUM(weight_kg) FROM leftovers WHERE purchase_id = ? AND status IN ('Dropped', 'Transferred_Next_Day')) as managed");
        $stmt->execute([$purchaseId, $purchaseId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'sold' => $row['sold'] ?: 0,
            'managed' => $row['managed'] ?: 0
        ];
    }

    public function moveStockToTomorrow($purchaseId, $surplus, $currentDate, $tomorrow)
    {
        // 1. Create Momsi entry in purchases
        $sqlP = "INSERT INTO purchases (purchase_date, qat_type_id, quantity_kg, received_weight_grams, is_received, status, received_at, provider_id, original_purchase_id)
                 SELECT ?, qat_type_id, ?, ?, 1, 'Momsi', ?, provider_id, id 
                 FROM purchases WHERE id = ?";
        $this->pdo->prepare($sqlP)->execute([$tomorrow, $surplus, $surplus * 1000, $tomorrow . ' 00:00:01', $purchaseId]);

        // 2. Create entry in leftovers table
        $sqlL = "INSERT INTO leftovers (source_date, purchase_id, qat_type_id, weight_kg, status, decision_date, sale_date) 
                 VALUES (?, ?, (SELECT qat_type_id FROM purchases WHERE id = ?), ?, 'Auto_Momsi', ?, ?)";
        $this->pdo->prepare($sqlL)->execute([$currentDate, $purchaseId, $purchaseId, $surplus, $currentDate, $tomorrow]);

        // 3. Close original
        $this->pdo->prepare("UPDATE purchases SET status = 'Closed' WHERE id = ?")->execute([$purchaseId]);
    }

    /**
     * STEP 3: Debt Rollover
     */
    public function migrateDailyDebts($currentDate, $tomorrow)
    {
        $sql = "UPDATE sales
                SET debt_type = 'Deferred', 
                    due_date = ?,
                    notes = CONCAT(IFNULL(notes, ''), ' [ترحيل آلي من ', ?, ']')
                WHERE due_date <= ?
                AND payment_method = 'Debt'
                AND debt_type = 'Daily'
                AND is_paid = 0";
        return $this->pdo->prepare($sql)->execute([$tomorrow, $currentDate, $currentDate]);
    }

    public function closePurchase($purchaseId)
    {
        return $this->pdo->prepare("UPDATE purchases SET status = 'Closed' WHERE id = ?")->execute([$purchaseId]);
    }
}
