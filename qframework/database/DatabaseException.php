<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\database;

use qframework\exception\QException;
class DatabaseException extends QException
{
    //数据库连接错误
    const ERR_CONNECT = 1;
    //数据库配置错误
    const ERR_CONFIG = 2;
    //数据库查询失败
    const ERR_QUERY = 3;
}