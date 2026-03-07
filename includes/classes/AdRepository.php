<?php
// includes/classes/AdRepository.php

class AdRepository extends BaseRepository
{
    public function getAll()
    {
        return $this->fetchAll("SELECT * FROM advertisements ORDER BY created_at DESC");
    }

    public function create(array $data)
    {
        $sql = "INSERT INTO advertisements (client_name, title, description, media_path, image_url, link_url, status, created_at) 
                VALUES (:client_name, :title, :description, :media_path, :image_url, :link_url, :status, NOW())";
        $this->execute($sql, $data);
        return $this->pdo->lastInsertId();
    }

    public function update($id, array $data)
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $sql = "UPDATE advertisements SET " . implode(', ', $fields) . " WHERE id = :id";
        $data['id'] = $id;
        return $this->execute($sql, $data);
    }

    public function delete($id)
    {
        return $this->execute("DELETE FROM advertisements WHERE id = ?", [$id]);
    }
}
