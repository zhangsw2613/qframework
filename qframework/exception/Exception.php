<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\exception;

use qframework;

class Exception
{

    public function fatalError($e)
    {
        if ($e !== null) {
            switch ($e['type']) {
                case E_ERROR:
                case E_PARSE:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                    ob_end_clean();//清除缓冲区
                    $this->handlerError($e);
                    break;
            }
        }
    }

    public function qError($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_USER_NOTICE:
                ob_end_clean();//清除缓冲区
                $error = "$errstr " . $errfile . " 第 $errline 行.";
                $this->handlerError($error);
                break;
        }
    }

    public function qException($e)
    {
        $error = [];
        $error['message'] = $e->getMessage();
        $error['file'] = $e->getFile();
        $error['line'] = $e->getLine();
        $error['trace'] = $e->getTraceAsString();
        $this->handlerError($error);
    }

    /**
     * @param $error
     */
    protected function handlerError($error)
    {
        if (QFramework::$isDebug || IS_CLI) {
            if (!is_array($error)) {
                $trace = debug_backtrace();
                $e['message'] = $error;
                $e['file'] = $trace[0]['file'];
                $e['line'] = $trace[0]['line'];
                ob_start();
                debug_print_backtrace();
                $e['trace'] = ob_get_clean();
            } else {
                $e = $error;
            }
            if (IS_CLI) {
                exit(iconv('UTF-8', 'gbk', $e['message']) . PHP_EOL . 'FILE: ' . $e['file'] . '(' . $e['line'] . ')' . PHP_EOL . $e['trace']);
            }
        } else {
            $e['message'] = is_array($error) ? $error['message'] : $error;
        }
        if (QFramework::$config->get('log.is_open', false) == true) {
            QFramework::log($e['message'], 'error');
        }
        $exceptionTpl = QFramework::$config->get('exception_tpl', BASE_PATH . DS . 'exception' . DS . 'tpl' . DS . 'exception.html');
        include $exceptionTpl;
        exit;
    }
}