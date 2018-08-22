<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\cache\Drivers;

use qframework\exception\QException;

class CacheApc
{
    protected static $config = [];
    protected $apc;

    public function __construct($config = '')
    {
        if (self::$config == []) {
            self::$config = $config['apc'];
        }
        if (!function_exists("apc_store")) {
            throw new QException("apc缓存不可用");
        }
    }

    public function get($key)
    {
        $key = self::$config['prefix'] . $key;
        if (apc_exists($key)) {
            return apc_fetch($key);
        }
        return false;
    }

    public function set($key, $data, $expire = null)
    {
        $key = self::$config['prefix'] . $key;
        if (is_null($expire)) {
            $expire = (int)self::$config['expire'];
        }
        return apc_store($key, $data, $expire);
    }

    public function delete($key)
    {
        $key = self::$config['prefix'] . $key;
        if (apc_exists($key)) {
            return apc_delete($key);
        }
        return false;
    }

    public function clean()
    {
        return apc_clear_cache('user');
    }

}