<?php
/**
 * Created by PhpStorm.
 * User: 108496
 * Date: 2017/7/27
 * Time: 10:15
 */
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace Portal\Controller;
use Common\Controller\HomeBaseController;
/*加入我们接口*/
class InterfaceController extends HomeBaseController {

    protected $addus_model;
    protected $ad_model;
    protected $serve_model;
    protected $users_model;
    protected $reward_model;
    protected $options_model;
    protected $vsample_model;
    function _initialize(){
        parent::_initialize();
        $this->addus_model=D("Common/addus");
        $this->ad_model=D("Common/ad");
        $this->serve_model=D("Common/serve");
        $this->users_model=D("Common/Users");
        $this->reward_model=D("Common/reward");
        $this->options_model = D("Common/Options");
        $this->vsample_model = D("Common/Vsample");
    }
    /** 加入我们 */
 public function addus(){
     $userid=$_POST['user_id'];
	 unset($_POST['user_id']);
     if(empty($userid)){
         $status = 5000;
         $msg = '未登录，请先登录';
     }else{
         if (IS_POST) {
             $addus =  $this->addus_model->where('users_id='.$userid)->find();
             if($addus['users_id']){
                 if($addus['status']==2){
                     $status = 5003;
                     $msg = '改用户已加入,并审核通过';
                 }else{
                     $_POST['addus_createtime'] = date('Y-m-d H:i:s',time());
                     if ($this->addus_model->create()) {
                         if ($this->addus_model->where("users_id=$userid")->save()) {
                             $status=1003;
                             $msg = '操作成功';
                         } else {
                             $status=1004;
                             $msg = '操作失败';
                         }
						// echo $this->addus_model->_sql();
                     } else {
                         $status = 0;
                         $msg = '参数错误';
                     }
                 }
             }else{
                 $_POST['users_id'] = $userid;
                 if ($this->addus_model->create()) {
                     if ($result = $this->addus_model->add()) {
                         $status=1003;
                         $msg = '操作成功';
                     } else {
                         $status=1004;
                         $msg = '操作失败';
                     }
                 } else {
                     $status = 0;
                     $msg = '参数错误';
                 }
             }
         }else{
            $status = 0;
            $msg = '参数错误';
        }
     }
     $data = array('status'=>$status,'msg'=>$msg,'addus_id'=>$result);
     echo json_encode($data);
	 exit;
 }


    /*加入我们信息*/
    public function edit_addus(){
        $userid=$_POST['user_id'];
        $addus =  $this->addus_model->where('users_id='.$userid)->find();
        if(empty($addus['addus_id'])){
            $status = 5001;
            $msg = '用户还未加入';
        }else{
            $status = 1003;
            $msg = '操作成功';
        }
        $data = array('status'=>$status,'data'=>$addus);
        echo json_encode($data);exit;
    }

    /*导航*/
    public function bannerList(){
        $bList = $this->ad_model->field('ad_url')->where('status=1')->select();
        if(empty($bList)){
            $status = 1002;
            $msg = '数据为空';
        }else{
            $status = 1001;
            $msg = '返回数据成功';
        }
        $data = array('status'=>$status,'msg'=>$msg,'data'=>$bList);
        echo json_encode($data);
		exit;
    }
    /*上架服务列表接口*/
    public function serveList(){

        //echo $where;
        $count=$this->serve_model->where('status=1')->count();

        $page = $this->page($count,10);

        if(empty($count)){
            $status = 1002;
            $msg = '数据为空';
            $data = array('status'=>$status,'msg'=>$msg);
        }else{
            $serveList=$this->serve_model->where('status=1')
                ->limit($page->firstRow . ',' . $page->listRows)
                ->order("serve_id DESC")->select();
                $status = 1001;
                $msg = '返回数据成功';
            $data = array('status'=>$status,'msg'=>$msg,'data'=>$serveList,'page'=>$page->show('Admin'),'current_page'=>$page->GetCurrentPage());
        }
        //return json_encode($data);
		echo json_encode($data);
		exit;
    }

