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

class Router
{
    private static $dispatcher = null;
    private $req = null;
    protected $argsArray = [];
    protected static $config = [];
    protected $segments = '';

    public function __construct($http)
    {
        $this->req = $http;
        if (self::$config == []) {
            self::$config = Qframework::$config->get('router');
        }
        if (self::$dispatcher == null) {
            self::$dispatcher = QFramework::$container->singleton(__NAMESPACE__ . '\Dispatch');
        }
    }

    /**
     * 路由分发
     */
    public function dispatcher()
    {
        $this->queryString();
        if (trim($this->segments, '?') == '') {
            $this->defaultRouter(); // 空请求调用默认路由
        } else {
            // 根据配置使用不同的方式解析url请求字符串
            switch (self::$config['urlmode']) {
                case 1:
                    $this->queryParseUrl();
                    break;
                case 2:
                    $this->rewriteParseUrl();
                    break;
                case 3:
                    $this->pathParseUrl();
                    break;
                case 4:
                    $this->htmlParseUrl();
                    break;
                default:
                    $this->queryParseUrl();
                    break;
            }
        }
    }

    public function run()
    {
        self::$dispatcher->exec();
    }

    /**
     * 解析query模式
     * 示例：index.php?m=user&c=index&a=run
     */
    public function queryParseUrl()
    {
        $m = $this->req->request()->get('m');
        if(!empty($m)){
            self::$dispatcher->setModuleName($m);
        }
        $c = $this->req->request()->get('c',$this->config('defaultController','Index'));
        $a = $this->req->request()->get('a',$this->config('defaultAction','index'));
        if (!self::$dispatcher->setControllerName($c) || !self::$dispatcher->setActionName($a)) {
            throw new QException("控制器{$c} <br> 方法{$a} 错误");
        }
    }

    /**
     * 解析rewrite模式
     * 示例：/user/index/run/?id=100
     */
    public function rewriteParseUrl()
    {
        $segments = trim($this->segments,'/');
        $request = explode('/',$segments);
        $req_q = array_pop($request);
        $req_q_arr = array_values(array_filter(explode('?',$req_q)));
        if(count($req_q_arr) > 1){
            $request[] = $req_q_arr[0];
        }
        $m = array_shift($request);
        if(!empty($m)){
            self::$dispatcher->setModuleName($m);
        }
        $c = isset($request[0]) ? $request[0] : $this->config('defaultController', 'Index');
        $a = isset($request[1]) ? $request[1] : $this->config('defaultAction', 'index');
        if (!self::$dispatcher->setControllerName($c) || !self::$dispatcher->setActionName($a)) {
            throw new QException("控制器{$c} <br> 方法{$a} 错误");
        }
    }

    /**
     * 解析pathinfo模式
     * 示例：/user/index/run/id/100
     */
    public function pathParseUrl()
    {
        $depr = $this->config('pathinfo_depr', '/');
        if ($pos = strpos($this->segments, '?')) {
            $this->segments = substr($this->segments, 0, $pos);
        }
        $request = explode($depr, trim($this->segments, $depr));
        if (self::$dispatcher->setModuleName($request[0])) {
            array_shift($request);
        }
        $c = isset($request[0]) ? $request[0] : $this->config('defaultController', 'Index');
        $a = isset($request[1]) ? $request[1] : $this->config('defaultAction', 'index');
        array_shift($request);
        array_shift($request);
        $var = [];
        preg_replace_callback('/(\w+)\/([^\/]+)/', function ($match) use (&$var) {
            $var[$match[1]] = strip_tags($match[2]);
        }, implode('/', $request));
        if ($var != []) {
            $this->req->request()->setGet = $var;
        }
        $_REQUEST = array_merge($this->req->request()->post(), $this->req->request()->get(), $this->req->request()->cookie());
        if (!self::$dispatcher->setControllerName($c) || !self::$dispatcher->setActionName($a)) {
            throw new QException("控制器{$c} <br> 方法{$a} 错误");
        }
    }

    /**
     * 解析html模式
     * 示例：user-index-run?uid=100
     */
    public function htmlParseUrl()
    {
        $request = explode('?',$this->segments);
        $pass = explode('-',$request[0]);
        if(!empty($pass[0])){
            self::$dispatcher->setModuleName($pass[0]);
        }
        array_shift($pass);
        $c = isset($pass[0]) ? $pass[0] : $this->config('defaultController', 'Index');
        $a = isset($pass[1]) ? $pass[1] : $this->config('defaultAction', 'index');
        if (!self::$dispatcher->setControllerName($c) || !self::$dispatcher->setActionName($a)) {
            throw new QException("控制器{$c} <br> 方法{$a} 错误");
        }
    }
    /**
     * 对url进行提取
     */
    private function queryString()
    {
        $baseUrl = self::$config['base'];
        $uri = $this->req->request()->getServer('REQUEST_URI');
        $filterParam = array('<', '>', '"', "'", '%3C', '%3E', '%22', '%27', '%3c', '%3e');
        $uri = str_replace($filterParam, '', $uri);
        $urlArr = parse_url($baseUrl);
        $this->segments = str_replace($urlArr['path'], '', $uri);
        if (($pos = strpos($this->segments, '.php')) !== false) {
            $this->segments = substr($this->segments, $pos + 4);
        }
        $this->segments = trim($this->segments, '/');
    }

    /**
     * 默认控制器
     * @throws RouterException
     */
    private function defaultRouter()
    {
        self::$dispatcher->setModuleName($this->config('defaultModule', null));
        $c = $this->config('defaultController', 'Index');
        $a = $this->config('defaultAction', 'index');
        if (!self::$dispatcher->setControllerName($c) || !self::$dispatcher->setActionName($a)) {
            throw new QException("控制器{$c} <br> 方法{$a} 错误");
        }
    }

    /**
     * 获取dispatcher实例
     * @return object
     */
    public function getDispatch()
    {
        return self::$dispatcher;
    }
    /**
     * 获取配置项
     * @param  [type] $key 配置项的键名
     * @param  [type] $def 不存在时候返回的默认值
     * @return string
     */
    protected function config($key = null, $def = null)
    {
        return isset(self::$config[$key]) ? self::$config[$key] : $def;
    }
}