<?php
// includes/classes/PurchaseService.php

class PurchaseService extends BaseService
{
    private $purchaseRepo;
    private $productRepo;

    public function __construct(PurchaseRepository $purchaseRepo, ProductRepository $productRepo)
    {
        $this->purchaseRepo = $purchaseRepo;
        $this->productRepo = $productRepo;
    }

    public function sourceShipment(array $data)
    {
        // Automatic Linking: Find or Create the Type
        $type = $this->productRepo->getByName($data['type_name']);
        if ($type) {
            $data['qat_type_id'] = $type['id'];
        } else {
            $data['qat_type_id'] = $this->productRepo->create([
                'name' => $data['type_name'],
                'description' => 'Auto-created from sourcing',
                'media_path' => $data['media_path'] ?? null
            ]);
        }

        $unitType = $data['unit_type'] ?? 'weight';

        if ($unitType !== 'weight') {
            // Unit mode (قبضة / قرطاس): received immediately — no shipping step
            $units = (int)($data['source_units'] ?? 0);
            $pricePerUnit = (float)($data['price_per_unit'] ?? 0);
            $data['source_weight_grams']  = 0;
            $data['received_weight_grams'] = 0;
            $data['price_per_kilo']       = 0;
            $data['agreed_price']         = $units * $pricePerUnit;
            $data['quantity_kg']          = $units; // units count stored in quantity_kg for inventory
            $data['is_received']          = 1;      // immediately in stock
            $data['received_at']          = date('Y-m-d H:i:s');
        } else {
            // Weight mode: goes through a separate receiving step
            $weightKg = (float)$data['source_weight_grams'] / 1000;
            $data['agreed_price']  = $weightKg * (float)$data['price_per_kilo'];
            $data['quantity_kg']   = 0; // Not received yet
            $data['source_units']  = 0;
            $data['price_per_unit'] = 0;
            $data['is_received']   = 0;
            $data['received_at']   = null;
            $data['received_weight_grams'] = 0;
        }

        $data['status'] = 'Fresh';

        // Clean up data for repo
        unset($data['type_name']);

        return $this->purchaseRepo->create($data);
    }

    public function receiveShipment($id, $receivedWeightGrams)
    {
        $quantityKg = (float)$receivedWeightGrams / 1000;

        $this->purchaseRepo->beginTransaction();
        try {
            $purchase = $this->purchaseRepo->getById($id, true);
            if (!$purchase) {
                throw new Exception("الشحنة غير موجودة");
            }

            $sourceKg = (float)$purchase['source_weight_grams'] / 1000;
            $lossKg = $sourceKg - $quantityKg;

            $this->purchaseRepo->update($id, [
                'received_weight_grams' => $receivedWeightGrams,
                'quantity_kg' => $quantityKg,
                'is_received' => 1,
                'received_at' => date('Y-m-d H:i:s'),
                'purchase_date' => date('Y-m-d')
            ]);

            // Record loss if significant
            if ($lossKg > 0.001) {
                $this->purchaseRepo->recordReceptionLoss($id, $purchase['qat_type_id'], $lossKg, date('Y-m-d'));
            }

            // Sync media to product display
            if ($purchase['media_path']) {
                $this->productRepo->update($purchase['qat_type_id'], [
                    'media_path' => $purchase['media_path']
                ]);
            }

            $this->purchaseRepo->commit();
        } catch (Exception $e) {
            $this->purchaseRepo->rollBack();
            throw $e;
        }
    }

    public function getPending()
    {
        return $this->purchaseRepo->getPendingShipments();
    }

    public function addPurchase(array $data)
    {
        $data['is_received'] = 1; // Direct fresh purchase is usually received
        $data['source_weight_grams'] = $data['source_weight_grams'] ?? ($data['quantity_kg'] * 1000);
        $data['received_weight_grams'] = $data['received_weight_grams'] ?? $data['source_weight_grams'];
        $data['received_at'] = $data['received_at'] ?? date('Y-m-d H:i:s');
        $data['unit_type'] = $data['unit_type'] ?? 'weight';
        $data['source_units'] = $data['source_units'] ?? 0;
        $data['price_per_unit'] = $data['price_per_unit'] ?? 0;
        return $this->purchaseRepo->create($data);
    }

    public function getReceivedToday($date)
    {
        return $this->purchaseRepo->getTodayReceived($date);
    }
}
