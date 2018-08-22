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

class SessionFile implements SessionHandlerInterface
{

    protected $session_id = '';
    protected $file_path = '';
    protected $file_handle;
    protected $is_new_file = true;
    protected $file;
    protected $file_data_md5 = '';

    public function __construct($config = [])
    {
        if (!empty($config) && isset($config['sess_save_path'])) {
            $this->file_path = rtrim($config['sess_save_path'], '/\\');
            if (!is_dir($this->file_path)) {
                throw new QException("session 存储路径{$this->file_path}不存在");
            }
            if (!is_writable($this->file_path)) {
                throw new QException("session 存储路径{$this->file_path}不可写");
            }
            ini_set('session.save_path', $this->file_path);
        } else {
            $this->file_path = rtrim(ini_get('session.file_path'), '/\\');
        }
    }

    public function open($file_path, $name)
    {
        $this->file_path = $this->file_path . DIRECTORY_SEPARATOR;
        return true;
    }

    public function close()
    {
        if (is_resource($this->file)) {
            flock($this->file, LOCK_UN);
            fclose($this->file);

            $this->file = $this->session_id = null;
            return true;
        }
        return true;
    }

    public function read($session_id)
    {
        $this->session_id = $session_id;
        $this->file = $this->file_path . $this->session_id;
        $this->is_new_file = !file_exists($this->file);
        if ($this->file_handle == null) {
            if (($this->file_handle = fopen($this->file, 'c+b')) === false) {
                QFramework::log('session文件：' . $this->file . '读取失败', 'error');
                return false;
            }
            if (flock($this->file_handle, LOCK_EX) === false) {
                QFramework::log('session文件：' . $this->file . '锁定失败', 'error');
                fclose($this->file_handle);
                $this->file_handle = NULL;
                return false;
            }
            if ($this->is_new_file) {
                chmod($this->file, 0600);
                $this->file_data_md5 = '';
                return '';//第一次读取空文件
            }
        } else {
            rewind($this->file_handle);
        }
        $session_data = '';
        for ($read = 0, $length = filesize($this->file); $read < $length; $read += strlen($buffer)) {
            if (($buffer = fread($this->file_handle, $length - $read)) === false) {
                break;
            }
            $session_data .= $buffer;
        }
        $this->file_data_md5 = md5($session_data);
        return $session_data;
    }

    public function write($session_id, $session_data)
    {
        if ($session_id !== $this->_session_id && (!$this->close() OR $this->read($session_id) === false)) {
            return false;
        }
        if (!is_resource($this->file_handle)) {
            return false;
        }
        if ($this->file_data_md5 == md5($session_data)) {
            //相同内容不重复写入
            return true;
        }
        if (!$this->is_new_file) {
            ftruncate($this->file_handle, 0);
            rewind($this->file_handle);
        }
        if (($length = strlen($session_data)) > 0) {
            for ($written = 0; $written < $length; $written += $result) {
                if (($result = fwrite($this->file_handle, substr($session_data, $written))) === FALSE) {
                    break;
                }
            }
            if (!is_int($result)) {
                QFramework::log('session文件：' . $this->file . '写入失败', 'error');
                return false;
            }
        }
        $this->file_data_md5 = md5($session_data);
        return true;
    }

    public function destroy($session_id)
    {
        if ($this->close()) {
            return file_exists($this->file) ? unlink($this->file) : true;
        } elseif ($this->file_path !== null) {
            clearstatcache();
            return file_exists($this->file) ? unlink($this->file) : true;
        }

        return false;
    }

    //垃圾回收
    public function gc($maxlifetime)
    {
        if ( ! is_dir( $this->file_path ))
        {
            return false;
        }
        foreach (glob($this->file_path."*") as $file) {
            if (is_file($file) && filemtime($file) + $maxlifetime < time()) {
                unlink($file);
            }
        }
        return true;
    }
}