<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */
namespace qframework\database;

use qframework;
use qframework\base\Model;
use qframework\database\Drivers\Driver;
use qframework\database\Analyzer;
use qframework\database\DatabaseException;
use think\log\driver\File;


/**
 * 查询集
 * Class Query
 * @package qframework\database
 */
class Query
{
    private $driver = null;
    private $model = null;
    private $analyzer = null;
    private $table = '';

    /**
     * Query constructor.
     * @param Driver $driver
     * @param Model $model
     */
    public function __construct(Driver $driver,Model $model)
    {
        $this->driver = $driver;
        $this->model = $model;
        $this->analyzer = new Analyzer($this->model->table);
    }

    /**
     * 增加一个and条件
     * @param $expression
     * @return $this
     */
    public function where($expression)
    {
        $this->analyzer->addWhere($expression);
        return $this;
    }

    /**
     * 增加一个or条件
     * @param $expression
     * @return $this
     */
    public function orWhere($expression)
    {
        $this->analyzer->addWhere($expression,'OR');
        return $this;
    }

    /**
     * 获取当前查询条件
     * @return array
     */
    public function getOptions()
    {
        return $this->analyzer->options();
    }

    /**
     * 增加查询字段
     * @param string $fields
     * @return $this
     */
    public function field($fields = '*')
    {
        $this->analyzer->addFields($fields);
        return $this;
    }

    /**
     * 增加一个limit条件
     * @param int $limit
     * @param int $length
     * @return $this
     */
    public function limit(int $limit, int $length = 0)
    {
        $this->analyzer->addLimit($limit,$length);
        return $this;
    }

    /**
     * 增加一个group by条件
     * @param $group
     * @return $this
     */
    public function group($group)
    {
        $this->analyzer->addGroup($group);
        return $this;
    }

    /**
     * 增加一个having条件
     * @param $having
     * @return $this
     */
    public function having($having)
    {
        $this->analyzer->addHaving($having);
        return $this;
    }


    public function order($order)
    {
        $this->analyzer->addOrder($order);
        return $this;
    }

    /**
     * 返回查询结果实体
     * @return null|Entity
     */
    public function find()
    {
        $this->analyzer->parseQuerySql();
        $result = $this->driver->query($this->analyzer->sql(), $this->analyzer->binds());
        $entity = $this->model->create($this->model,$this);
        $entity->setAttribute(count($result) == 0 ? [] : $result[0]);
        $this->analyzer->clean();
        return $entity;
    }

    /**
     * 返回查询结果集
     * @return null|DataSet
     */
    public function select()
    {
        $this->analyzer->parseQuerySql();
        $result = $this->driver->query($this->analyzer->sql(), $this->analyzer->binds());
        if(count($result) == 0){
            return null;
        }
        $dataSet = new DataSet();
        foreach ($result as $key => $item){
            $entity = $this->model->create($this->model,$this);
            $entity->setAttribute($item);
            $dataSet->add($entity);
        }
        $this->analyzer->clean();
        return $dataSet;
    }

    /**
     * @param array $fields
     * @return mixed
     */
    public function insert(array $fields)
    {
        $this->model->triggerManager->fire('beforeInsert');
        $this->analyzer->parseInsertSql($fields);
        $this->driver->exec($this->analyzer->sql(), $this->analyzer->binds());
        $this->model->triggerManager->fire('afterInsert');
        $this->analyzer->clean();
        return $this->driver->lastInsertId();
    }
    public function delete()
    {
        $this->model->triggerManager->fire('beforeDelete');
        $this->analyzer->parseDeleteSql();
        $this->driver->exec($this->analyzer->sql(), $this->analyzer->binds());
        $this->model->triggerManager->fire('afterDelete');
        $this->analyzer->clean();
        return $this->driver->rowCount();
    }

    /**
     * @param array $fields
     * @return mixed
     */
    public function update(array $fields)
    {
        $this->model->triggerManager->fire('beforeUpdate');
        $this->analyzer->parseUpdateSql($fields);
        $this->driver->exec($this->analyzer->sql(), $this->analyzer->binds());
        $this->model->triggerManager->fire('afterUpdate');
        $this->analyzer->clean();
        return $this->driver->rowCount();
    }

    /**
     * @param string $field
     * @param int $value
     * @return mixed
     */
    public function increment(string $field,int $value = 1)
    {
        if(!$value){return false;}
        $field = [$field=>['INC',$value]];
        return $this->update($field);
    }

    /**
     * @param string $field
     * @param int $value
     * @return mixed
     */
    public function decrement(string $field,int $value = 1)
    {
        if(!$value){return false;}
        $field = [$field=>['DEC',$value]];
        return $this->update($field);
    }

    /**
     * @param string $field
     * @return int
     */
    public function count(string $field)
    {
        $field = "count({$field})";
        $this->field($field);
        $this->analyzer->parseQuerySql();
        $result = $this->driver->query($this->analyzer->sql(), $this->analyzer->binds());
        $this->analyzer->clean();
        if(count($result) == 0){
            return 0;
        }
        return $result[0][$field];
    }

    /**
     * @param string $fields
     * @return $this
     */
    public function distinct(string $fields)
    {
        $this->analyzer->addDistinct($fields);
        return $this;
    }

    /**
     * @param string $sql
     * @param array $binds
     * @return mixed
     */
    public function exec(string $sql, array $binds)
    {
        $this->driver->exec($sql, $binds);
        return $this->driver->rowCount();
    }

    /**
     *
     */
    public function beginTrans()
    {
        return $this->driver->beginTransaction();
    }
    public function commit()
    {
        return $this->driver->commit();
    }
    public function rollback()
    {
        return $this->driver->rollBack();
    }
    public function alias(string $alias)
    {
        $this->analyzer->addAlias($alias);
        return $this;
    }
    public function join(string $join)
    {
        $this->analyzer->addJoin($join,'INNER JOIN');
        return $this;
    }
    public function rightJoin(string $join)
    {
        $this->analyzer->addJoin($join,'RIGHT JOIN');
        return $this;
    }
    public function leftJoin(string $join)
    {
        $this->analyzer->addJoin($join,'LEFT JOIN');
        return $this;
    }
}