<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\database;

/**
 * 触发器管理器
 * Class Trigger
 * @package qframework\trigger
 */
class Trigger
{

    private $_triggerHandlers = ['beforeInsert', 'afterInsert', 'beforeDelete', 'afterDelete', 'beforeUpdate', 'afterUpdate'];
    private $triggers = [];
    private $model = null;

    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * 绑定一个触发器
     * @param $name
     * @param $callback
     * @return $this
     * @throws \qframework\database\ModelException
     */
    public function bind($name, $callback)
    {
        if (!in_array($name, $this->_triggerHandlers)) {
            throw new ModelException('不支持的触发器类型：' . $name);
        }
        $this->triggers[$name][] = $callback;
        return $this;
    }


    /**
     * 执行触发器
     * @param $name
     */
    public function fire($name)
    {
        if (in_array($name, $this->_triggerHandlers) && array_key_exists($name, $this->triggers)) {
            foreach ($this->triggers[$name] as $callback) {
                call_user_func(array($this->model, $callback));
            }
        }
    }

}