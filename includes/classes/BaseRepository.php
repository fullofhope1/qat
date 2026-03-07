<?php
// includes/classes/BaseRepository.php

abstract class BaseRepository
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function inTransaction()
    {
        return $this->pdo->inTransaction();
    }

    public function rollBack()
    {
        if ($this->inTransaction()) {
            return $this->pdo->rollBack();
        }
        return false;
    }

    protected function fetchOne($sql, $params = [])
    {
        return $this->prepareAndExecute($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    protected function fetchAll($sql, $params = [])
    {
        return $this->prepareAndExecute($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function fetchColumn($sql, $params = [])
    {
        return $this->prepareAndExecute($sql, $params)->fetchColumn();
    }

    protected function execute($sql, $params = [])
    {
        try {
            return $this->prepareAndExecute($sql, $params)->rowCount() > 0;
        } catch (PDOException $e) {
            // Re-throw or handle as needed
            throw $e;
        }
    }

    /**
     * Helper to prepare and bind parameters with types
     */
    private function prepareAndExecute($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $paramKey = is_int($key) ? $key + 1 : $key;
            $type = PDO::PARAM_STR;

            if (is_int($value)) {
                $type = PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $type = PDO::PARAM_BOOL;
            } elseif (is_null($value)) {
                $type = PDO::PARAM_NULL;
            }

            $stmt->bindValue($paramKey, $value, $type);
        }
        $stmt->execute();
        return $stmt;
    }

    public function getPdo()
    {
        return $this->pdo;
    }
}
