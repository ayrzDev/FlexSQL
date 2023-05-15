<?php

class FlexSQL {
    private $pdo;
    private $stmt;

    public function __construct($host, $db, $user, $pass) {
        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $this->pdo = new PDO($dsn, $user, $pass, $options);
    }

    public function query($query, $params = []) {
        $this->stmt = $this->pdo->prepare($query);
        $this->stmt->execute($params);
        return $this;
    }

    public function fetch() {
        return $this->stmt->fetch();
    }

    public function fetchAll() {
        return $this->stmt->fetchAll();
    }

    public function rowCount() {
        return $this->stmt->rowCount();
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollBack() {
        return $this->pdo->rollBack();
    }

    public function quote($string) {
        return $this->pdo->quote($string);
    }

    public function select($table, $columns = '*', $where = '', $params = []) {
        $query = "SELECT $columns FROM $table";
        if (!empty($where)) {
            $query .= " WHERE $where";
        }
        return $this->query($query, $params);
    }

    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $params = array_values($data);
        $this->query($query, $params);
        return $this->lastInsertId();
    }

    public function update($table, $data, $where = '', $params = []) {
        $set = '';
        foreach ($data as $column => $value) {
            $set .= "$column=?,";
        }
        $set = rtrim($set, ',');
        $query = "UPDATE $table SET $set";
        if (!empty($where)) {
            $query .= " WHERE $where";
        }
        $params = array_merge(array_values($data), $params);
        return $this->query($query, $params)->rowCount();
    }

    public function delete($table, $where = '', $params = []) {
        $query = "DELETE FROM $table";
        if (!empty($where)) {
            $query .= " WHERE $where";
        }
        return $this->query($query, $params)->rowCount();
    }
}
