<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\database\Drivers;

use qframework\database\DatabaseException;

/**
 * 数据驱动接口类,对PDO的再封装
 * Class Driver
 * @package qframework\database\Drivers
 */
abstract class Driver
{
    public $config = [];
    public $dsn = '';
    public $conn = null;
    private $_querySql = '';
    private $_PDOStatement = null;
    private $_transactionCounter = 0;

    public function __construct()
    {
        try {
            $this->conn = new \PDO($this->dsn, $this->config['user'], $this->config['passwd']);
            if (isset($this->config['charset'])) {
                $this->conn->prepare("set names '{$this->config['charset']}'")->execute();
            }
            return $this;
        } catch (\PDOException $e) {
            throw new DatabaseException($e->getMessage(), DatabaseException::ERR_CONNECT);
        }
    }

    public function bindParam()
    {
    }

    /**
     * 只会返回受影响的行数
     * @param string $sql
     * @param array $binds
     * @return mixed
     * @throws DatabaseException
     */
    public function exec($sql = '', $binds = [])
    {
        $this->execute($sql, $binds);
    }

    public function lastInsertId()
    {
        return $this->conn->lastInsertId();
    }

    /**
     * 使用PDO::prepare() 来准备一个 PDOStatement 对象并用 PDOStatement::execute() 发出语句
     * @param string $sql
     * @param array $binds
     * @return array|null
     * @throws DatabaseException
     */
    public function query($sql = '', $binds = [])
    {
        $this->execute($sql, $binds);
        return $this->_PDOStatement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function rowCount()
    {
        return $this->_PDOStatement->rowCount();
    }

    public function beginTransaction()
    {
        if (!$this->_transactionCounter++) {
            return $this->conn->beginTransaction();
        }
        $this->exec('SAVEPOINT trans' . $this->_transactionCounter);
        return $this->_transactionCounter;
    }

    public function commit()
    {
        if (!--$this->_transactionCounter) {
            return $this->conn->commit();
        }
        return $this->_transactionCounter >= 0;
    }

    public function rollBack()
    {
        if (--$this->_transactionCounter) {
            $this->exec('ROLLBACK TO trans' . $this->_transactionCounter + 1);
            return true;
        }
        return $this->conn->rollback();
    }

    private function execute($sql = '', $binds = [])
    {
        if (is_null($this->conn)) {
            return null;
        }
        if (is_object($this->_PDOStatement) && $this->_querySql != $sql) {
            $this->free();
        }
        try {
            if (empty($this->_PDOStatement)) {
                $this->_PDOStatement = $this->conn->prepare($sql);
            }
            if (!empty($binds)) {
                foreach ($binds as $key => $bind) {
                    $this->_PDOStatement->bindValue($key, $bind, $this->getParam($bind));
                }
            }
            $this->_PDOStatement->execute();
        } catch (\PDOException $e) {
            throw new DatabaseException($e->getMessage(), DatabaseException::ERR_QUERY);
        }

    }

    /**
     * 释放上次查询结果
     */
    public function free()
    {
        $this->_PDOStatement = null;
    }

    private function getParam($value)
    {
        if (is_int($value))
            $param = \PDO::PARAM_INT;
        elseif (is_bool($value))
            $param = \PDO::PARAM_BOOL;
        elseif (is_null($value))
            $param = \PDO::PARAM_NULL;
        elseif (is_string($value))
            $param = \PDO::PARAM_STR;
        else
            $param = FALSE;
        return $param;
    }
}