<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\base;

use qframework;
use qframework\database\Query;
use qframework\database\Entity;
use qframework\database\Trigger;
use qframework\database\Relation;
use qframework\database\ModelException;

/**
 * 模型基类，用户模型需继承该基类
 * Class Model
 * @package qframework\base
 */
abstract class Model
{
    const PK = 'id';
    const HAS_ONE = 1;
    const HAS_MANY = 2;
    public $table = '';
    public $relations = [];
    public $triggers = [];
    public $triggerManager = null;
    private static $_models = [];
    private static $_links = [];
    private static $_drivers = [];

    public function __construct()
    {
        $this->setAttribute();
        $this->triggerManager();
    }

    abstract protected function setAttribute();

    /**
     * 设置关联关系
     * @param string $relation_mode 关联关系
     * @param string $relation_name 关联名称
     * @param string $model_name 关联模型
     * @param string $relation_field 关联模型的字段
     * @param string $my_field 当前模型的字段
     * @throws ModelException
     */
    final public function relation($relation_mode, $relation_name, $model_name, $relation_field = Model::PK, $my_field = Model::PK)
    {
        if (isset($this->relations[$relation_name])) {
            throw new ModelException("已存在一个{$relation_name}的关系");
        }
        $relation = new Relation($relation_mode, $relation_name, $model_name, $relation_field, $my_field);
        $this->relations[$relation_name] = $relation;
    }

    /**
     * 添加一个触发器
     * @param string $trigger_name
     * @param callback $func
     * @throws ModelException
     */
    final public function addTrigger($trigger_name = '', $func)
    {
        $this->triggers[$trigger_name][] = $func;
    }

    /**
     * 触发器处理函数
     */
    protected function triggerManager()
    {
        $trigger = new Trigger($this);
        foreach ($this->triggers as $name => $callbacks) {
            foreach ($callbacks as $c) {
                $trigger->bind($name, $c);
            }
        }
        $this->triggerManager = $trigger;
    }

    /**
     * 用户模型对象映射到实体
     * @param Model $model
     * @param Query $query
     * @return Entity
     */
    public function create(Model $model, Query $query): Entity
    {
        return new Entity($model, $query);;
    }

    /**
     * 返回查询器实例
     * @return Query
     */
    public static function query(): Query
    {
        $_selfModel = self::_myself();
        if (!isset(self::$_links[$_selfModel])) {

            self::$_links[$_selfModel] = new Query(self::getDriver(), self::$_models[$_selfModel]);
        }
        return self::$_links[$_selfModel];
    }

    public static function _myself()
    {
        $_selfModel = get_called_class();
        if (!isset(self::$_models[$_selfModel])) {
            if (QFramework::getComponent('load')->isModelLoaded($_selfModel)) {//为了兼容$this->load->model()方法【废弃】
                self::$_models[$_selfModel] = QFramework::getComponent('load')->getModelLoaded($_selfModel);
            } else {
                self::$_models[$_selfModel] = new $_selfModel;
            }
        }
        return $_selfModel;
    }

    /**
     * 获取数据库驱动
     * @return null
     */
    public static function getDriver()
    {
        $_selfModel = get_called_class();
        if (!isset(self::$_drivers[$_selfModel])) {
            self::$_drivers[$_selfModel] = QFramework::getComponent('database')->driver;
        }
        return self::$_drivers[$_selfModel];
    }


}