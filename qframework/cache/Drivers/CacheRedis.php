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

class CacheRedis
{
    protected static $config = [];
    protected $redis;

    public function __construct($config = '')
    {
        if (self::$config == []) {
            self::$config = $config['redis'];
        }
        try {
            $this->redis = new \Redis();
            if (!$this->redis->connect(self::$config['host'], self::$config['port'], self::$config['timeout'])) {
                throw new QException('Redis 连接失败');
            }
        } catch (\RedisException $e) {
            throw new QException('Redis 初始化失败：' . $e->getMessage());
        }
        if (isset(self::$config['password'])) {
            if (!$this->redis->auth(self::$config['password'])) {
                throw new QException('Redis 认证失败');
            }
        }
    }

    public function get($key)
    {
        $data = $this->redis->get(self::$config['prefix'] . $key);
        $jsonData = json_decode($data, true);
        return ($jsonData === null) ? $data : $jsonData;
    }

    public function set($key, $data, $expire = null)
    {
        $key = self::$config['prefix'] . $key;
        if (is_null($expire)) {
            $expire = (int)self::$config['expire'];
        }
        if (is_object($data) || is_array($data)) {
            $data = json_encode($data);
        }
        return (is_int($expire) && $expire) ?
            $this->redis->setex($key, $expire, $data) :
            $this->redis->set($key, $data);
    }

    public function delete($key)
    {
        $key = self::$config['prefix'] . $key;
        if ($this->redis->delete($key) !== 1) {
            return false;
        }
        return true;
    }

    public function clean()
    {
        return $this->redis->flushDB();
    }

}