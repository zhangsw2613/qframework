<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\security;

use qframework;
use qframework\exception\QException;

class Security
{

    private $input = "";
    private static $httpCookie = null;
    protected $_xss_hash;
    protected $_never_allowed_str = array(
        'document.cookie' => '[removed]',
        'document.write' => '[removed]',
        '.parentNode' => '[removed]',
        '.innerHTML' => '[removed]',
        '-moz-binding' => '[removed]',
        '<!--' => '&lt;!--',
        '-->' => '--&gt;',
        '<![CDATA[' => '&lt;![CDATA[',
        '<comment>' => '&lt;comment&gt;'
    );
    protected $_never_allowed_regex = array(
        'javascript\s*:',
        '(document|(document\.)?window)\.(location|on\w*)',
        'expression\s*(\(|&\#40;)', // CSS and IE
        'vbscript\s*:', // IE, surprise!
        'wscript\s*:', // IE
        'jscript\s*:', // IE
        'vbs\s*:', // IE
        'Redirect\s+30\d',
        "([\"'])?data\s*:[^\\1]*?base64[^\\1]*?,[^\\1]*?\\1?"
    );

    protected $normal_patterns = array(
        '\'' => '&lsquo;',
        '"' => '&quot;',
        '&' => '&amp;',
        '<' => '&lt;',
        '>' => '&gt;',
        //possible SQL injection remove from string with there is no '
        'SELECT * FROM' => '',
        'SELECT(' => '',
        'SLEEP(' => '',
        'AND (' => '',
        ' AND' => '',
        '(CASE' => ''
    );
    protected $_csrf_hash;
    protected $_csrf_expire = 7200;
    protected $_csrf_token_name = 'q_csrf_token'; //固定值
    protected $_csrf_cookie_name = 'q_csrf_cookie';

    public function __construct()
    {
        if (self::$httpCookie == null) {
            self::$httpCookie = QFramework::$container->singleton('qframework\http\Cookie');
        }
        $this->_enable_csrf = (QFramework::$config->get('csrf_filter') == true);
        if ($this->_enable_csrf === TRUE && !IS_CLI) {
            foreach (array('csrf_expire', 'csrf_token_name', 'csrf_cookie_name') as $key) {
                if (NULL !== ($val = QFramework::$config->get($key))) {
                    $this->{'_' . $key} = $val;
                }
            }
            $this->_csrf_set_hash();
        }
    }

    /**
     * xss过滤
     * @param $str string
     * @return string
     */
    public function xss_clean($str)
    {
        if (is_array($str)) {
            while (list($key) = each($str)) {
                $str[$key] = $this->xss_clean($str[$key]);
            }
            return $str;
        }
        $this->input = $str;
        $this->remove_invisible_characters($this->input);
        do {
            $this->input = rawurldecode($this->input);
        } while (preg_match('/%[0-9a-f]{2,}/i', $this->input));
        $this->input = preg_replace_callback("/[^a-z0-9>]+[a-z0-9]+=([\'\"]).*?\\1/si", array($this, '_convert_attribute'), $this->input);
        $this->input = preg_replace_callback('/<\w+.*/si', array($this, '_decode_entity'), $this->input);
        $this->remove_invisible_characters($this->input);
        $this->input = str_replace("\t", ' ', $this->input);
        $this->input = $this->_do_never_allowed($str);
        $this->normal_replace();
        return $this->input;
    }

    protected function _convert_attribute($match)
    {
        return str_replace(array('>', '<', '\\'), array('&gt;', '&lt;', '\\\\'), $match[0]);
    }

    protected function _decode_entity($match)
    {
        // Protect GET variables in URLs
        // 901119URL5918AMP18930PROTECT8198
        $match = preg_replace('|\&([a-z\_0-9\-]+)\=([a-z\_0-9\-/]+)|i', $this->xss_hash() . '\\1=\\2', $match[0]);

        // Decode, then un-protect URL GET vars
        return str_replace(
            $this->xss_hash(),
            '&',
            $this->entity_decode($match, $this->charset)
        );
    }

    public function entity_decode($str, $charset = NULL)
    {
        if (strpos($str, '&') === FALSE) {
            return $str;
        }

        static $_entities;

        isset($charset) OR $charset = $this->charset;
        $flag = is_php('5.4')
            ? ENT_COMPAT | ENT_HTML5
            : ENT_COMPAT;

        do {
            $str_compare = $str;

            // Decode standard entities, avoiding false positives
            if (preg_match_all('/&[a-z]{2,}(?![a-z;])/i', $str, $matches)) {
                if (!isset($_entities)) {
                    $_entities = array_map(
                        'strtolower',
                        is_php('5.3.4')
                            ? get_html_translation_table(HTML_ENTITIES, $flag, $charset)
                            : get_html_translation_table(HTML_ENTITIES, $flag)
                    );

                    // If we're not on PHP 5.4+, add the possibly dangerous HTML 5
                    // entities to the array manually
                    if ($flag === ENT_COMPAT) {
                        $_entities[':'] = '&colon;';
                        $_entities['('] = '&lpar;';
                        $_entities[')'] = '&rpar;';
                        $_entities["\n"] = '&newline;';
                        $_entities["\t"] = '&tab;';
                    }
                }

                $replace = array();
                $matches = array_unique(array_map('strtolower', $matches[0]));
                foreach ($matches as &$match) {
                    if (($char = array_search($match . ';', $_entities, TRUE)) !== FALSE) {
                        $replace[$match] = $char;
                    }
                }

                $str = str_ireplace(array_keys($replace), array_values($replace), $str);
            }

            // Decode numeric & UTF16 two byte entities
            $str = html_entity_decode(
                preg_replace('/(&#(?:x0*[0-9a-f]{2,5}(?![0-9a-f;])|(?:0*\d{2,4}(?![0-9;]))))/iS', '$1;', $str),
                $flag,
                $charset
            );
        } while ($str_compare !== $str);
        return $str;
    }

