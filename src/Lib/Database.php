<?php

namespace SymphonyPDO\Lib;

use SymphonyPDO\Lib\Exceptions\DatabaseException;
use PDO;

class Database
{
    private $connection;
    private $tablePrefix;

    public function __construct($dsn, $user, $password, array $options = null, array $attributes = null)
    {
        $this->tablePrefix = null;

        if (isset($options['table-prefix'])) {
            $this->tablePrefix = $options['table-prefix'];
            unset($options['table-prefix']);
        }

        $this->connection = new PDO($dsn, $user, $password, $options);

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
        return (!is_null($this->connection) &&
            is_object($this->connection) &&
            $this->connection instanceof PDO);
    }

    public function bindMultiple($query, $params, &$variable, $type)
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

    private function __findType($value)
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

    public function insert(array $fields, $table)
    {
        $set = null;
        foreach (array_keys($fields) as $key) {
            $set .= "`{$key}` = :{$key}, ";
        }

        $query = $this->prepare(sprintf(
            'INSERT INTO %s SET %s', $table, rtrim($set, ', ')
        ));

        // $value MUST be passed to bindParam by reference or it will fail!
        // http://stackoverflow.com/questions/12144557/php-pdo-bindparam-was-falling-in-a-foreach
        foreach ($fields as $key => &$value) {
            $query->bindParam(sprintf(':%s', $key), $value, $this->__findType($value));
        }

        $query->execute();

        return $this->lastInsertId();
    }

    public function update(array $fields, $table, $where = null)
    {
        $set = null;
        foreach (array_keys($fields) as $key) {
            $set .= "`{$key}` = :{$key}, ";
        }

        $query = $this->prepare(sprintf(
            'UPDATE %s SET %s %s', $table, rtrim($set, ', '), ($where != null ? " WHERE {$where}" : null)
        ));

        // $value MUST be passed to bindParam by reference or it will fail!
        // http://stackoverflow.com/questions/12144557/php-pdo-bindparam-was-falling-in-a-foreach
        foreach ($fields as $key => &$value) {
            $query->bindParam(sprintf(':%s', $key), $value, $this->__findType($value));
        }

        return $query->execute();
    }

    public function delete($table, $where)
    {
        $query = $this->prepare(sprintf('DELETE FROM `%s` WHERE %s', $this->replaceTablePrefix($table), $where));

        return $query->execute();
    }

    public function truncate($table)
    {
        $query = $this->prepare(sprintf('TRUNCATE `%s`', $this->replaceTablePrefix($table)));

        return $query->execute();
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
