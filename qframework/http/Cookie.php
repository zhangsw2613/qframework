<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */
namespace qframework\http;

class Cookie
{
    private $expire = 2592000;
    private $path = '/';
    private $domain = '';
    private $prefix = "q_"; //cookie前缀
    private $cookie = [];

    public function init($cookie = [], $cookieConf = [])
    {
        $this->cookie = $cookie;
        if ($cookie != []) {
            //重建全局cookie
            $_COOKIE = $cookie;
        }
        foreach (array('expire', 'prefix', 'domain', 'path') as $conf) {
            if (!empty($cookieConf['cookie_' . $conf])) {
                $func = 'set' . ucfirst($conf);
                $this->$func($cookieConf['cookie_' . $conf]);
            }
        }
    }

    /**
     * 修改cookie中的数据
     * @param $key cookie的名称
     * @param $val cookie值
     * @param string $expire cookie失效时间
     * @param string $prefix cookie前綴
     * @param string $path cookie路径
     * @param string $domain cookie作用的主机
     */
    public function set($key, $val, $expire = '', $prefix = '', $path = '', $domain = '')
    {
        $expire = (empty($expire)) ? time() + $this->expire : $expire; //cookie时间
        $prefix = (empty($prefix)) ? $this->prefix : $prefix; //cookie前缀
        $path = (empty($path)) ? $this->path : $path; //cookie路径
        $domain = (empty($domain)) ? $this->domain : $domain; //主机名称
        if (empty($domain)) {
            setcookie($prefix . $key, $val, $expire, $path);
        } else {
            setcookie($prefix . $key, $val, $expire, $path, $domain);
        }
        $_COOKIE[$prefix . $key] = $val;
    }

    /**
     * 获取cookie数据
     * @param  string $key 键
     * @param  string $def 默认值
     * @return mixed
     */
    public function get($key = null, $def = null)
    {
        if ($key == null) {
            return $this->cookie;
        }
        $key = $this->getPrefix() . $key;
        if (isset($this->cookie[$key])) {
            return $this->cookie[$key];
        }
        return $def;

    }

    /**
     * 清空cookie
     * @return void
     */
    public function del()
    {
        if (!empty($this->cookie)) {
            foreach ($this->cookie as $key => $val) {
                if (strpos($key, $this->prefix) == 0) {
                    $key = substr($key, strlen($this->prefix));
                }
                $this->rm($key);
            }
        }
    }

    /**
     * 删除指定cookie项
     * @param  string $key cookie项
     * @return void
     */
    public function rm($key = '', $path = '')
    {
        $key = $this->getPrefix() . $key;
        $this->set($key, '', time() - 3600, $path);
        $_COOKIE[$this->prefix . $key] = '';
        unset($_COOKIE[$this->prefix . $key]);
    }

    /**
     * 判断cookie是否存在
     * @param $key string 键名
     * @return bool
     */
    public function is_set($key)
    {
        return isset($_COOKIE[$this->prefix . $key]);
    }

    /**
     * 设置前綴
     * @param string $val 前綴
     */
    public function setPrefix($val = '')
    {
        $this->prefix = is_string($val) ? $val : '';
    }

    /**
     * 设置过期时间
     * @param integer $val 过期时间戳
     */
    public function setExpire($val = 0)
    {
        $this->expire = is_numeric($val) ? $val : 0;
    }

    /**
     * 设置cookie可访问路径
     * @param string $val 路径
     */
    public function setPath($val = '/')
    {
        $this->path = $val;
    }

    /**
     * 设置cookie域
     * @param [type] $val 域
     */
    public function setDomain($val = null)
    {
        if ($val !== null) $this->domain = $val;
    }

    /**
     * 获取前綴
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * 获取过期时间
     * @return int
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * 获取cookie可访问路径
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * 获取cookie域
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }
}