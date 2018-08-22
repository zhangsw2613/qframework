<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */
namespace controllers\admin;
use qframework\base\Controller;
//use libraries\test\T;调用系统类库
use demo\models\users\UserInfo;
class Index extends Controller
{
	public function __construct()
	{
		parent::__construct();
		//$test = new T();
		//$test->rr();exit;
		//$this->load->library('page','helpers');
		//print_r($this->config->get('site_name'));exit;
		//print_r($this->input->post());exit;
	}
	public function before_index()
	{//print_r($_REQUEST);
		//print_r($this->input->get('test'));exit;
		echo 11;
	}

	public function after_index()
	{
		echo 33;
	}

	public function t1(){
		//UserInfo::test();
		//exit;
        //$res = $this->cache->get('key1');
		//print_r($res);exit;
		//$this->cache->clean();exit;
		//var_dump($this->cache->is_supported());exit;
		//$this->cache->set('key1',['a'=>'a1','b'=>'b1'],3600);exit;
		//$this->cache->file->set('key1',['a'=>'a1','b'=>'b1'],3600);
		//$this->cache->redis->set('key1',['a'=>'a1','b'=>'b1'],3600);
		//var_dump($this->cache->memcached->set('key1',['a'=>'a1','b'=>'b1'],3600));exit;
		//
		//echo json_encode(['enabled'=>0,'menulist'=>[]]);exit;
		$this->view->assign("test_str","fffffff");
		$this->view->assign("test_num",123);
		$this->view->assign("test_arr",['t1'=>'fff','t2'=>'方法反反复复']);
		$this->view->display();
		//$this->view->display('test/index');
		//$this->view->display('test/index.html');
		//$this->view->display('index.html');
		//$this->view->display('index');
		//$this->view->display('index.html');
		exit;
		$this->cache->apc->set('key1',['a'=>'a1','b'=>'b1'],3600);exit;
		//echo $this->cookie->get('test');exit;
		//$this->session->destory();exit;
		//print_r($this->session->get_data()) ;exit;
		$this->session->set_data('test',44);
		//$this->session->set_data('test2',222);
		//$this->session->set_data('test33',333);
		//$this->session->set_data('test3',777);
		echo $this->session->get_data('test3');
		echo $this->session->get_data('test33');
		var_dump($_SESSION);exit;
		echo $this->url->redirect('/admin-index-index?t=2&q=key');
	}
	public function t2(){
		echo 33;exit;
	}
	public function testUpper()
	{
		//echo is_php();exit;调用用户自定义common类库中函数
		//$page = new \page();调用辅助函数
		//$page->test();
		//$this->controller->get('index','index','test',['page'=>['11','22'],'ddd']);执行另一个控制器的方法
		//$this->user->test(array("key1"=>1,'key2'=>2));//调用model中的方法
		//$this->userinfo->test(array("key1"=>1,'key2'=>2));//调用model中的方法
		//$this->url->redirect('/admin/index/index?t=2&q=key');
		echo $this->url->create('/admin-index-t2');exit;
		echo "testUpper";exit;
	}

	public function index()
	{
		echo 22;
	}
}