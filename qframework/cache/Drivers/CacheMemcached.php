<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\cache\Drivers;

use qframework;
use qframework\exception\QException;
use memcached;

class CacheMemcached
{
    protected static $config = [];
    protected $memcacahed;

    public function __construct($config = '')
    {
        if (self::$config == []) {
            self::$config = $config['memcached'];
        }
        if (!class_exists('Memcached', false)) {
            throw new QException("Memcached类找不到");
            return false;
        }
        $this->memcacahed = new Memcached();
        if (!empty(self::$config['servers'])) {
            $this->memcacahed->addServers(self::$config['servers']);
        } else {
            $this->memcacahed->addServer(self::$config['host'], self::$config['port'], self::$config['weight']);
        }
    }

    public function get($key)
    {
        return $this->memcacahed->get(self::$config['prefix'] . $key);
    }

    public function set($key, $data, $expire = null)
    {
        $key = self::$config['prefix'] . $key;
        if (is_null($expire)) {
            $expire = (int)self::$config['expire'];
        }
        return $this->memcacahed->set($key,$data,$expire);
    }

    public function delete($key, $time = 0)
    {
        $key = self::$config['prefix'] . $key;
        return $this->memcacahed->delete($key, $time);
    }

    public function clean()
    {
        return $this->memcacahed->flush();
    }

}