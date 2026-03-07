<?php
// includes/classes/PurchaseRepository.php

class PurchaseRepository extends BaseRepository
{

    public function getById($id, $lock = false)
    {
        $sql = "SELECT * FROM purchases WHERE id = ?";
        if ($lock) {
            $sql .= " FOR UPDATE";
        }
        return $this->fetchOne($sql, [$id]);
    }

    public function create(array $data)
    {
        $sql = "INSERT INTO purchases (
            purchase_date, provider_id, qat_type_id, source_weight_grams, 
            quantity_kg, price_per_kilo, agreed_price, is_received, 
            status, media_path, created_by
        ) VALUES (
            :purchase_date, :provider_id, :qat_type_id, :source_weight_grams, 
            :quantity_kg, :price_per_kilo, :agreed_price, :is_received, 
            :status, :media_path, :created_by
        )";
        return $this->execute($sql, $data);
    }

    public function update($id, array $data)
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $sql = "UPDATE purchases SET " . implode(', ', $fields) . " WHERE id = :id";
        $data['id'] = $id;
        return $this->execute($sql, $data);
    }

    public function getPendingShipments()
    {
        $sql = "SELECT p.*, t.name as type_name, prov.name as provider_name 
                FROM purchases p 
                LEFT JOIN qat_types t ON p.qat_type_id = t.id 
                LEFT JOIN providers prov ON p.provider_id = prov.id 
                WHERE p.is_received = 0 
                ORDER BY p.created_at ASC";
        return $this->fetchAll($sql);
    }

    public function getTodayShipmentsByUserId($date, $userId)
    {
        $sql = "SELECT p.*, t.name as type_name, prov.name as provider_name 
                FROM purchases p 
                LEFT JOIN qat_types t ON p.qat_type_id = t.id 
                LEFT JOIN providers prov ON p.provider_id = prov.id 
                WHERE p.purchase_date = ? AND p.created_by = ?
                ORDER BY p.created_at DESC";
        return $this->fetchAll($sql, [$date, $userId]);
    }

    public function getTodayReceived($date)
    {
        $sql = "SELECT p.*, t.name as type_name, prov.name as provider_name 
                FROM purchases p 
                LEFT JOIN qat_types t ON p.qat_type_id = t.id 
                LEFT JOIN providers prov ON p.provider_id = prov.id 
                WHERE p.is_received = 1 AND DATE(p.received_at) = ? 
                ORDER BY p.received_at DESC";
        return $this->fetchAll($sql, [$date]);
    }

    public function getFreshStockByDate($date)
    {
        $sql = "SELECT p.*, prov.name as provider_name 
                FROM purchases p 
                JOIN providers prov ON p.provider_id = prov.id 
                WHERE p.purchase_date = ? 
                AND p.status IN ('Fresh', 'Momsi')
                AND p.is_received = 1";
        return $this->fetchAll($sql, [$date]);
    }

    public function getStockQuantity($id, $lock = false)
    {
        $sql = "SELECT quantity_kg FROM purchases WHERE id = ?";
        if ($lock) {
            $sql .= " FOR UPDATE";
        }
        return $this->fetchColumn($sql, [$id]) ?: 0;
    }

    public function recordReceptionLoss($purchaseId, $typeId, $weight, $date)
    {
        $sql = "INSERT INTO leftovers (source_date, purchase_id, qat_type_id, weight_kg, status, decision_date, sale_date) 
                VALUES (?, ?, ?, ?, 'Reception_Loss', ?, ?)";
        return $this->execute($sql, [$date, $purchaseId, $typeId, $weight, $date, $date]);
    }
}
