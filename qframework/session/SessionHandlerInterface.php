<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */
namespace qframework\session;

interface SessionHandlerInterface{
    public function open($save_path, $name);
    public function close();
    public function read($session_id);
    public function write($session_id, $session_data);
    public function destroy($session_id);
    public function gc($maxlifetime);
}