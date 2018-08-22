<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\http;

class Response
{
    protected static $status = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        118 => 'Connection timed out',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        210 => 'Content Different',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        310 => 'Too many Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested range unsatisfiable',
        417 => 'Expectation failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable entity',
        423 => 'Locked',
        424 => 'Method failure',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway or Proxy Error',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        507 => 'Insufficient storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];
    protected $body;

    public function send($code = 200, $content = '', $content_type = 'text/html')
    {
        $this->setHeader($code,$content_type);
        $this->setBody($content);
        $body = $this->getBody();
        if ($body != '') {
            echo $body;
        } else {
            $signature = 'Framework Server at ' . $_SERVER['SERVER_NAME'] . ' Port 80';
            $body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
	                        <html>
	                            <head>
	                                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	                                <title>' . $code . ' ' . self::$status[$code] . '</title>
	                            </head>
	                            <body>
	                                <h1>' . self::$status[$code] . '</h1>
	                                ' . $content . '

	                                <hr />
	                                <address>' . $signature . '</address>
	                            </body>
	                        </html>';
            echo $body;
        }
    }

    public function setHeader($code,$content_type)
    {
        if (isset(self::$status[$code])) {
            header('HTTP/1.1 ' . $code . ' ' . self::$status[$code]);
            header('Status:' . $code . ' ' . self::$status[$code]);
        }
        header('Content-type: ' . $content_type);
    }

    public function setBody($content)
    {
        $this->body = $content;
    }

    public function getBody()
    {
        return $this->body;
    }
}