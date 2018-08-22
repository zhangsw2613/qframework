<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */
namespace qframework\http;

use qframework;

class Http
{

    protected static $httpRequest = null;
    protected static $httpResponse = null;

    public function __construct()
    {
        if (self::$httpRequest == null) {
            self::$httpRequest = QFramework::$container->singleton(__NAMESPACE__ . '\Request');
        }
    }

    public function request()
    {
        return self::$httpRequest;
    }

    public function response()
    {
        if (self::$httpResponse == null) {
            self::$httpResponse = QFramework::$container->singleton(__NAMESPACE__ . '\Response');
        }
        return self::$httpResponse;
    }

}