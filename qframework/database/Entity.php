<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\database;

use qframework\base\Model;

/**
 * 实体类
 * Class Entity
 * @package qframework\database
 */
class Entity implements \ArrayAccess, \Serializable
{

    private $_fields = [];
    private $_model = null;
    private $_query = null;
    private $_table = '';
    private $_triggerManager = null;

    public function __construct(Model $model, Query $query)
    {
        $this->_model = $model;
        $this->_query = $query;
        $this->_table = $this->_model->table;
        $this->_triggerManager = $this->_model->triggerManager;
    }


    /**
     * 赋值查询结果至实体属性
     * @param $arr
     * @param bool $is_set_arr
     */
    public function setAttribute($arr, $is_set_arr = false)
    {
        foreach ($arr as $key => $value) {
            if ($is_set_arr) {
                property_exists($this, $key) && $this->$key = $value;
            } else {
                is_string($key) && $this->_fields[$key] = $value;
            }
        }
    }

    /**
     * 将结果集转换成数组
     */
    public function toArray()
    {
        return $this->_fields;
    }

    /**
     * 返回关联模型查询结果
     * @param $name
     * @param $arguments
     * @return Entity|DataSet
     * @throws ModelException
     */
    public function __call($name, $arguments)
    {
        //获取关联模型数据
        if (!isset($this->_model->relations[$name])) {
            throw new ModelException("不存在名为{$name}的关联模型");
        }
        $relation = $this->_model->relations[$name];
        //获取主键的值
        $relation->myValue = $this[$relation->myField];
        return $relation->getResult();
    }


    public function offsetExists($offset)
    {
    }

    public function offsetGet($offset)
    {
        return $this->_fields[$offset] ?? null;
    }

    public function offsetSet($offset, $value)
    {
    }

    public function offsetUnset($offset)
    {
    }

    public function serialize()
    {
        return serialize($this->_fields);
    }

    public function unserialize($serialized)
    {
    }
}