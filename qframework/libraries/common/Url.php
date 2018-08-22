<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 * 系统类库注意书写命名空间
 */
namespace libraries\common;

use qframework;

class Url
{
    protected static $protocol = '';
    protected static $config = [];

    public function __construct()
    {
        if (self::isHttps()) {
            self::$protocol = 'https';
        } else {
            self::$protocol = 'http';
        }
        if (empty(self::$config)) {
            self::$config = Qframework::$config->get('router');
        }
    }

    /**
     * 生成一个url
     * @param $uri string
     * @return mixed
     */
    public static function create($uri = '')
    {
        $parse_uri = parse_url($uri);
        $url = $parse_uri['path'];
        if (isset($parse_uri['scheme'])) {
            if (!empty(self::$protocol)) {
                $url = self::$protocol . substr($uri, strpos($uri, '://'));
            } else {
                $url = $uri;
            }
        } elseif (!isset($parse_uri['scheme'])) {
            $base_url = self::$config['base'];
            $parse_base = parse_url($base_url);
            $url = $parse_base['path'] . (!empty(self::$config['indexPage']) ? '/' . self::$config['indexPage'] : '') . $url;
            if (isset($parse_uri['query'])) {
                $mode = self::$config['urlmode'];
                if ($mode == 3) {
                    $vars = explode('&', $parse_uri['query']);
                    $url_arr = [];
                    foreach ($vars as $key => $val) {
                        $url_arr[] = str_replace('=', self::$config['pathinfo_depr'], $val);
                    }
                    $url .= '/' . implode(self::$config['pathinfo_depr'], $url_arr);
                } else {
                    $url .= '?' . $parse_uri['query'];
                }
            }
            self::$config['showDomain'] && $url = $parse_base['scheme'] . '://' . $parse_base['host'] . $url;
        }
        return $url;
    }

    /**
     * 重定向功能
     * @param $uri
     * @param int $time
     */
    public static function redirect($uri, $time = 0)
    {
        $url = self::create($uri);
        if (!headers_sent()) {
            if ($time === 0) {
                if (isset($_SERVER['SERVER_PROTOCOL'], $_SERVER['REQUEST_METHOD']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.1') {
                    $code = ($_SERVER['REQUEST_METHOD'] !== 'GET') ? 303 : 307;
                } else {
                    $code = 302;
                }
                header("Location: " . $url, true, $code);
            } else {
                header("refresh:" . $time . ";url=" . $url . "");
            }
        } else {
            exit("<meta http-equiv='Refresh' content='" . $time . ";URL=" . $url . "'>");
        }
    }

    public static function isHttps()
    {
        if (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) {
            return true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return TRUE;
        } elseif (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
            return TRUE;
        }
        return FALSE;
    }
}