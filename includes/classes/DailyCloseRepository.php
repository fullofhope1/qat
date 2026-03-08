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
        $sql = "SELECT id, purchase_id, qat_type_id, weight_kg, quantity_units, source_date 
                FROM leftovers 
                WHERE status IN ('Transferred_Next_Day', 'Auto_Momsi')";
        return $this->fetchAll($sql);
    }

    public function trashLeftover($leftoverId, $currentDate)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM leftovers WHERE id = ?");
        $stmt->execute([$leftoverId]);
        $l = $stmt->fetch();
        if (!$l) return;

        // Determine mode — unit or weight
        $isUnitMode = !empty($l['quantity_units']) && (int)$l['quantity_units'] > 0;

        if ($isUnitMode) {
            $sold = $this->fetchColumn("SELECT SUM(quantity_units) FROM sales WHERE leftover_id = ?", [$leftoverId]) ?: 0;
            $surplus = (int)$l['quantity_units'] - (int)$sold;
            if ($surplus > 0) {
                $sql = "INSERT INTO leftovers (source_date, purchase_id, qat_type_id, weight_kg, quantity_units, status, decision_date, sale_date) 
                        VALUES (?, ?, ?, 0, ?, 'Auto_Dropped', ?, ?)";
                $this->pdo->prepare($sql)->execute([$l['source_date'], $l['purchase_id'], $l['qat_type_id'], $surplus, $currentDate, $currentDate]);
            }
        } else {
            $sold = $this->fetchColumn("SELECT SUM(weight_kg) FROM sales WHERE leftover_id = ?", [$leftoverId]) ?: 0;
            $surplus = (float)$l['weight_kg'] - (float)$sold;
            if ($surplus > 0.001) {
                $sql = "INSERT INTO leftovers (source_date, purchase_id, qat_type_id, weight_kg, quantity_units, status, decision_date, sale_date) 
                        VALUES (?, ?, ?, ?, 0, 'Auto_Dropped', ?, ?)";
                $this->pdo->prepare($sql)->execute([$l['source_date'], $l['purchase_id'], $l['qat_type_id'], $surplus, $currentDate, $currentDate]);
            }
        }

        return $this->pdo->prepare("UPDATE leftovers SET status = 'Dropped' WHERE id = ?")->execute([$leftoverId]);
    }

    public function trashMomsiPurchase($purchaseId, $currentDate)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM purchases WHERE id = ?");
        $stmt->execute([$purchaseId]);
        $p = $stmt->fetch();
        if (!$p) return;

        $isUnitMode = ($p['unit_type'] ?? 'weight') !== 'weight';

        if ($isUnitMode) {
            $sold = $this->fetchColumn("SELECT SUM(quantity_units) FROM sales WHERE purchase_id = ?", [$purchaseId]) ?: 0;
            $managedUnits = $this->fetchColumn("SELECT SUM(quantity_units) FROM leftovers WHERE purchase_id = ? AND status IN ('Dropped','Transferred_Next_Day')", [$purchaseId]) ?: 0;
            $surplus = (int)$p['quantity_kg'] - (int)$sold - (int)$managedUnits;
            if ($surplus > 0) {
                $sql = "INSERT INTO leftovers (source_date, purchase_id, qat_type_id, weight_kg, quantity_units, status, decision_date, sale_date) 
                        VALUES (?, ?, ?, 0, ?, 'Auto_Dropped', ?, ?)";
                $this->pdo->prepare($sql)->execute([$p['purchase_date'], $purchaseId, $p['qat_type_id'], $surplus, $currentDate, $currentDate]);
            }
        } else {
            $stmtW = $this->pdo->prepare("SELECT 
                (SELECT SUM(weight_kg) FROM sales WHERE purchase_id = ?) as sold,
                (SELECT SUM(weight_kg) FROM leftovers WHERE purchase_id = ? AND status IN ('Dropped', 'Transferred_Next_Day')) as managed");
            $stmtW->execute([$purchaseId, $purchaseId]);
            $row = $stmtW->fetch();
            $surplus = (float)$p['quantity_kg'] - ($row['sold'] ?: 0) - ($row['managed'] ?: 0);
            if ($surplus > 0.001) {
                $sql = "INSERT INTO leftovers (source_date, purchase_id, qat_type_id, weight_kg, quantity_units, status, decision_date, sale_date) 
                        VALUES (?, ?, ?, ?, 0, 'Auto_Dropped', ?, ?)";
                $this->pdo->prepare($sql)->execute([$p['purchase_date'], $purchaseId, $p['qat_type_id'], $surplus, $currentDate, $currentDate]);
            }
        }

        return $this->pdo->prepare("UPDATE purchases SET status = 'Closed' WHERE id = ?")->execute([$purchaseId]);
    }

    /**
     * STEP 2: Moving today's surplus Fresh stock.
     */
    public function getDayFreshStock($currentDate)
    {
        $stmt = $this->pdo->prepare("SELECT id, qat_type_id, quantity_kg, unit_type FROM purchases 
                                    WHERE purchase_date = ? AND status = 'Fresh'");
        $stmt->execute([$currentDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSoldAndManagedWeightForPurchase($purchaseId)
    {
        $stmt = $this->pdo->prepare("SELECT unit_type FROM purchases WHERE id = ?");
        $stmt->execute([$purchaseId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $isUnitMode = ($row['unit_type'] ?? 'weight') !== 'weight';

        if ($isUnitMode) {
            $sold = $this->fetchColumn("SELECT SUM(quantity_units) FROM sales WHERE purchase_id = ?", [$purchaseId]) ?: 0;
            $managed = $this->fetchColumn("SELECT SUM(quantity_units) FROM leftovers WHERE purchase_id = ? AND status IN ('Dropped','Transferred_Next_Day')", [$purchaseId]) ?: 0;
        } else {
            $stmt2 = $this->pdo->prepare("SELECT 
                (SELECT SUM(weight_kg) FROM sales WHERE purchase_id = ?) as sold,
                (SELECT SUM(weight_kg) FROM leftovers WHERE purchase_id = ? AND status IN ('Dropped','Transferred_Next_Day')) as managed");
            $stmt2->execute([$purchaseId, $purchaseId]);
            $r = $stmt2->fetch(PDO::FETCH_ASSOC);
            $sold    = $r['sold'] ?: 0;
            $managed = $r['managed'] ?: 0;
        }

        return ['sold' => $sold, 'managed' => $managed];
    }

    public function moveStockToTomorrow($purchaseId, $surplus, $currentDate, $tomorrow)
    {
        // Fetch original purchase for unit_type context
        $orig = $this->pdo->query("SELECT unit_type, source_units, price_per_unit FROM purchases WHERE id = $purchaseId")->fetch(PDO::FETCH_ASSOC);
        $unitType    = $orig['unit_type'] ?? 'weight';
        $isUnitMode  = $unitType !== 'weight';

        // 1. Create Momsi entry in purchases
        if ($isUnitMode) {
            // Unit mode: surplus is a count; unit_type carried forward
            $sqlP = "INSERT INTO purchases (purchase_date, qat_type_id, quantity_kg, received_weight_grams, is_received, status, received_at, provider_id, original_purchase_id, unit_type, source_units, price_per_unit)
                     SELECT ?, qat_type_id, ?, 0, 1, 'Momsi', ?, provider_id, id, unit_type, ?, price_per_unit 
                     FROM purchases WHERE id = ?";
            $this->pdo->prepare($sqlP)->execute([$tomorrow, $surplus, $tomorrow . ' 00:00:01', (int)$surplus, $purchaseId]);
        } else {
            $sqlP = "INSERT INTO purchases (purchase_date, qat_type_id, quantity_kg, received_weight_grams, is_received, status, received_at, provider_id, original_purchase_id)
                     SELECT ?, qat_type_id, ?, ?, 1, 'Momsi', ?, provider_id, id 
                     FROM purchases WHERE id = ?";
            $this->pdo->prepare($sqlP)->execute([$tomorrow, $surplus, $surplus * 1000, $tomorrow . ' 00:00:01', $purchaseId]);
        }

        // 2. Create entry in leftovers table
        if ($isUnitMode) {
            $sqlL = "INSERT INTO leftovers (source_date, purchase_id, qat_type_id, weight_kg, quantity_units, status, decision_date, sale_date) 
                     VALUES (?, ?, (SELECT qat_type_id FROM purchases WHERE id = ?), 0, ?, 'Auto_Momsi', ?, ?)";
            $this->pdo->prepare($sqlL)->execute([$currentDate, $purchaseId, $purchaseId, (int)$surplus, $currentDate, $tomorrow]);
        } else {
            $sqlL = "INSERT INTO leftovers (source_date, purchase_id, qat_type_id, weight_kg, quantity_units, status, decision_date, sale_date) 
                     VALUES (?, ?, (SELECT qat_type_id FROM purchases WHERE id = ?), ?, 0, 'Auto_Momsi', ?, ?)";
            $this->pdo->prepare($sqlL)->execute([$currentDate, $purchaseId, $purchaseId, $surplus, $currentDate, $tomorrow]);
        }

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
