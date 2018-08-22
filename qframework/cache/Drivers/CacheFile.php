<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\cache\Drivers;

use qframework;
use qframework\exception\QException;

class CacheFile
{
    protected static $config = [];
    protected $file = '';

    public function __construct($config = '')
    {
        if (self::$config == []) {
            self::$config = $config['file'];
        }
        if (substr(self::$config['path'], -1) != DS) {
            self::$config['path'] .= DS;
        }
    }

    public function get($key)
    {
        $this->filename($key);
        if (!is_file($this->file)) {
            return false;
        }
        $fileData = unserialize(substr(file_get_contents($this->file), 13));
        if ($fileData !== false) {
            $expire = (int)$fileData['expire'];
            if ($expire != 0 && time() > filemtime($this->file) + $expire) {
                unlink($this->file);
                return false;
            }
            return is_array($fileData) ? $fileData['data'] : false;
        }
        return false;
    }

    public function set($key, $data, $expire = null)
    {
        $this->filename($key);
        if (is_null($expire)) {
            $expire = (int)self::$config['expire'];
        }
        $fileData = [
            'expire' => $expire,
            'data' => $data
        ];
        $content = '<?php exit;?>' . serialize($fileData);
        if (write_file($this->file, $content)) {
            chmod($this->file, 0640);
            return true;
        };
        return false;
    }

    public function delete($key)
    {
        $this->filename($key);
        return is_file($this->file) ? unlink($this->file) : false;
    }

    public function clean($dir = '')
    {
        if (empty($dir)) {
            $dir = self::$config['path'];
        }
        $dir .= '*';
        $files = glob($dir);
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                } else {
                    $this->clean($file . DS);
                }
            }
        }
    }

    private function filename($key)
    {
        if(strripos($key,DS) !== false){
            $depr_0 = substr($key,0,strripos($key,DS)+1);
            $depr_1 = substr($key,strripos($key,DS)+1);
            self::$config['path'] .= $depr_0.$depr_1.DS;
        }
        if (!is_dir(self::$config['path'])) {
            mkdir(self::$config['path'], 0755, true);
        }
        $this->file = self::$config['path'] . self::$config['prefix'] . md5($key) . '.php';
    }
}