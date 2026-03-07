<?php
// includes/classes/RefundRepository.php

class RefundRepository extends BaseRepository
{
    public function getRecentRefunds($limit = 50)
    {
        $limit = (int)$limit;
        $sql = "SELECT r.*, c.name as cust_name 
                FROM refunds r 
                LEFT JOIN customers c ON r.customer_id = c.id 
                ORDER BY r.id DESC LIMIT $limit";
        return $this->fetchAll($sql);
    }

    public function create($data)
    {
        $sql = "INSERT INTO refunds (customer_id, amount, refund_type, reason, refund_date) 
                VALUES (?, ?, ?, ?, NOW())";
        return $this->execute($sql, [
            $data['customer_id'],
            $data['amount'],
            $data['refund_type'],
            $data['reason']
        ]);
    }

    public function getRefundsByPeriod($where, $params)
    {
        $sql = "SELECT r.*, c.name as cust_name FROM refunds r LEFT JOIN customers c ON r.customer_id = c.id $where ORDER BY r.id DESC";
        return $this->fetchAll($sql, $params);
    }
}
