<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\base;

use qframework;
//以下加载系统类库
use libraries\common\Url;

abstract class Base
{
    protected static $url = null;
    public static $_loadedClass = [];
    protected static $instance = null;

    public function __construct()
    {
        if (self::$_loadedClass == []) {
            self::$_loadedClass = QFramework::getLoadedClass();
        }
        foreach (self::$_loadedClass as $var => $class) {
            $this->$var = $class;
        }
        if (self::$instance == null) {
            self::$instance = $this;
        }
        $this->load->library('common', 'libraries');//自动加载项目公共函数库
        QFramework::import(LIB_PATH . "/functions.php");//加载系统函数库
    }

    /**
     * 返回request实例
     * @return mixed
     */
    public function getInput()
    {
        return $this->http->request();
    }

    /**
     * 返回config实例
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * 执行另一个控制器方法
     */
    public function getController()
    {
        return $this->router->getDispatch();
    }

    /**
     * 加载类库导入组件
     * @return mixed
     */
    public function getLoad()
    {
        return QFramework::getComponent('load', [$this->getInstance()]);
    }

    /**
     * 调用系统类库中的url类
     */
    public function getUrl()
    {
        if (self::$url == null) {
            self::$url = new Url();
        }
        return self::$url;
    }

    /**
     * 调用系统session类
     */
    public function getSession()
    {
        return QFramework::getComponent('session');
    }

    /**
     * 调用系统session类
     */
    public function getCookie()
    {
        return QFramework::$container->singleton('qframework\http\Cookie');
    }

    /**
     * 调用系统cache类
     */
    public function getCache()
    {
        return QFramework::getComponent('cache');
    }

    public function &getInstance()
    {
        return self::$instance;
    }

    public function __get($name)
    {
        $func = 'get' . ucfirst($name);
        if (method_exists($this, $func)) {
            return $this->$func();
        }
        return null;
    }
}