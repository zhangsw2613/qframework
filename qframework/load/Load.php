<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\load;

use qframework;
use qframework\exception\QException;

class Load
{
    protected static $QInstance = null;
    protected static $_loadedModels = [];

    public function __construct($instance = null)
    {
        self::$QInstance = $instance;
    }

    /**
     * 加载用户自定义函数库、辅助函数helper
     * @param $fileName 文件名
     * @param string $loadType 类型
     */
    public function library($fileName, $loadType = 'libraries')
    {
        if (empty($fileName)) return;
        $fileName = $fileName . '.php';
        if (in_array($loadType, array('libraries', 'helpers'))) {
            $tryFile = APP_PATH . DS . $loadType . DS . $fileName;
            QFramework::import($tryFile);
        } else {
            $tryFile = APP_PATH . DS . 'libraries' . DS . $fileName;
            $is_loaded = QFramework::import($tryFile);
            if (!$is_loaded) {
                $tryFile = APP_PATH . DS . 'helpers' . DS . $fileName;
                QFramework::import($tryFile);
            }
        }
    }

    /**
     * 加载模型
     * @param $modelName 模型名称
     * @return mixed
     * @throws QException
     */
    public function model($modelName)
    {
        if (empty($modelName)) throw new QException("model不存在");
        $path = '';
        $model = '';
        if (($match = strrpos($modelName, '/')) !== false) {
            $path = substr($modelName, 0, $match);
            $model = substr($modelName, $match + 1);
        }
        if ($model == '') {
            $model = $modelName;
        }
        $modelName = QFramework::$id.'\models\\' . (empty($path) ? '' : $path . '\\') . ucfirst($model);
        if(isset(self::$_loadedModels[$modelName])){
            return self::$_loadedModels[$modelName];
        }
        $modelFile = APP_PATH . DS . 'models' . DS . $path . DS . $model . '.php';
        if (QFramework::import($modelFile)) {
            self::$QInstance->$model = QFramework::$container->singleton($modelName);
            self::$_loadedModels[$modelName] = self::$QInstance->$model;
        } else {
            throw new QException("model：{$modelName}不存在");
        }
    }

    /**
     * 判断模型是否已加载
     * @param string $modelName
     * @return bool
     */
    public function isModelLoaded($modelName = '')
    {
        return isset(self::$_loadedModels[$modelName]);
    }

    /**
     * 获取已加载模型实例
     * @param string $modelName
     * @return mixed
     */
    public function getModelLoaded($modelName = '')
    {
        return self::$_loadedModels[$modelName];
    }
}