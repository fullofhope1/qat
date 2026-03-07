<?php
// includes/classes/UserRepository.php

class UserRepository extends BaseRepository
{
    public function getAllUsers()
    {
        return $this->fetchAll("SELECT id, username, display_name, phone, role, sub_role, created_at FROM users ORDER BY created_at DESC");
    }

    public function getById($id)
    {
        return $this->fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
    }

    public function getByUsername($username)
    {
        return $this->fetchOne("SELECT * FROM users WHERE username = ?", [$username]);
    }

    public function create(array $data)
    {
        $sql = "INSERT INTO users (username, display_name, phone, password, role, sub_role) 
                VALUES (:username, :display_name, :phone, :password, :role, :sub_role)";
        return $this->execute($sql, $data);
    }

    public function update($id, array $data)
    {
        $fields = [];
        foreach ($data as $key => $value) {
            if ($key === 'password' && empty($value)) continue;
            $fields[] = "$key = :$key";
        }
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $data['id'] = $id;

        // Remove empty password if present to prevent overwriting
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        }

        return $this->execute($sql, $data);
    }

    public function delete($id)
    {
        return $this->execute("DELETE FROM users WHERE id = ?", [$id]);
    }
}
