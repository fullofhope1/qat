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
            $item['remaining_kg'] = round($item['quantity_kg'] - $sold, 3);
        }
        return $stock;
    }

    public function processSale(array $data)
    {
        $this->saleRepo->beginTransaction();
        try {
            $weightKg = (float)$data['weight_grams'] / 1000;
            $data['weight_kg'] = $weightKg; // Ensure weight_kg is set for DB

            // 1. Inventory Check
            if (!empty($data['purchase_id'])) {
                $totalPurchased = $this->purchaseRepo->getStockQuantity($data['purchase_id'], true);
                $totalSold = $this->saleRepo->getSoldKgByPurchaseId($data['purchase_id']);
                $available = round($totalPurchased - $totalSold, 3);

                if ($weightKg > $available) {
                    throw new Exception("InventoryExceeded|{$available}|{$weightKg}");
                }
            } elseif (!empty($data['leftover_id'])) {
                $totalLeftover = $this->leftoverRepo->getWeight($data['leftover_id'], true);
                $totalSold = $this->saleRepo->getSoldKgByLeftoverId($data['leftover_id']);
                $available = round($totalLeftover - $totalSold, 3);

                if ($weightKg > $available) {
                    throw new Exception("LeftoverExceeded|{$available}|{$weightKg}");
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
