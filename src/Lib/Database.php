<?php

namespace SymphonyPDO\Lib;

use SymphonyPDO\Lib\Exceptions\DatabaseException;
use PDO;

class Database
{
    private $connection;
    private $tablePrefix;

    public function doInTransaction(\Closure $query) {
        $this->beginTransaction();
        try {
            $result = $query($this);
            $this->commit();
        } catch (\PDOException $ex) {
            $this->rollBack();
            throw $ex;
        }
        return $result;
    }

    public function __construct($dsn, $user, $password, array $options = null, array $attributes = null)
    {
        $this->tablePrefix = null;

        if (isset($options['table-prefix'])) {
            $this->tablePrefix = $options['table-prefix'];
            unset($options['table-prefix']);
        }

        $this->connection = new SymphonyPDO($dsn, $user, $password, $options);

        foreach ($attributes as $k => $v) {
            $this->connection->setAttribute($k, $v);
        }

        return $this->connected();
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function connected()
    {
        return ($this->connection instanceof PDO);
    }

    public static function bindMultiple($query, $params, &$variable, $type)
    {
        foreach ($params as $param) {
            $query->bindParam($param, $variable, $type);
        }
    }

    public function replaceTablePrefix($statement)
    {
        if (!is_null($this->tablePrefix)) {
            $statement = preg_replace('/tbl_(\S+?)([\s\.,]|$)/', $this->tablePrefix.'\\1\\2', $statement);
        }

        return $statement;
    }

    private static function findParamType($value)
    {
        $type = false;

        switch (gettype($value)) {

            case 'boolean':
                $type = PDO::PARAM_BOOL;
                break;

            case 'integer':
                $type = PDO::PARAM_INT;
                break;

            case 'double':
            case 'string':
                $type = PDO::PARAM_STR;
                break;

            case 'NULL':
                $type = PDO::PARAM_NULL;
                break;

            case 'array':
            case 'object':
            case 'resource':
            case 'unknown type':
            default:
                $type = PDO::PARAM_STR;
                break;

        }

        return $type;
    }

    public function insertUpdate(array $fields, array $updatableFields, $table)
    {
        $set = [];
        foreach ($updatableFields as $key) {
            $set[] = "`{$key}` = :{$key}";
        }

        $sql = 'INSERT INTO %1$s (%2$s) VALUES (%3$s) ON DUPLICATE KEY UPDATE ' . implode(", ", $set);

        return $this->insert($fields, $table, $sql);
    }

    public function insert(array $fields, $table, $sql=null)
    {
        if (is_null($sql)) {
            $sql = 'INSERT INTO %1$s (%2$s) VALUES (%3$s);';
        }

        $params = [];
        $keys = $values = [];

        foreach (array_keys($fields) as $key) {
            if (!is_null($fields[$key])) {
                $params[] = $key;
            }
            $keys[] = "`{$key}`";
            $values[] = (is_null($fields[$key]) ? 'NULL' : ":{$key}");
        }

        $keys = implode(", ", $keys);
        $values = implode(", ", $values);

        $sql = sprintf(
            $sql, $table, $keys, $values
        );

        return $this->doInTransaction(function(Database $db) use ($sql, $fields, $params) {
            $query = $db->prepare($sql);

            // $value MUST be passed to bindParam by reference or it will fail!
            // http://stackoverflow.com/questions/12144557/php-pdo-bindparam-was-falling-in-a-foreach
            foreach ($fields as $key => &$value) {
                if (!in_array($key, $params)) {
                    continue;
                }

                $query->bindParam(sprintf(':%s', $key), $value, self::findParamType($value));
            }

            $query->execute();

            return $db->lastInsertId();
        });
    }

    public function update(array $fields, $table, $where=null, $sql=null)
    {
        if (is_null($sql)) {
            $sql = 'UPDATE %1$s SET %2$s %3$s;';
        }

        $set = [];
        foreach (array_keys($fields) as $key) {
            $set[] = "`{$key}` = :{$key}";
        }

        $set = implode(", ", $set);

        $where = !is_null($where)
            ? " WHERE {$where}"
            : ""
        ;

        $sql = sprintf(
            $sql, $table, $set, $where
        );

        return $this->doInTransaction(function(Database $db) use ($sql, $fields) {
            $query = $db->prepare($sql);

            // $value MUST be passed to bindParam by reference or it will fail!
            // http://stackoverflow.com/questions/12144557/php-pdo-bindparam-was-falling-in-a-foreach
            foreach ($fields as $key => &$value) {
                $query->bindParam(sprintf(':%s', $key), $value, self::findParamType($value));
            }

            return $query->execute();
        });
    }

    public function delete($table, $where)
    {
        $sql = sprintf(
            'DELETE FROM `%s` WHERE %s',
            $this->replaceTablePrefix($table),
            $where
        );

        return $this->doInTransaction(function(Database $db) use ($sql) {
            $query = $db->prepare($sql);
            return $query->execute();
        });
    }

    public function truncate($table)
    {
        $sql = sprintf('TRUNCATE `%s`', $this->replaceTablePrefix($table));

        return $this->doInTransaction(function(Database $db) use ($sql) {
            $query = $db->prepare($sql);
            return $query->execute();
        });
    }

    public function __call($name, $args)
    {
        if (!$this->connected()) {
            throw new DatabaseException('No valid connection found. Initialise database connection first.');
        }
        $callback = array($this->connection, $name);

        switch ($name) {
            // Replace `tbl_` with the assigned table prefix
            case 'prepare':
            case 'query':
                $args[0] = $this->replaceTablePrefix($args[0]);
                break;
        }

        return call_user_func_array($callback, $args);
    }
}
