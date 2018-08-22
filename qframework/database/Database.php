<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\database;

use qframework;

class Database
{
    public $driver = null;
    protected static $config = [];

    public function __construct()
    {
        if (self::$config == []) {
            self::$config = QFramework::$config->get('database');
        }
        $info = self::$config;
        if ($info != '') {
            $dsn = parse_url($info['dsn']);
            $tmp = explode('/', trim($dsn['path'], '/'));
            isset($dsn['scheme']) && $info['scheme'] = $dsn['scheme'];
            isset($dsn['host']) && $info['host'] = $dsn['host'];
            isset($dsn['port']) && $info['port'] = $dsn['port'];
            $info['dbname'] = $tmp[0];
            $info['passwd'] = self::$config['passwd'];
        }
        if (!empty($info['scheme'])) {
            $this->driver = QFramework::$container->singleton('qframework\database\Drivers\\' . ucfirst($info['driver'] . 'Driver'), [$info]);
        } else {
            throw new DatabaseException("Exception驱动器未指定");
        }
    }

}