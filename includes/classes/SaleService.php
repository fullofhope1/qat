<?php
// includes/classes/SaleService.php

class SaleService extends BaseService
{
    private $saleRepo;
    private $purchaseRepo;
    private $customerRepo;
    private $leftoverRepo;

    public function __construct(
        SaleRepository $saleRepo,
        PurchaseRepository $purchaseRepo,
        CustomerRepository $customerRepo,
        LeftoverRepository $leftoverRepo
    ) {
        $this->saleRepo = $saleRepo;
        $this->purchaseRepo = $purchaseRepo;
        $this->customerRepo = $customerRepo;
        $this->leftoverRepo = $leftoverRepo;
    }

    public function getTodaysStock($date)
    {
        $stock = $this->purchaseRepo->getFreshStockByDate($date);
        $salesMap = $this->saleRepo->getSalesMap($date);

        foreach ($stock as &$item) {
            $sold = $salesMap[$item['id']] ?? 0;
            $unitType = $item['unit_type'] ?? 'weight';

            if ($unitType === 'weight') {
                $item['remaining_kg'] = round($item['quantity_kg'] - $sold, 3);
            } else {
                $item['remaining_units'] = (int)$item['quantity_kg'] - (int)$sold;
                $item['remaining_kg'] = 0; // Or keep for compatibility if needed
            }
        }
        return $stock;
    }

    public function processSale(array $data)
    {
        $this->saleRepo->beginTransaction();
        try {
            $unitType = $data['unit_type'] ?? 'weight';
            $isUnitMode = $unitType !== 'weight';

            if ($isUnitMode) {
                $quantityUnits = (int)($data['quantity_units'] ?? 0);
                $data['weight_grams'] = 0;
                $data['weight_kg'] = 0;
            } else {
                $weightKg = (float)$data['weight_grams'] / 1000;
                $data['weight_kg'] = $weightKg;
                $quantityUnits = 0;
            }

            // 1. Inventory Check
            if (!empty($data['purchase_id'])) {
                $totalPurchased = $this->purchaseRepo->getStockQuantity($data['purchase_id'], true);

                if ($isUnitMode) {
                    $totalSold = $this->saleRepo->getSoldUnitsByPurchaseId($data['purchase_id']);
                    $available = (int)$totalPurchased - (int)$totalSold;
                    if ($quantityUnits > $available) {
                        throw new Exception("InventoryExceeded|{$available} Units|{$quantityUnits} Units");
                    }
                } else {
                    $totalSold = $this->saleRepo->getSoldKgByPurchaseId($data['purchase_id']);
                    $available = round($totalPurchased - $totalSold, 3);
                    if ($weightKg > $available) {
                        throw new Exception("InventoryExceeded|{$available}kg|{$weightKg}kg");
                    }
                }
            } elseif (!empty($data['leftover_id'])) {
                $totalLeftover = $this->leftoverRepo->getWeight($data['leftover_id'], true);

                if ($isUnitMode) {
                    $totalSold = $this->saleRepo->getSoldUnitsByLeftoverId($data['leftover_id']);
                    $available = (int)$totalLeftover - (int)$totalSold;
                    if ($quantityUnits > $available) {
                        throw new Exception("LeftoverExceeded|{$available} Units|{$quantityUnits} Units");
                    }
                } else {
                    $totalSold = $this->saleRepo->getSoldKgByLeftoverId($data['leftover_id']);
                    $available = round($totalLeftover - $totalSold, 3);
                    if ($weightKg > $available) {
                        throw new Exception("LeftoverExceeded|{$available}kg|{$weightKg}kg");
                    }
                }
            }

            // 2. Credit Limit Check
            if ($data['payment_method'] === 'Debt' && !empty($data['customer_id'])) {
                $cust = $this->customerRepo->getById($data['customer_id']); // getById should probably support locking if needed, but we can do it via repository
                // BaseRepository.fetchOne doesn't support lock easily unless passed in SQL.
                // Let's assume CustomerRepository.getById is fine for now, or add a locked version.

                if ($cust) {
                    $newDebt = (float)$cust['total_debt'] + (float)$data['price'];
                    if ($cust['debt_limit'] !== null && $newDebt > $cust['debt_limit']) {
                        throw new Exception("CreditLimitExceeded|{$cust['debt_limit']}|{$cust['total_debt']}");
                    }
                }
            }

            // 3. Create Sale
            $saleId = $this->saleRepo->create($data);

            // 4. Update Customer Debt
            if ($data['payment_method'] === 'Debt' && !empty($data['customer_id'])) {
                $this->customerRepo->incrementDebt($data['customer_id'], $data['price']);
            }

            $this->saleRepo->commit();
            return $saleId;
        } catch (Exception $e) {
            $this->saleRepo->rollBack();
            throw $e;
        }
    }
}
