<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */
namespace qframework\http;

use qframework;

class Request
{

    protected $get = [];
    protected $post = [];
    protected $files = [];
    protected $cookie = [];
    protected $_enable_xss = false;
    protected $_enable_csrf = false;
    protected $requestMethod = '';
    protected $raw_input_stream;
    private static $httpCookie = null;
    private static $security = null;

    //TODO:不良设计-过度耦合（改进）
    public function __construct()
    {
        if (self::$httpCookie == null) {
            self::$httpCookie = QFramework::$container->singleton(__NAMESPACE__ . '\Cookie');
        }
        $this->_enable_xss = (QFramework::$config->get('xss_filter') == true);
        $this->_enable_csrf = (QFramework::$config->get('csrf_filter') == true);
        if ($this->_enable_csrf === TRUE && !IS_CLI) {
            $this->csrf_filter();
        }
        $pattern = '/^[a-z0-9:_\/|-]+$/i';
        foreach (['get', 'post', 'cookie', 'files'] as $data) {
            $inputData = '_' . strtoupper($data);
            foreach ($GLOBALS[$inputData] as $key => $val) {
                if (preg_match($pattern, $key)) {
                    $tmp = &$this->$data;
                    $tmp[$key] = $val;
                }
            }
            unset($GLOBALS[$inputData]);//防止全局变量引起的不安全因素
        }
        self::$httpCookie->init($this->cookie, QFramework::$config->get('cookie'));
        $this->cookie = self::$httpCookie;
    }

    /**
     * 获取$_GET
     * @param null $key
     * @param null $def
     * @param bool|true $filter
     * @return array|null|string
     */
    public function get($key = null, $def = null, $filter = true)
    {
        if ($key != null && !isset($this->get[$key])) {
            return $def;
        }
        return $filter == true ? $this->filter_values($this->get, $key) : (isset($this->get[$key]) ? $this->get[$key] : $this->get);
    }

    /**
     * 获取$_POST
     * @param null $key
     * @param null $def
     * @param bool|true $filter
     * @return array|null|string
     */
    public function post($key = null, $def = null, $filter = true)
    {
        if ($key != null && !isset($this->post[$key])) {
            return $def;
        }
        return $filter == true ? $this->filter_values($this->post, $key) : (isset($this->post[$key]) ? $this->post[$key] : $this->post);
    }

    /**
     * 获取cookie
     * @param null $key
     * @param string $def
     * @return mixed
     */
    public function cookie($key = null, $def = '')
    {
        return $this->filter_values($this->cookie->get($key, $def));
    }

    /**
     * 获取文件数据
     * @param null $key
     * @param null $def
     * @return array|null
     */
    public function files($key = null, $def = null)
    {
        if ($key == null) return $this->files;
        if (isset($this->files[$key])) {
            return $this->files[$key];
        }
        return $def;
    }

    /**
     * 检查是否ajax
     * @return bool
     */
    public function isAjax()
    {
        return IS_AJAX;
    }

    /**
     * 返回请求类型
     * @return string
     */
    public function getMethod()
    {
        if (IS_GET) {
            $this->requestMethod = "IS_GET";
        } elseif (IS_POST) {
            $this->requestMethod = "IS_POST";
        } elseif (IS_PUT) {
            $this->requestMethod = "IS_PUT";
        } elseif (IS_DELETE) {
            $this->requestMethod = "IS_DELETE";
        }
        return $this->requestMethod;
    }

    /**
     * 返回$_SERVER值
     * @param null $key
     * @return null
     */
    public function getServer($key = null)
    {
        if ($key == null) return $_SERVER;
        if (isset($_SERVER[$key]) || (($key = strtoupper($key)) && isset($_SERVER[$key]))) {
            return $_SERVER[$key];
        }
        return null;
    }

    /**
     * 获取客户端IP
     * @return string
     */
    public function getIp()
    {
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $realip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR")) {
                $realip = getenv("HTTP_X_FORWARDED_FOR");
            } elseif (getenv("HTTP_CLIENT_IP")) {
                $realip = getenv("HTTP_CLIENT_IP");
            } else {
                $realip = getenv("REMOTE_ADDR");
            }
        }

        return $realip;
    }

    /**
     * 数据安全过滤
     * @param $array
     * @param $key
     * @return array|string
     */
    private function filter_values($array, $key = null)
    {
        if (is_array($key)) {
            $output = array();
            foreach ($key as $k) {
                $output[$k] = $this->filter_values($array, $k);
            }
            return $output;
        }
        if (isset($array[$key])) {
            $val = $array[$key];
        } else {
            $val = $array;
        }
        return $this->_enable_xss ? self::xss_filter($val) : $val;
    }

    /**
     * xss过滤
     * @param $str string
     * @return string
     */
    public static function xss_filter($str)
    {
        if (self::$security == null) {
            self::$security = QFramework::getComponent("Security");
        }
        return self::$security->xss_clean($str);
    }

    /**
     * csrf过滤
     */
    public static function csrf_filter()
    {
        if (self::$security == null) {
            self::$security = QFramework::getComponent("Security");
        }
        self::$security->csrf_clean();
    }

    /**
     * 获取输入流
     * @param $name string
     * @return string
     */
    public function __get($name)
    {
        if ($name == 'raw_input_stream') {
            empty($this->raw_input_stream) AND $this->raw_input_stream = file_get_contents("php://input");
            return $this->raw_input_stream;
        }
    }

    /**
     * 设置属性
     * @param $name string
     * @param $value array
     */
    public function __set($name, $value = [])
    {
        if ($name == 'setGet') {
            $this->get = array_merge($value, $this->get);
        } elseif ($name == 'setPost') {
            $this->post = array_merge($value, $this->post);
        } elseif ($name == 'setCookie') {
            $this->cookie->set($value[0], $value[1]);
        }
    }
}