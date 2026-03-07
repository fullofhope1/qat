<?php
// includes/classes/DepositRepository.php

class DepositRepository extends BaseRepository
{

    public function create(array $data)
    {
        $sql = "INSERT INTO qat_deposits (deposit_date, currency, recipient, amount, notes, created_by) 
                VALUES (:deposit_date, :currency, :recipient, :amount, :notes, :created_by)";
        return $this->execute($sql, $data);
    }

    public function update($id, array $data)
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $sql = "UPDATE qat_deposits SET " . implode(', ', $fields) . " WHERE id = :id";
        $data['id'] = $id;
        return $this->execute($sql, $data);
    }

    public function delete($id)
    {
        return $this->execute("DELETE FROM qat_deposits WHERE id = ?", [$id]);
    }

    public function getTodayDeposits($date, $userId)
    {
        $sql = "SELECT * FROM qat_deposits WHERE deposit_date = ? AND created_by = ? ORDER BY id DESC";
        return $this->fetchAll($sql, [$date, $userId]);
    }
}
