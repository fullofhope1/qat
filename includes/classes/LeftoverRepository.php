<?php
// includes/classes/LeftoverRepository.php

class LeftoverRepository extends BaseRepository
{

    public function getById($id, $lock = false)
    {
        $sql = "SELECT * FROM leftovers WHERE id = ?";
        if ($lock) {
            $sql .= " FOR UPDATE";
        }
        return $this->fetchOne($sql, [$id]);
    }

    public function getWeight($id, $lock = false)
    {
        $sql = "SELECT weight_kg FROM leftovers WHERE id = ?";
        if ($lock) {
            $sql .= " FOR UPDATE";
        }
        return $this->fetchColumn($sql, [$id]) ?: 0;
    }

    public function create(array $data)
    {
        $sql = "INSERT INTO leftovers (source_date, purchase_id, qat_type_id, weight_kg, status, decision_date, sale_date, notes) 
                VALUES (:source_date, :purchase_id, :qat_type_id, :weight_kg, :status, :decision_date, :sale_date, :notes)";
        return $this->execute($sql, $data);
    }
}
