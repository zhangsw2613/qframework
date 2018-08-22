<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\base;

use qframework;
use qframework\exception\QException;
use ReflectionClass;

class Container
{

    private static $_instance = null;
    private static $_singletons = [];
    private static $_reflections = [];
    private static $_dependencies = [];

    public static function getInstance()
    {
        if (empty(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * 利用反射创建实例对象
     * @param $class string $class 类名
     * @param $params [type] $args 参数
     * @return object
     */
    protected function build($class, $params)
    {
        list ($reflection, $dependencies) = $this->getDependencies($class);
        foreach ($params as $index => $param) {
            $dependencies[$index] = $param;
        }
        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * 获取类的反射对象及参数
     * @param $class string $class 类名
     * @return array
     */
    protected function getDependencies($class)
    {
        if (isset(self::$_reflections[$class])) {
            return [self::$_reflections[$class], self::$_dependencies[$class]];
        }
        $dependencies = [];
        try {
            $reflection = new ReflectionClass($class);
            $constructor = $reflection->getConstructor();
            if ($constructor !== null) {
                foreach ($constructor->getParameters() as $param) {
                    if ($param->isDefaultValueAvailable()) {
                        $dependencies[] = $param->getDefaultValue();
                    }
                }
            }
            self::$_reflections[$class] = $reflection;
            self::$_dependencies[$class] = $dependencies;
            return [$reflection, $dependencies];
        } catch (QException $e) {
            QFramework::log($e->getMessage());
        }
    }

    /**
     * 单例class
     * @param string $class string $class 类名
     * @param array $params [type] $params 参数
     * @return object
     */
    public function singleton($class = '', $params = [])
    {
        if (!isset(self::$_singletons[$class])) {
            self::$_singletons[$class] = $this->build($class, $params);
        }
        return self::$_singletons[$class];
    }
}