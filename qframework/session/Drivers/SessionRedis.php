<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */
namespace qframework\session\Drivers;

use qframework;
use qframework\exception\QException;
use qframework\session\SessionHandlerInterface;

class SessionRedis implements SessionHandlerInterface
{

    protected $config = [];
    protected $redis;
    protected $session_id = '';
    protected $redis_data_md5 = '';

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    public function open($file_path, $name)
    {
        if (!class_exists('Redis', false)) {
            throw new QException("Redis类找不到");
            return false;
        }
        try {
            $this->redis = new \Redis();
            $err_messgae = '';
            if (!$this->redis->connect($this->config['host'], $this->config['port'], $this->config['timeout'])) {
                $err_messgae = 'redis服务连接失败 host:' . $this->config['host'] . ', port:' . $this->config['port'];
            }
            if (!empty($this->config['passwd']) AND !$this->redis->auth($this->config['passwd'])) {
                $err_messgae = 'redis认证失败';
            }
            if (!empty($err_messgae)) {
                throw new \RedisException($err_messgae);
            }
        } catch (\RedisException $e) {
            throw new QException($e->getMessage());
        }
        return true;
    }

    public function close()
    {
        if (isset($this->redis)) {
            try {
                if ($this->redis->ping() === '+PONG') {
                    if (!$this->redis->close()) {
                        return false;
                    }
                }
            } catch (\RedisException $e) {
                QFramework::log("Redis关闭异常:" . $e);
            }
        }
        return true;
    }

    public function read($session_id)
    {
        $this->session_id = $session_id;
        if (!$this->redis->exists($session_id)) {
            return '';
        }
        $session_data = $this->redis->get($session_id);
        $this->redis_data_md5 = md5($session_data);
        return $session_data;
    }

    public function write($session_id, $session_data)
    {
        if ($session_id !== $this->session_id) {
            $this->session_id = $session_id;
            $this->redis_data_md5 = '';
        }
        if ($this->redis_data_md5 == md5($session_data)) {
            return true;
        }
        if (!$this->redis->setex($session_id, (int)$this->config['sess_expire'], $session_data)) {
            return false;
        }
        $this->redis_data_md5 = md5($session_data);
        return true;
    }

    public function destroy($session_id)
    {
        if (!$this->redis->delete($session_id)) {
            QFramework::log("Redis销毁session失败");
            return false;
        }
        return true;
    }

    //使用redis自己的回收机制
    public function gc($maxlifetime)
    {
        return true;
    }
}