    public function xss_hash()
    {
        if ($this->_xss_hash === NULL) {
            $rand = $this->get_random_bytes(16);
            $this->_xss_hash = ($rand === FALSE)
                ? md5(uniqid(mt_rand(), TRUE))
                : bin2hex($rand);
        }

        return $this->_xss_hash;
    }

    public function get_random_bytes($length)
    {
        if (empty($length) OR !ctype_digit((string)$length)) {
            return FALSE;
        }

        // Unfortunately, none of the following PRNGs is guaranteed to exist ...
        if (defined('MCRYPT_DEV_URANDOM') && ($output = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM)) !== FALSE) {
            return $output;
        }


        if (is_readable('/dev/urandom') && ($fp = fopen('/dev/urandom', 'rb')) !== FALSE) {
            // Try not to waste entropy ...
            is_php('5.4') && stream_set_chunk_size($fp, $length);
            $output = fread($fp, $length);
            fclose($fp);
            if ($output !== FALSE) {
                return $output;
            }
        }

        if (function_exists('openssl_random_pseudo_bytes')) {
            return openssl_random_pseudo_bytes($length);
        }

        return FALSE;
    }

    protected function _do_never_allowed($str)
    {
        $str = str_replace(array_keys($this->_never_allowed_str), $this->_never_allowed_str, $str);

        foreach ($this->_never_allowed_regex as $regex) {
            $str = preg_replace('#' . $regex . '#is', '[removed]', $str);
        }

        return $str;
    }

    private function normal_replace()
    {
        $this->input = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $this->input);
        foreach ($this->normal_patterns as $pattern => $replacement) {
            $this->input = str_replace($pattern, $replacement, $this->input);
        }
        if (strpos($this->input, '&amp;#') !== false) {
            $this->input = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $this->input);
        }
        $this->input = preg_replace('#(alert|prompt|confirm|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si',
            '\\1\\2&#40;\\3&#41;',
            $this->input);
    }

    /**
     * 去除不可见字符
     * @param $str string
     */
    private function remove_invisible_characters(&$str)
    {
        $pattern = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';    // 00-08, 11, 12, 14-31, 127
        $count = 0;
        do {
            $str = preg_replace($pattern, '', $str, -1, $count);
        } while ($count);
    }

    /**
     *
     */
    public function csrf_clean()
    {
        if (!IS_POST) {
            return $this->csrf_set_cookie();
        }
        //如果是post请求验证csrf
        if (!isset($_POST[$this->_csrf_token_name], $_COOKIE[self::$httpCookie->getPrefix() . $this->_csrf_cookie_name])
            OR $_POST[$this->_csrf_token_name] !== $_COOKIE[self::$httpCookie->getPrefix() . $this->_csrf_cookie_name]
        ) // Do the tokens match?
        {
            throw new QException("访问被拒绝：令牌验证失败！");
        }
        unset($_POST[$this->_csrf_token_name], $_COOKIE[self::$httpCookie->getPrefix() . $this->_csrf_cookie_name]);
        $this->_csrf_hash = NULL;
        $this->_csrf_set_hash();
        $this->csrf_set_cookie();
        //TODO:log
        return $this;
    }

    /**
     * set csrf hash
     * @return string
     */
    protected function _csrf_set_hash()
    {
        if ($this->_csrf_hash === NULL) {
            $csrf_cookie_name = self::$httpCookie->getPrefix() . $this->_csrf_cookie_name;
            if (isset($_COOKIE[$csrf_cookie_name]) && is_string($_COOKIE[$csrf_cookie_name])
                && preg_match('#^[0-9a-f]{32}$#iS', $_COOKIE[$csrf_cookie_name]) === 1
            ) {
                return $this->_csrf_hash = $_COOKIE[$csrf_cookie_name];
            }

            $rand = $this->get_random_bytes(16);
            $this->_csrf_hash = ($rand === FALSE)
                ? md5(uniqid(mt_rand(), TRUE))
                : bin2hex($rand);
        }

        return $this->_csrf_hash;
    }

    /**
     * set csrf cookie
     * @return $this|bool
     */
    public function csrf_set_cookie()
    {
        $expire = time() + $this->_csrf_expire;
        if (IS_CLI) {
            return FALSE;
        }
        self::$httpCookie->set($this->_csrf_cookie_name, $this->_csrf_hash, $expire);
        return $this;
    }

    public function get_csrf_token_name()
    {
        return $this->_csrf_token_name;
    }

    public function get_csrf_hash()
    {
        return $this->_csrf_hash;
    }
}