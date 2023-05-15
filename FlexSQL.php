<?php

class FlexSQL
{
    private $pdo;
    private $stmt;
    private $joinClauses = [];
    private $groupClauses = [];
    private $orderClauses = [];
    private $limitClause = '';

    public function __construct($host, $db, $user, $pass)
    {
        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $this->pdo = new PDO($dsn, $user, $pass, $options);
    }

    public function query($query, $params = [])
    {
        $this->stmt = $this->pdo->prepare($query);
        $this->stmt->execute($params);
        return $this;
    }

    public function fetch()
    {
        return $this->stmt->fetch();
    }

    public function fetchAll()
    {
        return $this->stmt->fetchAll();
    }

    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    public function quote($string)
    {
        return $this->pdo->quote($string);
    }

    private function resetQueryState()
    {
        $this->joinClauses = [];
        $this->groupClauses = [];
        $this->orderClauses = [];
        $this->limitClause = '';
    }

    private function buildJoinClause()
    {
        $joinClause = '';
        foreach ($this->joinClauses as $join) {
            $joinClause .= " {$join['type']} JOIN {$join['table']} ON {$join['on']}";
        }
        return $joinClause;
    }

    public function join($table, $on, $type = 'INNER')
    {
        $this->joinClauses[] = [
            'table' => $table,
            'on' => $on,
            'type' => $type,
        ];
        return $this;
    }

    public function leftJoin($table, $on)
    {
        return $this->join($table, $on, 'LEFT');
    }

    public function rightJoin($table, $on)
    {
        return $this->join($table, $on, 'RIGHT');
    }

    public function fullOuterJoin($table, $on)
    {
        return $this->join($table, $on, 'FULL OUTER');
    }

    public function groupBy($columns)
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }
        $this->groupClauses['columns'] = implode(',', $columns);
        return $this;
    }

    public function having($having)
    {
        $this->groupClauses['having'] = $having;
        return $this;
    }

    public function orderBy($table, $columns, $order = 'ASC')
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $orderBy = implode(', ', $columns);
        $query = "SELECT * FROM $table ORDER BY $orderBy $order";
        return $this->query($query)->fetchAll();
    }

    public function createTable($table, $columns)
    {
        $query = "CREATE TABLE $table ($columns)";
        return $this->query($query)->rowCount();
    }

    public function dropTable($table)
    {
        $query = "DROP TABLE $table";
        return $this->query($query)->rowCount();
    }

    public function updateTable($table, $column, $new_value, $where)
    {
        $query = "UPDATE $table SET $column = ? WHERE $where";
        $params = [$new_value];
        return $this->query($query, $params)->rowCount();
    }

    public function limit($limit)
    {
        $this->limitClause = "LIMIT $limit";
        return $this;
    }
}
