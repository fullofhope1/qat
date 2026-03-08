<?php
// includes/classes/SaleRepository.php

class SaleRepository extends BaseRepository
{

    public function create(array $data)
    {
        // Ensure unit-mode fields have defaults so existing callers don't break
        $data['unit_type']      = $data['unit_type'] ?? 'weight';
        $data['quantity_units'] = $data['quantity_units'] ?? 0;
        // due_date defaults to sale_date (avoid duplicate :sale_date placeholder)
        $data['due_date'] = $data['due_date'] ?? $data['sale_date'];

        $sql = "INSERT INTO sales (
            sale_date, due_date, customer_id, qat_type_id, purchase_id, leftover_id, 
            qat_status, weight_grams, weight_kg, unit_type, quantity_units, price, payment_method, is_paid, 
            transfer_sender, transfer_receiver, transfer_number, transfer_company, 
            debt_type, notes
        ) VALUES (
            :sale_date, :due_date, :customer_id, :qat_type_id, :purchase_id, :leftover_id, 
            :qat_status, :weight_grams, :weight_kg, :unit_type, :quantity_units, :price, :payment_method, :is_paid, 
            :transfer_sender, :transfer_receiver, :transfer_number, :transfer_company, 
            :debt_type, :notes
        )";

        $this->execute($sql, $data);
        return $this->pdo->lastInsertId();
    }

    public function getSoldKgByPurchaseId($purchaseId)
    {
        return $this->fetchColumn("SELECT SUM(weight_kg) FROM sales WHERE purchase_id = ? AND unit_type = 'weight'", [$purchaseId]) ?: 0;
    }

    public function getSoldUnitsByPurchaseId($purchaseId)
    {
        return $this->fetchColumn("SELECT SUM(quantity_units) FROM sales WHERE purchase_id = ? AND unit_type != 'weight'", [$purchaseId]) ?: 0;
    }

    public function getSoldKgByLeftoverId($leftoverId)
    {
        return $this->fetchColumn("SELECT SUM(weight_kg) FROM sales WHERE leftover_id = ? AND unit_type = 'weight'", [$leftoverId]) ?: 0;
    }

    public function getSoldUnitsByLeftoverId($leftoverId)
    {
        return $this->fetchColumn("SELECT SUM(quantity_units) FROM sales WHERE leftover_id = ? AND unit_type != 'weight'", [$leftoverId]) ?: 0;
    }

    public function getTodaySalesMapByPurchase($date)
    {
        $sql = "SELECT purchase_id, SUM(weight_kg) as sold_kg 
                FROM sales 
                WHERE sale_date = ? AND purchase_id IS NOT NULL 
                GROUP BY purchase_id";
        return $this->pdo->prepare($sql)->execute([$date]) ? $this->pdo->prepare($sql)->fetchAll(PDO::FETCH_KEY_PAIR) : [];
    }

    // Improved fetch key pair helper needed in BaseRepository or handled here
    public function getSalesMap($date)
    {
        $stmt = $this->pdo->prepare("SELECT purchase_id, SUM(weight_kg) as sold_kg FROM sales WHERE sale_date = ? AND purchase_id IS NOT NULL GROUP BY purchase_id");
        $stmt->execute([$date]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
