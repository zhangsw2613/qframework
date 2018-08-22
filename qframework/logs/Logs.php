<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\logs;

use qframework;

class Logs
{
    protected static $conf = [];

    public function __construct()
    {
        if (self::$conf == []) {
            self::$conf = QFramework::$config->get('log');
        }
    }

    /**
     * 写入日志文件
     * @param $log
     * @param string $type
     */
    public function writeLog($log, $type = 'debug')
    {
        $lodDir = $this->config('dir', BASE_PATH . DS . 'sys_cache' . DS . 'logs');
        if (!is_dir($lodDir)) {
            mkdir($lodDir, 0755, true);
        }
        $file = $lodDir . DS . date('Y-m-d').'.log';
        if (is_file($file) && $this->config('file_size', 8388608) <= filesize($file)) {
            rename($file, dirname($file) . DS . '-bak-' . basename($file));
        }
        $log = "[".$type."]"."[".date('Y-m-d H:i:s')."] ".QFramework::getComponent('http')->request()->getIp().' '.$_SERVER['REQUEST_URI']." --> {$log}\r\n";
        error_log($log, 3, $file);
    }

    protected function config($key = null, $def = null)
    {
        return !empty(self::$conf[$key]) ? self::$conf[$key] : $def;
    }
}