<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */
namespace qframework\database;


use qframework\base\Model;
use qframework\database\Drivers\Driver;

class Relation
{
    /**
     * 关联关系
     * @var string
     */
    public $relationMode;

    /**
     * 关联名称
     * @var string
     */
    public $relationName;

    /**
     * 关联模型
     * @var string
     */
    public $modelName;

    /**
     * 关联模型的字段
     * @var string
     */
    public $relationField;

    /**
     * 当前模型的字段
     * @var string
     */
    public $myField;

    /**
     * 当前模型的字段值
     * @var string
     */
    public $myValue;

    public function __construct($relation_mode, $relation_name, $model_name, $relation_field = Model::PK, $my_field = Model::PK)
    {
        $this->relationMode = $relation_mode;
        $this->relationName = $relation_name;
        $this->modelName = $model_name;
        $this->relationField = $relation_field;
        $this->myField = $my_field;
    }

    /**
     * 返回查询结果
     * @return Entity|DataSet
     */
    public function getResult()
    {
        $model = new $this->modelName;
        $query = new Query($model->getDriver(),$model);
        $query->where([$this->relationField=>['=',$this->myValue]]);
        if(Model::HAS_ONE == $this->relationMode){
           $result = $query->find();
        }elseif(Model::HAS_MANY == $this->relationMode){
            $result = $query->select();
        }
        return $result;
    }
}