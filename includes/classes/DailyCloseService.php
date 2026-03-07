<?php
// includes/classes/DailyCloseService.php

class DailyCloseService extends BaseService
{
    private $repository;

    public function __construct(DailyCloseRepository $repository)
    {
        $this->repository = $repository;
    }

    public function closeDay($currentDate)
    {
        $tomorrow = date('Y-m-d', strtotime($currentDate . ' +1 day'));

        try {
            $this->repository->beginTransaction();

            // --- STEP 1: CLEAN OLD LEFTOVERS (THE EMPTY BUCKET) ---
            // Identify EVERYTHING in 'Momsi' or active 'Leftover' state and trash it.

            // 1a. Handle manual/auto leftovers in leftovers table
            $staleLeftovers = $this->repository->getActiveManualLeftovers();
            foreach ($staleLeftovers as $l) {
                $this->repository->trashLeftover($l['id'], $currentDate);
            }

            // 1b. Handle Momsi purchases in purchases table
            $stalePurchases = $this->repository->getActiveMomsiStock();
            foreach ($stalePurchases as $p) {
                $this->repository->trashMomsiPurchase($p['id'], $currentDate);
            }

            // --- STEP 2: MOVE TODAY'S SALES (SURPLUS) ---
            // Identify today's Fresh stock and move remaining quantity to tomorrow.
            $dayFresh = $this->repository->getDayFreshStock($currentDate);
            foreach ($dayFresh as $p) {
                $weights = $this->repository->getSoldAndManagedWeightForPurchase($p['id']);
                $surplus = (float)$p['quantity_kg'] - (float)$weights['sold'] - (float)$weights['managed'];

                if ($surplus > 0.001) {
                    $this->repository->moveStockToTomorrow($p['id'], $surplus, $currentDate, $tomorrow);
                } else {
                    $this->repository->closePurchase($p['id'] ?? $p['purchase_id']);
                }
            }

            // --- STEP 3: MIGRATE DAILY DEBTS ---
            $this->repository->migrateDailyDebts($currentDate, $tomorrow);

            $this->repository->commit();
            return true;
        } catch (Exception $e) {
            if ($this->repository->inTransaction()) {
                $this->repository->rollBack();
            }
            throw $e;
        }
    }
}
