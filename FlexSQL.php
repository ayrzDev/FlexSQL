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
            // PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, // Object olarak çekmek için aşağıdaki kodu kapatıp bu kodu açmanız gerekli.
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

    public function delete($table, $where = "", $params = [])
    {
        $query = "DELETE FROM $table";
        if (!empty($where)) {
            $query .= " WHERE $where";
        }
        $this->stmt = $this->pdo->prepare($query);
        $this->stmt->execute($params);
        return $this;
    }

    public function select($table, $columns = "*", $where = "", $params = [], $limit = "")
    {
        $query = "SELECT $columns FROM $table";
        if (!empty($where)) {
            $query .= " WHERE $where";
        }
        if (!empty($limit)) {
            $query .= " LIMIT $limit";
        }
        $this->stmt = $this->pdo->prepare($query);
        $this->stmt->execute($params);
        return $this;
    }

    public function insert($table, $data)
    {
        $columns = implode(", ", array_keys($data));
        $values = implode(", ", array_fill(0, count($data), "?"));
        $query = "INSERT INTO $table ($columns) VALUES ($values)";
        $this->stmt = $this->pdo->prepare($query);
        $this->stmt->execute(array_values($data));
        return $this;
    }

    public function update($table, $data, $where = "", $params = [])
    {
        $setClause = implode(", ", array_map(function ($key) {
            return "$key=?";
        }, array_keys($data)));
        $query = "UPDATE $table SET $setClause";
        if (!empty($where)) {
            $query .= " WHERE $where";
        }
        $params = array_merge(array_values($data), $params);
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
        $this->limitClause = "LIMIT " . intval($limit);
        return $this;
    }


    public function mdelete($table, $where, $whereIn = null)
    {
        $query = "DELETE FROM $table WHERE $where";

        if ($whereIn !== null && is_array($whereIn) && count($whereIn) > 1) { // $whereIn değişkeni kontrol ediliyor.
            $whereInClause = implode(",", array_fill(0, count($whereIn[1]), "?"));
            $query .= " AND $whereIn[0] IN ($whereInClause)";
        }

        $this->stmt = $this->pdo->prepare($query);

        if ($whereIn !== null && is_array($whereIn) && count($whereIn) > 1) { // $whereIn değişkeni kontrol ediliyor.
            $params = $whereIn[1];
            if (isset($whereIn[2])) {
                $params = array_merge($params, $whereIn[2]);
            }
            $this->stmt->execute($params);
        } else {
            $this->stmt->execute();
        }

        return $this;
    }

    public function minsert($table, $data, $columns = null)
    {
        if ($columns === null) {
            $columns = array_keys(reset($data));
        } elseif (is_string($columns)) {
            $columns = explode(',', $columns);
        }

        $values = implode(", ", array_fill(0, count($columns), "?"));
        $columns = implode(", ", $columns);
        $query = "INSERT INTO $table ($columns) VALUES ($values)";
        $this->stmt = $this->pdo->prepare($query);
        foreach ($data as $row) {
            $this->stmt->execute(array_values($row));
        }
        return $this;
    }
}
