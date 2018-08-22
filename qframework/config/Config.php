<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\config;

class Config
{
    private static $config = [];

    public function __construct($path = '')
    {
        if (empty(self::$config)) {
            $path = APP_PATH . '/config/config.php';
            if (file_exists($path)) {
                self::$config = include $path;
            }
            if (!empty(self::$config['ext_files'])) {
                foreach (self::$config['ext_files'] as $conf) {
                    $tmp_path = APP_PATH . '/config/' . $conf . '.php';
                    self::$config = array_merge(include $tmp_path, self::$config);
                }
            }
        }
    }

    public function get($key = '', $default = null)
    {
        if (empty($key)) {
            return self::$config;
        }
        if (strpos($key, '.') !== false) {
            $key = explode('.', $key, 2);
            return !empty(self::$config[$key[0]][$key[1]]) ? self::$config[$key[0]][$key[1]] : $default;
        } else {
            return !empty(self::$config[$key]) ? self::$config[$key] : $default;
        }
    }

    public function set($key = null, $val = null)
    {
        $key = explode('.', $key, 2);
        if (isset(self::$config[$key[0]][$key[1]]) && $val !== null) {
            self::$config[$key[0]][$key[1]] = $val;
            return true;
        }
        return false;
    }
}