<?php
// includes/classes/ExpenseRepository.php

class ExpenseRepository extends BaseRepository
{

    public function create(array $data)
    {
        $sql = "INSERT INTO expenses (expense_date, description, amount, category, staff_id, created_by) 
                VALUES (:expense_date, :description, :amount, :category, :staff_id, :created_by)";
        return $this->execute($sql, $data);
    }

    public function update($id, array $data)
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $sql = "UPDATE expenses SET " . implode(', ', $fields) . " WHERE id = :id";
        $data['id'] = $id;
        return $this->execute($sql, $data);
    }

    public function delete($id)
    {
        return $this->execute("DELETE FROM expenses WHERE id = ?", [$id]);
    }

    public function getTodayExpenses($date, $userId)
    {
        $sql = "SELECT e.*, s.name as staff_name 
                FROM expenses e 
                LEFT JOIN staff s ON e.staff_id = s.id 
                WHERE expense_date = ? AND e.created_by = ? 
                ORDER BY id DESC";
        return $this->fetchAll($sql, [$date, $userId]);
    }

    public function getTotalStaffWithdrawals($staffId)
    {
        return $this->fetchColumn("SELECT SUM(amount) FROM expenses WHERE staff_id = ? AND category = 'Staff'", [$staffId]) ?: 0;
    }
}
