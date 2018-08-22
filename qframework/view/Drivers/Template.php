<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\view\Drivers;

use qframework;
use qframework\exception\QException;
use qframework\view\Taglib;

/**
 * 系统内置模板引擎，第三方模板引擎请放在同一目录下
 * Class Template
 * @package qframework\view\Drivers
 */
class Template
{
    protected $tVars;

    public function __set($name, $value)
    {
        $this->tVars = $value;
    }

    public function fetch($file_name)
    {
        if (is_file($file_name)) {
            $content = file_get_contents($file_name);
        } else {
            throw new QException("模板文件{$file_name}不存在");
        }
        $content = $this->parse($content);;
        // 优化生成的php代码
        return str_replace('?><?php', '', $content);
    }

    protected function parse($content)
    {
        if (empty($content)) return '';
        //系统内置标签库解析
        $this->parseTagLib($content);
        //常规标签解析
        $content = preg_replace_callback("/(\{)([^\d\s\{\}].+?)(\})/is", array($this, 'parseTag'), $content);
        return $content;
    }

    protected function parseTagLib(&$content)
    {
        $taglib = QFramework::$container->singleton('qframework\view\Taglib\Taglib');
        foreach ($taglib->getTags() as $tag => $tval) {
            if ($tval['close']) {
                $patterns = '/<' . $tag . '\s([^>]*)>(.*?)<\/' . $tag . '(\s*?)>/is';
            } else {
                $patterns = '/<' . $tag . '(\s*?)\/>/is';
            }
            for ($l = 0; $l < $tval['level']; $l++) {
                $content = preg_replace_callback($patterns, function ($matches) use ($taglib, $tag, $content) {
                    return $taglib->parse($tag, $matches[1], $matches[2]);
                }, $content);
            }
        }
    }

    protected function parseTag($tagStr)
    {
        if (is_array($tagStr)) $tagStr = $tagStr[0];
        $keys = array(
            '{if %%}' => '<?php if (\1): ?>',
            '{elseif %%}' => '<?php ; elseif (\1): ?>',
            '{for %%}' => '<?php for (\1): ?>',
            '{foreach %%}' => '<?php foreach (\1): ?>',
            '{while %%}' => '<?php while (\1): ?>',
            '{/if}' => '<?php endif; ?>',
            '{/for}' => '<?php endfor; ?>',
            '{/foreach}' => '<?php endforeach; ?>',
            '{/while}' => '<?php endwhile; ?>',
            '{else}' => '<?php ; else: ?>',
            '{continue}' => '<?php continue; ?>',
            '{break}' => '<?php break; ?>',
            '{$%% = %%}' => '<?php $\1 = \2; ?>',
            '{$%%++}' => '<?php $\1++; ?>',
            '{$%%--}' => '<?php $\1--; ?>',
            '{$%%}' => '<?php echo $\1; ?>',
            '{comment}' => '<?php /*',
            '{/comment}' => '*/ ?>',
            '{/*}' => '<?php /*',
            '{*/}' => '*/ ?>',
        );

        foreach ($keys as $key => $val) {
            $patterns[] = '/' . str_replace('%%', '(.+)',
                    preg_quote($key, '/')) . '/U';
            $replace[] = $val;
        }
        return preg_replace($patterns, $replace, $tagStr);
    }
}