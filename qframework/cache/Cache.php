<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\cache;

use qframework;
use qframework\exception\QException;

class Cache
{
    protected static $drivers = ['apc', 'file', 'memcached', 'redis', 'sqlite', 'wincache', 'xcache'];
    protected static $config = [];
    protected $name = 'file';
    protected $loadClass = [];
    protected $cache;

    public function __construct()
    {
        if (self::$config == []) {
            self::$config = QFramework::$config->get('cache');
        }
    }

    /**
     * 获取一个缓存
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        $this->loadDriver();
        return $this->cache->get($key);
    }

    /**
     * 设置一个缓存
     * @param $key
     * @param $data
     * @param null $expire
     * @return mixed
     */
    public function set($key, $data, $expire = null)
    {
        $this->loadDriver();
        return $this->cache->set($key, $data, $expire);
    }

    /**
     * 删除指定缓存
     * @param $key
     * @return mixed
     */
    public function delete($key)
    {
        $this->loadDriver();
        return $this->cache->delete($key);
    }

    /**
     * 清空所有缓存
     * @return mixed
     */
    public function clean()
    {
        $this->loadDriver();
        return $this->cache->clean();
    }

    /**
     * 加载驱动
     */
    protected function loadDriver()
    {
        if (!isset($this->loadClass[$this->name])) {
            $this->cache = QFramework::$container->singleton('qframework\cache\Drivers\Cache' . ucfirst($this->name), [self::$config]);
            $this->loadClass[$this->name] = true;
        }
    }

    //get魔术方法
    public function __get($name)
    {
        $this->name = $name;
        if (!in_array($name, self::$drivers)) {
            QFramework::log("缓存驱动{$name}加载错误");
            return null;
        }
        $this->loadDriver();
        return $this;
    }

    //call魔术方法
    public function __call($name, $arguments)
    {
        if (method_exists($this->cache, $name)) {
            return call_user_func_array(array($this->cache, $name), $arguments);
        } else {
            throw new QException("调用方法：{$name}不存在");
        }
    }
}