    /*打赏我们*/
    public function reward(){
        $userInfo = array();
        if(IS_POST){
            if(empty($_POST['user_id'])){
                $status = 5000;
                $msg = '未认证';
            }else{
                $userInfo = $this->users_model->where('id='.$_POST['user_id'])->find();
                if(empty($userInfo)){
                    $status = 2000;
                    $msg = '用户不存在';
                }else{
                    $_POST['reward_createtime'] = date('Y-m-d H:i:s');
                    if($this->reward_model->create()){
                        if($result = $this->reward_model->add()){
                            $status = 1002;
                            $msg = '打赏成功';
                        }
                    }else{
                        $status = 0;
                        $msg = '参数错误';
                    }

                }
            }
        }else{
            $status = 0;
            $msg = '参数错误';
        }
        $data = array('status'=>$status,'msg'=>$msg,'data'=>$result);
       //return json_encode($data);
	   echo json_encode($data);
		exit;
    }

    /*打赏我们上传图片接口*/
   public function getReward(){
       $option=$this->options_model->where("option_name='site_options'")->find();
       $cmf_settings=$this->options_model->where("option_name='cmf_settings'")->getField("option_value");
       $tpls=sp_scan_dir(C("SP_TMPL_PATH")."*",GLOB_ONLYDIR);
       $noneed=array(".","..",".svn");
       $tpls=array_diff($tpls, $noneed);
       $this->assign("templates",$tpls);

       $adminstyles=sp_scan_dir(SPSTATIC."simpleboot/themes/*",GLOB_ONLYDIR);
       $adminstyles=array_diff($adminstyles, $noneed);
       $site = (array)json_decode($option['option_value']);
       $about = $site['site_reward'];
       if(empty($about)){
           $status = 1002;
           $msg = '数据为空';
       }else{
           $status = 1001;
           $msg = '返回数据成功';
       }

       $data = array('status'=>$status,'msg'=>$msg,'reward_url'=>$about);
      echo  json_encode($data);
       exit;
    //  return json_encode($data);
    }
    /*关于我们接口*/
    public function getAbout(){
        $option=$this->options_model->where("option_name='site_options'")->find();
        $cmf_settings=$this->options_model->where("option_name='cmf_settings'")->getField("option_value");
        $tpls=sp_scan_dir(C("SP_TMPL_PATH")."*",GLOB_ONLYDIR);
        $noneed=array(".","..",".svn");
        $tpls=array_diff($tpls, $noneed);
        $this->assign("templates",$tpls);

        $adminstyles=sp_scan_dir(SPSTATIC."simpleboot/themes/*",GLOB_ONLYDIR);
        $adminstyles=array_diff($adminstyles, $noneed);
        $site = (array)json_decode($option['option_value']);
        $about = $site['site_about'];
        if(empty($about)){
            $status = 1002;
            $msg = '数据为空';
        }else{
            $status = 1001;
            $msg = '返回数据成功';
        }

        $data = array('status'=>$status,'msg'=>$msg,'about'=>$about);
        echo  json_encode($data);
        exit;
    }


    /*语音示例列表*/
    public function VsampleList(){
        $vsample = $this->vsample_model->where('vs_status=1')->select();
        if(empty($vsample)){
            $status = 1002;
            $msg = '数据为空';
        }else{
            $status = 1001;
            $msg = '返回数据成功';
        }
	$rs_data = array();
	if($vsample){
		foreach($vsample as $k=>$v){
			$rs_data[] = array(
				"name"=>$v['title'],
				"musicId"=>$k+1,
				"mp3Url"=>"https://helizixun.cn/".$v['vsample_url']
			);
		}
	}
        $data = array('status'=>$status,'msg'=>$msg,'data'=>$rs_data);
        echo json_encode($data);
        exit;
    }
}
