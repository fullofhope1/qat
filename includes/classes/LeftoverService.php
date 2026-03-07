<?php
// includes/classes/LeftoverService.php

class LeftoverService extends BaseService
{
    private $leftoverRepo;
    private $purchaseRepo;

    public function __construct(LeftoverRepository $leftoverRepo, PurchaseRepository $purchaseRepo)
    {
        $this->leftoverRepo = $leftoverRepo;
        $this->purchaseRepo = $purchaseRepo;
    }

    public function markAsWaste($purchaseId, $weightKg, $notes = '')
    {
        $this->purchaseRepo->beginTransaction();
        try {
            $purchase = $this->purchaseRepo->getById($purchaseId, true);
            if (!$purchase) {
                throw new Exception("الشحنة غير موجودة");
            }

            // Record as Dropped (Waste)
            $this->leftoverRepo->create([
                'source_date' => date('Y-m-d'),
                'purchase_id' => $purchaseId,
                'qat_type_id' => $purchase['qat_type_id'],
                'weight_kg' => $weightKg,
                'status' => 'Dropped',
                'decision_date' => date('Y-m-d'),
                'sale_date' => date('Y-m-d'),
                'notes' => $notes
            ]);

            $this->purchaseRepo->commit();
            return true;
        } catch (Exception $e) {
            $this->purchaseRepo->rollBack();
            throw $e;
        }
    }

    public function transferToNextDay($purchaseId, $weightKg, $notes = '')
    {
        $this->purchaseRepo->beginTransaction();
        try {
            $purchase = $this->purchaseRepo->getById($purchaseId, true);
            if (!$purchase) {
                throw new Exception("الشحنة غير موجودة");
            }

            // Record as Transferred
            $this->leftoverRepo->create([
                'source_date' => date('Y-m-d'),
                'purchase_id' => $purchaseId,
                'qat_type_id' => $purchase['qat_type_id'],
                'weight_kg' => $weightKg,
                'status' => 'Transferred_Next_Day',
                'decision_date' => date('Y-m-d'),
                'sale_date' => date('Y-m-d', strtotime('+1 day')),
                'notes' => $notes
            ]);

            $this->purchaseRepo->commit();
            return true;
        } catch (Exception $e) {
            $this->purchaseRepo->rollBack();
            throw $e;
        }
    }
}
