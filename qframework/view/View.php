<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */
namespace qframework\view;

use qframework;
use qframework\exception\QException;

class View
{

    protected $driver = '';
    protected $theme = '';
    protected $path = '';
    protected $suffix = '';
    protected $prefix = '';
    protected $expire = 0;
    protected $vars = [];
    protected $file = '';
    protected $cachePath = '';
    protected $cacheFile = '';
    protected $dispatch;
    protected $template = null;

    public function __construct()
    {
        $config = QFramework::$config->get('view');
        $this->driver = isset($config['tpl_driver']) ? $config['tpl_driver'] : 'php';
        $this->theme = isset($config['tpl_theme']) ? $config['tpl_theme'] : 'default';
        $this->path = isset($config['tpl_path']) ? $config['tpl_path'] : APP_PATH . DS . 'views';
        $this->suffix = isset($config['tpl_suffix']) ? $config['tpl_suffix'] : '.phtml';
        $this->prefix = isset($config['tpl_prefix']) ? $config['tpl_prefix'] : 'q_cache_';
        $this->expire = isset($config['tpl_expire']) ? $config['tpl_expire'] : 0;
        $this->cachePath = isset($config['tpl_cache_path']) ? $config['tpl_cache_path'] : BASE_PATH . DS . 'sys_cache/tpl';
        $this->dispatch = QFramework::getComponent('router')->getDispatch();
    }

    /**
     * 模板赋值
     * @param $key
     * @param $value
     * @return null
     */
    public function assign($key, $value)
    {
        if ($key === null) {
            return null;
        }
        $this->vars[$key] = $value;
    }

    /**
     * 解析模板并输出页面
     * @param string $fileName
     * @param string $charset
     * @param string $contentType
     */
    public function display($fileName = '', $charset = '', $contentType = '')
    {
        $content = $this->fetch($fileName);
        $this->render($content, $charset, $contentType);
    }

    public function fetch($fileName)
    {
        $this->loadTemplate($fileName);
        if (!is_file($this->file)) {
            throw new QException("模板文件{$this->file}不存在");
        }
        return $this->parseTemplate();
    }

    /**
     * 载入模板文件
     * @param $file
     */
    public function loadTemplate($file)
    {
        if (is_file($file)) {
            $this->file = $file;
        }
        if ($file == '') {
            $module = $this->dispatch->getModuleName();
            $controller = $this->dispatch->getControllerName();
            $action = $this->dispatch->getActionName();
            $file = $this->path . DS . $this->theme . DS . ($module == '' ? '' : $module . DS) . $controller . DS . $action . $this->suffix;
        } else {
            $name = $file;
            $suffix = '';
            if (strpos($file, ".") !== false) {
                list($name, $suffix) = explode(".", $file);
            }
            $file = $this->path . DS . $this->theme . DS . ltrim($name, DS) . (empty($suffix) ? $this->suffix : '.' . $suffix);
        }
        $this->file = APP_PATH . DS . $file;
        $this->cachePath .=  DS . $module . DS;
        $this->cacheFile = $this->cachePath . $this->prefix . md5($this->file) . $this->suffix;
    }

    public function parseTemplate()
    {
        ob_start();
        ob_implicit_flush(0);
        if ('php' == $this->driver) {
            extract($this->vars, EXTR_OVERWRITE);
            //直接加载缓存文件
            include $this->file;
        } else {
            if (!QFramework::$isDebug && $this->expire > 0 && $this->checkCache()) {
                //非调试模式下如果缓存文件可用直接载入缓存文件
                $this->getFile($this->cacheFile);
            } else {
                //编译模板，写入缓存文件
                $this->template = QFramework::$container->singleton('qframework\view\Drivers\\' . ucfirst($this->driver));
                $this->template->tVars = $this->vars;
                $_content = $this->template->fetch($this->file);//编译后的模板文件
                if (!$this->putFile($_content)) {
                    throw new QException("编译文件生成失败");
                }
                unset($_content);
                $this->getFile($this->cacheFile);
            }
        }
        $content = ob_get_clean();
        return $content;
    }

    /**
     * 输出内容
     * @param $content
     * @param $charset
     * @param $contentType
     */
    public function render($content, $charset, $contentType)
    {
        if (empty($charset)) $charset = 'utf-8';
        if (empty($contentType)) $contentType = 'text/html';
        // 网页字符编码
        header('Content-Type:' . $contentType . '; charset=' . $charset);
        header('Cache-Control: no-cache, must-revalidate');  // 页面缓存控制
        header('Pragma: no-cache');
        header('X-Powered-By:QFramework');
        // 输出模板文件
        echo $content;
    }

    /**
     * 检测缓存文件是否有效
     */
    private function checkCache()
    {
        if (!is_file($this->cacheFile)) {
            return false;
        }
        if (filemtime($this->file) + $this->expire < time()) {
            return false;
        }
        return true;
    }

    /**
     * 加载文件
     * @param string $file_name
     */
    private function getFile($file_name = '')
    {
        if (!is_null($this->vars)) {
            extract($this->vars, EXTR_OVERWRITE);
        }
        include $file_name;
    }

    /**
     * 写入缓存文件
     * @param string $content
     * @return bool|int|void
     */
    private function putFile($content = '')
    {
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0777, true);
        }
        return file_put_contents($this->cacheFile, $content);
    }


}