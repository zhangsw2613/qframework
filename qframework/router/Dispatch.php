<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */
namespace qframework\router;

use qframework;
use qframework\exception\QException;

class Dispatch
{
    protected $controller = null;
    protected $moduleName = null;
    protected $controllerName = null;
    protected $actionName = null;
    protected $argsArray = [];

    public function __construct()
    {

    }

    /**
     * 执行用户请求的方法
     */
    public function exec()
    {
        $c = $this->controller;
        $a = $this->actionName;
        $error_404 = false;
        if (!method_exists($c, $a)) {
            $error_404 = true;
        } elseif (!in_array(strtolower($a), array_map('strtolower', get_class_methods($c)), true)) {
            $error_404 = true;
        }
        if ($error_404) {
            throw new QException("方法 {$a} 找不到");
        }
        $this->runBefore($c, $a);
        if ($this->argsArray != []) {
            call_user_func_array([$c, $a], $this->argsArray);
        } else {
            $c->$a();
        }
        $this->runAfter($c, $a);
    }

    /**
     * 执行前置方法
     * @param $controller
     * @param $action
     */
    protected function runBefore($controller, $action)
    {
        $action = 'before_' . $action;
        if (!method_exists($controller, $action)) return;
        $controller->$action();
    }

    /**
     * 执行后置方法
     * @param $controller
     * @param $action
     */
    protected function runAfter($controller, $action)
    {
        $action = 'after_' . $action;
        if (!method_exists($controller, $action)) return;
        $controller->$action();
    }

    /**
     * 执行一个控制器
     * @param null $m
     * @param string $c
     * @param string $a
     * @param array $args
     * @return bool
     */
    public function get($m = null, $c = '', $a = '', $args = [])
    {
        if ($m != null) $this->setModuleName($m);
        if ($this->setControllerName($c) && $this->setActionName($a)) {
            $this->setArgs($args);
            return $this->exec();
        }
        return false;

    }

    /**
     * 设置模块名称
     * @param string $m 模块名
     * @return bool
     */
    public function setModuleName($m = null)
    {
        if ($m != null) $m = strtolower($m);
        $this->moduleName = $m;
        return true;
    }

    /**
     * 设置控制器名称
     * @param string $c 控制器名
     * @return bool
     */
    public function setControllerName($c = null)
    {
        if ($c === null) return false;
        $className = ucfirst($c);
        $this->controller = QFramework::$container->singleton($this->getControllerNamespace() . $className);
        if ($this->controller) {
            $this->controllerName = $c;
            return true;
        }
        return false;
    }

    /**
     * 返回控制器命名空间
     * @return string
     */
    protected function getControllerNamespace()
    {
        $m = $this->moduleName;
        if ($m == null) {
            return '\controllers\\';
        } else {
            $m = str_replace('/', '\\', $m);
            return '\controllers\\' . $m . '\\';
        }
    }

    /**
     * 设置方法名称
     * @param string $a 方法名
     * @return bool
     */
    public function setActionName($a = null)
    {
        if ($a !== null) $this->actionName = strtolower($a);
        return true;
    }

    /**
     * 设置参数
     * @param $arr []
     */
    public function setArgs($arr)
    {
        $this->argsArray = $arr;
    }

    /**
     * 获取当前模块名称
     * @return null
     */
    public function getModuleName(){
        return $this->moduleName;
    }

    /**
     * 获取控制器名称
     * @return null
     */
    public function getControllerName(){
        return $this->controllerName;
    }

    /**
     * 获取方法名称
     * @return null
     */
    public function getActionName(){
        return $this->actionName;
    }
}