<?php
// includes/classes/RefundService.php

class RefundService extends BaseService
{
    protected $refundRepo;
    protected $customerRepo;

    public function __construct(RefundRepository $refundRepo, CustomerRepository $customerRepo)
    {
        $this->refundRepo = $refundRepo;
        $this->customerRepo = $customerRepo;
    }

    public function getRefundDashboardData()
    {
        return [
            'recent_refunds' => $this->refundRepo->getRecentRefunds(),
            'customers' => $this->customerRepo->getAllActive()
        ];
    }

    public function processRefund($data)
    {
        $this->refundRepo->beginTransaction();
        try {
            // 1. Create refund record
            $this->refundRepo->create($data);

            // 2. If it's a debt refund, decrement customer debt
            if ($data['refund_type'] === 'Debt') {
                $this->customerRepo->decrementDebt($data['customer_id'], $data['amount']);
            }

            $this->refundRepo->commit();
            return true;
        } catch (Exception $e) {
            $this->refundRepo->rollBack();
            throw $e;
        }
    }
}
