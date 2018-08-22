<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @copyright   Copyright by qufenggu.com
 * @since   Version 1.0.0
 */

if (version_compare(PHP_VERSION, '5.5.0', '<')) die('require PHP > 5.5.0 !');
// 应用程序目录
define('APP_PATH', __DIR__ . '/demo');
// 引入QFramework核心文件
require('qframework/QFramework.php');
QFramework::run();