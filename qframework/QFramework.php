<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

use qframework\base\Container;

class QFramework
{

    public static $id = null;
    public static $config = null;
    public static $container = null;
    public static $isDebug = false;
    private static $components = [];
    private static $loadedFiles = [];

    /**
     * 框架启动程序，需在项目入口文件调用
     * @param $conf string 配置文件路径
     */
    public static function run($conf = '')
    {
        self::init($conf);
        //路由调度
        $http = self::getComponent('http');
        $router = self::getComponent('router', [$http]);
        $router->dispatcher();
        $router->run();
    }

    /**
     * 系统初始化
     * @param $conf []
     */
    private static function init($conf = '')
    {
        define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
        define('IS_GET', REQUEST_METHOD == 'GET' ? true : false);
        define('IS_POST', REQUEST_METHOD == 'POST' ? true : false);
        define('IS_PUT', REQUEST_METHOD == 'PUT' ? true : false);
        define('IS_DELETE', REQUEST_METHOD == 'DELETE' ? true : false);
        define('IS_AJAX', ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'xmlhttprequest') == 0)) ? true : false);
        define('IS_CLI', PHP_SAPI === 'cli');
        define('BASE_PATH', __DIR__);
        define('DS', DIRECTORY_SEPARATOR);
        defined('LIB_PATH') or define('LIB_PATH', BASE_PATH . DS . 'libraries');

        // 注册AUTOLOAD方法
        spl_autoload_register([__CLASS__, 'autoload']);
        // 设定错误和异常处理
        register_shutdown_function([__CLASS__, 'fatalError']);
        set_error_handler([__CLASS__, 'qError']);
        set_exception_handler([__CLASS__, 'qException']);

        self::$container = Container::getInstance();
        self::$config = self::getComponent('config');
        self::$id = self::$config->get('application.id');
        if (self::$config->get('session.auto_start', false) == true) {
            self::getComponent('session')->start();
        }
        self::$isDebug = self::$config->get('debug', false);
    }

    /**
     * 组件方式加载
     * @param string $com
     * @param array $params
     * @return mixed
     */
    public static function getComponent($com = '', $params = [])
    {
        $com = strtolower($com);
        if (!array_key_exists($com, self::$components)) {
            self::$components[$com] = self::$container->singleton('qframework\\' . $com . '\\' . ucfirst($com), $params);
        }
        return self::$components[$com];
    }

    /**
     * 自动加载类库
     * @param string $class 类名
     */
    public static function autoload($class = '')
    {
        $class = str_replace('\\', DS, ltrim($class, '\\'));
        list($vendor, $filepath) = explode(DS, $class, 2);
        if (isset(self::$loadedFiles[$class])) {
            include self::$loadedFiles[$class];
        } else {
            if ($vendor == 'qframework') {
                $filename = BASE_PATH . DS . $filepath . '.php';
            } elseif ($vendor == 'libraries') {
                $filename = LIB_PATH . DS . $filepath . '.php';
            } elseif ($vendor == QFramework::$id) {
                $filename = APP_PATH . DS . $filepath . '.php';
            } elseif ($vendor == 'controllers') {
                $filename = APP_PATH . DS . $vendor . DS . strtolower($filepath) . '.php';
            }
            self::import($filename);
        }

    }

    /**
     * 优化include文件
     * @param $filename string 文件路径
     * @param $filename
     * @return mixed
     * @throws \qframework\exception\QException
     */
    public static function import($filename)
    {
        if (!isset(self::$loadedFiles[$filename])) {
            if (is_file($filename)) {
                include $filename;
                self::$loadedFiles[$filename] = true;
            } else {
                self::$loadedFiles[$filename] = false;
            }
        }
        return self::$loadedFiles[$filename];
    }

    /**
     * 加载日志组件写入日志
     * @param $log
     * @param string $type
     */
    public static function log($log, $type)
    {
        self::getComponent('logs')->writeLog($log, $type);
    }

    /**
     * 自定义致命错误处理
     */
    public static function fatalError()
    {
        $e = error_get_last();
        self::getComponent('exception')->fatalError($e);
    }

    /**
     * 自定义错误处理
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     */
    public static function qError($errno, $errstr, $errfile, $errline)
    {
        self::getComponent('exception')->qError($errno, $errstr, $errfile, $errline);
    }

    /**
     * 自定义异常处理
     * @param $e 异常对象
     */
    public static function qException($e)
    {
        self::getComponent('exception')->qException($e);
    }

    public static function getLoadedClass()
    {
        return self::$components;
    }

}