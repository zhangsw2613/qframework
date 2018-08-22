<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\view\Taglib;

use qframework\exception\QException;

/**
 * 标签库实现了几个比较运算符,用法参考thinkphp
 * Class Taglib
 * @package qframework\view\Taglib
 */
class Taglib
{

    protected $tags = array(
        'eq' => array('attr' => 'name,value', 'level' => 3, 'close' => true),
        'neq' => array('attr' => 'name,value', 'level' => 3, 'close' => true),
        'gt' => array('attr' => 'name,value', 'level' => 3, 'close' => true),
        'lt' => array('attr' => 'name,value', 'level' => 3, 'close' => true),
        'egt' => array('attr' => 'name,value', 'level' => 3, 'close' => true),
        'elt' => array('attr' => 'name,value', 'level' => 3, 'close' => true),
        'else' => array('attr' => '', 'level' => 1, 'close' => false),
    );
    protected $comparison = array('eq' => ' == ', 'neq' => ' != ', 'gt' => ' > ', 'lt' => ' < ', 'egt' => ' >= ', 'elt' => ' <= ');

    public function parse($tag, $attr, $content)
    {
        $tag = strtolower($tag);
        if (array_key_exists($tag, $this->tags)) {
            $attrs = array_filter(explode(',', $this->tags[$tag]['attr']));
        } else {
            throw new QException("标签{$tag}不存在");
        }
        $func = '_' . $tag;
        $parseStr = $this->$func($attrs, $attr, $content);
        return $parseStr;
    }

    protected function _compare($attrs, $attr, $content, $type = 'eq')
    {
        foreach ($attrs as $item) {
            preg_match('/' . $item . '=\"([\S\s].+?)\"/i', $attr, $matches);
            $parse_arr[] = $matches[1];
        }
        $parseStr = '<?php if((' . $parse_arr[0] . ') ' . $this->comparison[$type] . ' ' . $parse_arr[1] . '): ?>' . $content . '<?php endif; ?>';
        return $parseStr;
    }

    protected function _eq($attrs, $attr, $content)
    {
        return $this->_compare($attrs, $attr, $content, 'eq');
    }

    protected function _neq($attrs, $attr, $content)
    {
        return $this->_compare($attrs, $attr, $content, 'neq');
    }

    protected function _gt($attrs, $attr, $content)
    {
        return $this->_compare($attrs, $attr, $content, 'gt');
    }

    protected function _lt($attrs, $attr, $content)
    {
        return $this->_compare($attrs, $attr, $content, 'lt');
    }

    protected function _egt($attrs, $attr, $content)
    {
        return $this->_compare($attrs, $attr, $content, 'egt');
    }

    protected function _elt($attrs, $attr, $content)
    {
        return $this->_compare($attrs, $attr, $content, 'elt');
    }

    protected function _else()
    {
        $parseStr = '<?php else: ?>';
        return $parseStr;
    }

    public function getTags()
    {
        return $this->tags;
    }
}