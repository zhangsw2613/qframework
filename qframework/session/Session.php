<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\session;

use qframework;
use qframework\session\SessionHandlerInterface;
use qframework\exception\QException;

class Session
{
    private $driver = 'file';
    protected static $config = [];
    protected static $params = [];

    public function __construct()
    {
        if (IS_CLI) {
            return;
        } elseif ((bool)ini_get('session.auto_start')) {
            return;
        }
        if (self::$config == []) {
            self::$config = QFramework::$config->get('session');
        }
        $info = self::$config;
        if ($info != '') {
            $dsn = parse_url($info['dsn']);
            $tmp = explode('/', trim($dsn['path'], '/'));
            isset($dsn['scheme']) && $info['scheme'] = $dsn['scheme'];
            isset($dsn['host']) && $info['host'] = $dsn['host'];
            isset($dsn['port']) && $info['port'] = $dsn['port'];
            $info['dbname'] = $tmp[0];
            $info['tbname'] = isset($tmp[1]) ? $tmp[1] : 'session';
            $info['passwd'] = self::$config['passwd'];
        }
        $this->sessionConfigHandler($info);//session_start前执行设置
        $this->driver = isset($info['driver']) ? $info['driver'] : $info['scheme'];
        if ($this->driver != '') {
            $class = QFramework::$container->singleton('qframework\session\Drivers\Session' . ucfirst($this->driver), [$info]);
            if ($class instanceof SessionHandlerInterface) {
                //session_set_save_handler($class, TRUE);
               session_set_save_handler(
                    array(&$class,"open"),
                    array(&$class,"close"),
                    array(&$class,"read"),
                    array(&$class,"write"),
                    array(&$class,"destroy"),
                    array(&$class,"gc"));
            } else {
                QFramework::log('session驱动器未继承自SessionHandler接口', 'error');
                return;
            }
        } else {
            QFramework::log('session驱动器未指定', 'error');
            return;
        }

        ini_set('session.use_trans_sid', 0);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.hash_function', 1);
        ini_set('session.hash_bits_per_character', 4);
        register_shutdown_function('session_write_close');
    }

    /**
     * 启动session
     */
    public function start()
    {
       if (!empty(self::$params['sess_name'])) {
           $this->name(self::$params['sess_name']);
        }
        session_start();
    }

    /**
     * 关闭session
     * @return bool
     */
    public function close()
    {
        if (!self::isActive()) {
            return false;
        }
        session_write_close();
    }

    /**
     * 销毁session会话
     * @return bool
     */
    public function destory()
    {
        if (!self::isActive()) {
            return false;
        }
        if (ini_get('session.use_cookies')) {
            setcookie(
                $this->name(), '', time() - 42000,
                self::$params['path'],
                self::$params['domain'],
                self::$params['secure'],
                self::$params['httponly']
            );
        }
        return session_destroy();
    }

    /**
     * session状态
     * @return bool
     */
    public static function isActive()
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * 设置session name
     * @param string $value
     * @return string
     */
    protected function name($value = '')
    {
        if (!empty($value) && function_exists('ctype_alnum')) {
            if (!ctype_alnum($value)) {
                $value = '';
            }
        } elseif (!empty($value) && !preg_match('/^[a-zA-Z0-9]+$/', $value)) {
            $value = '';
        }
        return empty($value) ? session_name() : session_name($value);
    }


    /**
     * session元数据设置
     */
    protected function meta()
    {
        $meta = isset($_SESSION['meta']);
        if (!$meta) {
            $_SESSION['meta'] = array(
                'name' => $this->name(),
                'created' => time(),
                'updated' => time()
            );
        } else {
            $_SESSION['meta']['updated'] = time();
        }
    }

    /**
     * 设置session
     * @param $data
     * @param null $value
     */
    public function set_data($data, $value = null)
    {
        $this->start();
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $_SESSION[$key] = $value;
            }
        } else {
            $_SESSION[$data] = $value;
        }
    }

    /**
     * 获取session的值
     * @param null $key
     * @return array|null
     */
    public function get_data($key = null)
    {
        $this->start();
        if (is_null($key) or empty($key)) {
            return is_array($_SESSION) ? $_SESSION : [];
        }
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    /**
     * 删除指定session值
     * @param $data
     */
    public function unset_data($data)
    {
        $this->start();
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                unset($_SESSION[$key]);
            }
        } else {
            unset($_SESSION[$data]);
        }
    }

    /**
     * 初始化session设置
     * @param array $config
     */
    protected function sessionConfigHandler($config = [])
    {
        if ($config == []) {
            self::$params = session_get_cookie_params();
        } else {
            self::$params = array_change_key_case($config);
        }
        if (isset(self::$config['sess_name'])) {
            self::$params['sess_name'] = self::$config['sess_name'];
        } else {
            self::$params['sess_name'] = '';
        }
        session_set_cookie_params(
            self::$params['cookie_lifetime'],
            self::$params['cookie_path'],
            self::$params['cookie_domain'],
            self::$params['cookie_secure'],
            TRUE
        );
    }

}