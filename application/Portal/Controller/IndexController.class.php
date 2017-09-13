<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace Portal\Controller;
use Common\Controller\HomeBaseController; 
/**
 * 首页
 */
class IndexController extends HomeBaseController {
	
    //首页
	public function index() {
		$userid=sp_get_current_userid();
		$addus =  M('Addus')->where('users_id='.$userid)->find();
		$this->assign('addus',$addus);
    	$this->display(":index");
    }   
}


