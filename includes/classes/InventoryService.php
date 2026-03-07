<?php
// includes/classes/InventoryService.php

class InventoryService extends BaseService
{
    protected $productRepo;
    protected $adRepo;

    public function __construct(ProductRepository $productRepo, AdRepository $adRepo)
    {
        $this->productRepo = $productRepo;
        $this->adRepo = $adRepo;
    }

    public function getManageProductsData()
    {
        return $this->productRepo->getActiveProductsWithStats();
    }

    public function getManageAdsData()
    {
        return $this->adRepo->getAll();
    }

    public function processProduct($action, $data)
    {
        if ($action === 'add') {
            return $this->productRepo->create($data);
        } elseif ($action === 'update') {
            $id = $data['id'];
            unset($data['id']);
            return $this->productRepo->update($id, $data);
        } elseif ($action === 'delete') {
            return $this->productRepo->delete($data['id']);
        }
        return false;
    }

    public function processAd($action, $data)
    {
        if ($action === 'add') {
            return $this->adRepo->create($data);
        } elseif ($action === 'update') {
            $id = $data['id'];
            unset($data['id']);
            return $this->adRepo->update($id, $data);
        } elseif ($action === 'delete') {
            return $this->adRepo->delete($data['id']);
        }
        return false;
    }

    public function getInventoryDailyDebts()
    {
        return $this->productRepo->getInventoryDailyDebts();
    }

    public function getInventoryPurchaseStats()
    {
        return $this->productRepo->getInventoryPurchaseStats();
    }
}
