<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\database\Drivers;

use qframework\database\Drivers\Driver;
use qframework\database\DatabaseException;
use PDO;

class MysqlDriver extends Driver
{

    public function __construct($config = [])
    {
        if (empty($config)) {
            throw new DatabaseException("数据库配置错误", DatabaseException::ERR_CONFIG);
        }
        $this->dsn = strtolower($config['scheme']) . ':host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['dbname'] . ';';
        $this->config = $config;
        parent::__construct();
    }


}