<?php
/**
 * Q框架 常用函数封装
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

function session()
{

}

function cookie()
{

}

function url()
{

}

function redirect()
{

}

function write_log()
{

}

/**
 * 文件写入操作
 * @param $path
 * @param $data
 * @param string $mode
 * @return bool
 */
function write_file($path, $data, $mode = 'wb')
{
    if ( ! $fp = @fopen($path, $mode))
    {
        return FALSE;
    }

    flock($fp, LOCK_EX);

    for ($result = $written = 0, $length = strlen($data); $written < $length; $written += $result)
    {
        if (($result = fwrite($fp, substr($data, $written))) === FALSE)
        {
            break;
        }
    }

    flock($fp, LOCK_UN);
    fclose($fp);

    return is_int($result);
}

function is_php($version)
{
    static $_is_php;
    $version = (string) $version;

    if ( ! isset($_is_php[$version]))
    {
        $_is_php[$version] = version_compare(PHP_VERSION, $version, '>=');
    }

    return $_is_php[$version];
}