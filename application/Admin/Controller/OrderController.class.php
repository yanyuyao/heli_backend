<?php
/**
 * Created by PhpStorm.
 * User: 108496
 * Date: 2017/7/25
 * Time: 17:17
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class OrderController extends AdminbaseController{
    protected $order_model;
    protected $otime_model;
    protected $config_model;
    protected $users_model;
    function _initialize() {
        parent::_initialize();
        $this->order_model = D("Common/Order");
        $this->otime_model = D("Common/Otime");
        $this->config_model = D("Common/Config");
	$this->users_model = D("Common/Users");
    }

    function index(){
	$admin_id = $_SESSION['ADMIN_ID'];
	$show_zixun = 0;
	$role_user = M('RoleUser')->where("user_id=".$admin_id." and role_id = 1")->find();
	if($role_user && $role_user['role_id'] == 1){
		$show_zixun = 1;
	}
	if($admin_id == 1){
		$show_zixun = 1;
	}
	$this->assign("show_zixun",$show_zixun);
        $this->_lists();
        $this->display();
    }
    function tconsult(){
	$body = I('get.body');
	$this->assign("body",$body);
	$this->display();
    }
    function edit(){
        $id=I("get.id");
        $order=$this->order_model->field('order_id,status,kefu,guwen,order_opinion')->where("order_id=".$id)->find();
        $this->assign('order',$order);
        $this->display();
    }

    function edit_post(){
	if (IS_POST) {
            if ($this->order_model->create()) {
                if ($this->order_model->save()!==false) {
                    //$this->success("保存成功！", U("order/index"));
                    echo 1;
                    exit;
                } else {
                    $this->error("保存失败！");
                }
            } else {
                $this->error($this->order_model->getError());
            }
        }
    }
	function aa(){echo 555;}
    function setTime(){
        $otime=$this->otime_model->field('htime')->find();
        $order_limit_day =$this->config_model->field('svalue')->where('skey="order_limit_day"')->find();
        $order_limit_hours =$this->config_model->field('svalue')->where('skey="order_limit_hours"')->find();
	$this->assign('otime',$otime);
	$this->assign('order_limit_day',$order_limit_day['svalue']);
	$this->assign('order_limit_hours',$order_limit_hours['svalue']);
        $this->display('setTime');
    }

    public function doSetTime()
    {
        if (IS_POST) {
            $htime = I('post.htime');
	    $order_limit_day = I('post.order_limit_day');
            $order_limit_hours = I('post.order_limit_hours');
	    $this->otime_model->where("otime_id=1")->save(array("htime"=>$htime));	
	    echo $this->config_model->where("skey = 'order_limit_day'")->save(array("svalue"=>$order_limit_day));	
	    $this->config_model->where("skey = 'order_limit_hours'")->save(array("svalue"=>$order_limit_hours));	
		echo 1;
		exit;	
	    /*	
	    if ($this->otime_model->create()) {
                if ($this->otime_model->save() !== false) {
                    //$this->success("保存成功！", U("order/index"));
			echo 1;
			exit;
                } else {
                    $this->error("保存失败！");
                }
            } else {
                $this->error($this->otime_model->getError());
            }
            */
        }
    }
    /**
     *  删除
     */
    function delete(){
        $id = I("get.id",0,"intval");
        if ($this->order_model->delete($id)!==false) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }


    /*状态改变*/
    function changestatus(){
        $order['order_id'] = $_GET['id'];
        $status=$_GET['status']==1?0:1;
        $submit = $_GET['status']==1?'已下架':'已上架';
        $order['status'] = $status;

        if ($this->order_model->save($order)!==false) {
            $this->success($submit, U("order/index"));
        } else {
            $this->error("失败！");
        }
    }


    /*列表*/
    private  function _lists($status=1){

        $fields=array(
            'serve_name'  => array("field"=>"serve_name","operator"=>"like"),
            'order_num'  => array("field"=>"order_num","operator"=>"like"),
        );
        $where_ands = array();
        if(IS_POST){
            foreach ($fields as $param =>$val){
                if (isset($_POST[$param]) && !empty($_POST[$param])) {
                    $operator=$val['operator'];
                    $field   =$val['field'];
                    $get=$_POST[$param];
                    $_GET[$param]=$get;
                    if($operator=="like"){
                        $get="%$get%";
                    }
                    array_push($where_ands, "$field $operator '$get'");
                }
            }
        }else{
            foreach ($fields as $param =>$val){
                if (isset($_GET[$param]) && !empty($_GET[$param])) {
                    $operator=$val['operator'];
                    $field   =$val['field'];
                    $get=$_GET[$param];
                    if($operator=="like"){
                        $get="%$get%";
                    }
                    array_push($where_ands, "$field $operator '$get'");
                }
            }
        }

        $where= join(" and ", $where_ands);
        //echo $where;

        $count=$this->order_model->where($where)->count();

        $page = $this->page($count,20);


        //$posts=$this->order_model->whorder("order_id DESC")->select();

        $posts=$this->order_model
            ->where($where)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->order("order_id DESC")->select();
	if($posts){
		foreach($posts as $k=>&$v){
			$userinfo = $this->users_model->field('openId as openid,province,city,area')->where("id = ".$v['users_id'])->find();
			$v['openId'] = $userinfo['openid'];
			$v['province'] = $userinfo['province'];
			$v['city'] = $userinfo['city'];
			$v['area'] = $userinfo['area'];
		}
	}
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page",$page->GetCurrentPage());
        unset($_GET[C('VAR_URL_PARAMS')]);
        $this->assign("formget",$_GET);
        $this->assign("orders",$posts);
    }





}
