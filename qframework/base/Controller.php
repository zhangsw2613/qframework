<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\base;

use qframework;

class Controller extends Base
{
     /**
     * @var qframework\view\View
     */
    protected  $view = null;

    public function __construct()
    {
        parent::__construct();
        $this->view = QFramework::getComponent('view');
    }
}
