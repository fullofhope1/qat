<?php
// includes/classes/ProviderRepository.php

class ProviderRepository extends BaseRepository
{

    public function getAll()
    {
        return $this->fetchAll("SELECT * FROM providers ORDER BY name ASC");
    }

    public function getAllByUserId($userId)
    {
        return $this->fetchAll("SELECT * FROM providers WHERE created_by = ? ORDER BY name ASC", [$userId]);
    }

    public function getById($id)
    {
        return $this->fetchOne("SELECT * FROM providers WHERE id = ?", [$id]);
    }

    public function getByName($name)
    {
        return $this->fetchOne("SELECT id FROM providers WHERE name = ?", [$name]);
    }

    public function getByPhone($phone)
    {
        return $this->fetchOne("SELECT id FROM providers WHERE phone = ?", [$phone]);
    }

    public function create($name, $phone, $userId)
    {
        $sql = "INSERT INTO providers (name, phone, created_by) VALUES (?, ?, ?)";
        $this->execute($sql, [$name, $phone, $userId]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $name, $phone)
    {
        $sql = "UPDATE providers SET name = ?, phone = ? WHERE id = ?";
        return $this->execute($sql, [$name, $phone, $id]);
    }

    public function countPurchasesByProviderId($id)
    {
        return $this->fetchColumn("SELECT COUNT(*) FROM purchases WHERE provider_id = ?", [$id]);
    }

    public function getWithSales()
    {
        $sql = "SELECT DISTINCT prov.id, prov.name 
                FROM providers prov 
                JOIN purchases p ON prov.id = p.provider_id 
                JOIN sales s ON p.id = s.purchase_id 
                ORDER BY prov.name";
        return $this->fetchAll($sql);
    }

    public function delete($id)
    {
        return $this->execute("DELETE FROM providers WHERE id = ?", [$id]);
    }
}
