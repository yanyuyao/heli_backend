<?php
/**
 * Created by PhpStorm.
 * User: 108496
 * Date: 2017/7/27
 * Time: 17:35
 */
/*订单接口*/
namespace Portal\Controller;
use Common\Controller\HomeBaseController;
//require_once 'Wxpay/lib/WxPay.Api.php';
//require_once 'plugins\Wxpay\lib\WxPayapi.php';

vendor('Wxpay.WxPayApi','simplewind/Core/Library/Vendor','.php');
class OrderController extends HomeBaseController {
    protected $order_model;
    protected $serve_model;
    protected $otime_model;
    protected $config_model;
    function _initialize(){
        parent::_initialize();
        $this->order_model=D("Common/Order");
        $this->otime_model=D("Common/Otime");
        $this->serve_model=D("Common/Serve");
        $this->config_model=D("Common/Config");
		
    }
    /*下单接口*/
    public function createOrder(){
        $userid = $_REQUEST['users_id'];
        $oid = $_REQUEST['order_id'];
        $openId = $_REQUEST['usersession'];

        if($_REQUEST['order_vconsult']){ //保存路因文件

        }
		
        $orders = array();
        if(empty($userid) && empty($openId)){
			
            $status = 5000;
            $msg = '未认证,请先登录';
        }else{
            if(IS_POST){
                $serve_id = $_POST['serve_id'];
                $serveInfo = $this->serve_model->field('serve_name,serve_count')->where('serve_id='.$serve_id)->find();
				
                $where['where']['serve_id'] = array('eq',$serve_id);
                $where['where']['users_id'] = array('eq',$userid);
                //$where['where']['status'] = array('eq',2);
                $where['where']['order_createtime'] = array('gt',date('Y-m-d 00:00:00'));
                //$where['where']['order_createtime'] = array('lt',date('Y-m-d 23:59:59'));
                $count = $this->order_model->where($where['where'])->count();
				
                if($count>=$serveInfo['serve_count']){
                    $status  = 5005;
                    $msg = '达到今天限定购买次数';
                }else{
                    $createtime = date('Y-m-d H:i:s');
                    $order_num = date('Ymd').rand(1000,9999);
                    $_POST['order_createtime'] = $createtime;
                    $_POST['order_dotime'] = $createtime;
                    $_POST['order_num'] = $order_num;
                    //$_POST['names'] = $serveInfo['serve_name'];
                    $amount = isset($_POST['amount'])&&$_POST['amount']?$_POST['amount']:0;
                    $serve_name = isset($_POST['serve_name'])&&$_POST['serve_name']?$_POST['serve_name']:0;
                    
                    if($this->order_model->create()){
                        if($oid){
                            $this->order_model->where("order_id=$oid")->save();
                            $result = $oid;
                        }else{
                            $result = $this->order_model->add();
                        }
                        
                        if($result){
							if($amount >0){
								//{{{ 统一下单
								
								$input = new \WxPayUnifiedOrder();
								$input->SetBody($serve_name);
								$input->SetAttach('');
								$input->SetOut_trade_no(\WxPayConfig::MCHID.date("YmdHis"));
								$input->SetTotal_fee($amount*100);
								$input->SetTime_start(date("YmdHis"));
								$input->SetTime_expire(date("YmdHis", time() + 600));
								$input->SetGoods_tag('');
								$input->SetNotify_url("https://helizixun.cn/notify.php");
								$input->SetTrade_type("JSAPI");
								$input->SetOpenid($openId);
							
								$order = \WxPayApi::unifiedOrder($input);
								//echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
								//printf_info($order);
								//}}}
								$pay_type = 'wx';
							}else{
								$pay_type = 'dev';
							}
						
							$order['timeStamp'] = "".time();
                            $status=1003;
                            $msg = '操作成功';
                            $orders = array('order_id'=>$result,'order_num'=>$order_num,'pay_type'=>$pay_type,'wxorder'=>$order);
                        }else{
                            $status=1004;
                            $msg = '操作失败';
                        }
                    }else{
                        $status = 0;
                        $msg = '参数错误';
                    }
                }
            }else{
                $status = 0;
                $msg = '参数错误';
            }
        }
        $data = array('status'=>$status,'msg'=>$msg,'data'=>$orders);
        echo json_encode($data);
		exit;
    }

    /*订单支付状态接口*/

    public function payOrder(){
        $order_id = $_POST['order_id'];
        if(IS_POST){
            $serveInfo = $this->order_model->field('serve_id,users_id')->where('order_id='.$order_id)->find();
            $userid = $serveInfo['users_id'];
            $serve_id = $serveInfo['serve_id'];
            $serve = $this->serve_model->field('serve_count,serve_name')->where('serve_id='.$serve_id)->find();
            $serve_count = $serve['serve_count'];
            $order['order_id'] =$order_id;
            $order['status'] = 2;
            if($this->order_model->create($order)){
                if($reuslt = $this->order_model->save($order)){
                    $where['where']['serve_id'] = array('eq',$serve_id);
                    $where['where']['users_id'] = array('eq',$userid);
                    $where['where']['status'] = array('eq',2);
                    $where['where']['order_createtime'] = array('gt',date('Y-m-d 00:00:00'));
                    $where['where']['order_createtime'] = array('lt',date('Y-m-d 23:59:59'));
                    $count = $this->order_model->where($where['where'])->count();
                    $remainder = $serve_count-$count;
                    $status=1003;
                    $msg = '操作成功';
                }else{
                    $status=1004;
                    $msg = '操作失败';
                }
            }else{
                $status = 0;
                $msg = '参数错误';
            }
        }else{
            $status = 0;
            $msg = '参数错误';
        }
        $data = array('status'=>$status,'msg'=>$msg,'count'=>$count,'remainder'=>$remainder,'serve_name'=>$serve['serve_name']);
        //return json_encode($data);
		echo json_encode($data);
		exit;
    }

    /*订单列表*/
    public function orderList(){
        $userid = $_REQUEST['user_id']?$_REQUEST['user_id']:2;
   
        $orderList =  $this->order_model->alias("a")->field('a.order_id,a.order_num,b.serve_id,b.serve_title,b.serve_url,b.serve_price,a.serve_name,a.status')
				->join(C ( 'DB_PREFIX' )."serve b ON a.serve_id = b.serve_id")
				->where('a.users_id='.$userid)
				->order('a.order_id desc')
				->select();
       if(empty($orderList)){
           $status = 1002;
           $msg = '返回数据为空';
       }else{
           $count = $this->order_model->alias("a")->join(C ( 'DB_PREFIX' )."serve b ON a.serve_id = b.serve_id")->where('a.users_id='.$userid)->count();
           $page = $this->page($count, 100);
           $status = 1001;
           $msg = '返回数据成功';
       }

       // $data = array('status'=>$status,'msg'=>$msg,'data'=>$orderList,'page'=>$page->show('Admin'),'current_page'=>$page->GetCurrentPage());
        $data = array('status'=>$status,'msg'=>$msg,'data'=>$orderList,'page'=>1,'current_page'=>1);
        echo  json_encode($data);
		exit;
    }
    /**
     * 订单详情
     */
    public function orderInfo(){
        $order_id = $_REQUEST['order_id']?$_REQUEST['order_id']:2;
   
        $info =  $this->order_model->field('order_tel,order_email,names,order_vconsult,order_tconsult')->where('order_id="'.$order_id.'"')->find();
       if(empty($info)){
           $status = 1002;
           $msg = '返回数据为空';
       }else{
           $status = 1001;
           $msg = '返回数据成功';
       }

        $data = array('status'=>$status,'msg'=>$msg,'data'=>$info);
        echo  json_encode($data);
	exit;
    }
	public function getUserServiceStatus(){
		$userid = $_REQUEST['user_id']?$_REQUEST['user_id']:0;
		$serve_id = $_REQUEST['serve_id']?$_REQUEST['serve_id']:0;
                if(!$userid){
                    $status = 1002;
                    $msg = '参数错误';
                }
                $status = 1001;
                $morethan6 = 0;
                $limithours = 0;
                $order_limit_day = 0;
                $order_limit_hours = 0;
		$order_limit_day_data = $this->config_model->field('svalue')->where("skey='order_limit_day'")->find();
		$order_limit_hours_data = $this->config_model->field('svalue')->where("skey='order_limit_hours'")->find();
		if($order_limit_day_data){
			$order_limit_day = $order_limit_day_data['svalue'];
		}
		if($order_limit_hours_data){
			$order_limit_hours = $order_limit_hours_data['svalue'];
		}
		$lastorder = $this->order_model->field('order_createtime')->where('users_id='.$userid." and serve_id = ".$serve_id)->order('order_id desc')->find();
                if($lastorder){
			$otime=$this->otime_model->field('htime')->find();
			if($otime){
				$limithours = $otime['htime'];
                    		if((time()-strtotime($lastorder['order_createtime']))<$limithours*3600){
                        		$morethan6 = 1;
                    		}
			}	
                }
                $limitbuy = 0;
                $serve_id = $_POST['serve_id'];
                if($serve_id){
                    $where['where']['serve_id'] = array('eq',$serve_id);
                    $where['where']['users_id'] = array('eq',$userid);
                    //$where['where']['status'] = array('eq',2);
                    $where['where']['order_createtime'] = array('gt',date('Y-m-d 00:00:00'));
                    //$where['where']['order_createtime'] = array('lt',date('Y-m-d 23:59:59'));
                    $count = $this->order_model->where($where['where'])->count();
                    $serveInfo = $this->serve_model->field('serve_name,serve_count')->where('serve_id='.$serve_id)->find();
                    if($count>=$serveInfo['serve_count']){
                        $msg = '达到今天限定购买次数';
                        $limitbuy = 1;
                    }
                }
                
                $datas = array(
                    "morethan6"=>$morethan6,
		    "morethanhours"=>$limithours,
                    "limitbuy"=>$limitbuy,
		    "order_limit_day"=>$order_limit_day,
		    "order_limit_hours"=>$order_limit_hours	
                );
                /*
                $datas = array(
                    "morethan6"=>0,
                    "limitbuy"=>0
                );
		*/
                $data = array("status"=>$status,"msg"=>$msg,"data"=>$datas);
                echo json_encode($data);
                exit;
	}
	//服务购买成功后的返回信息
        public function getServeSuccess(){
                $userid = $_REQUEST['user_id']?$_REQUEST['user_id']:0;
		$serve_id = $_REQUEST['serve_id']?$_REQUEST['serve_id']:0;
		$order_id = $_REQUEST['oid']?$_REQUEST['oid']:0;
                if(!$userid){
                    $status = 1002;
                    $msg = '参数错误';
                }
                $status = 1001;
                if($order_id){
			$lastorder = $this->order_model->field('order_createtime')->where('users_id='.$userid." and order_id = '".$order_id."'")->order('order_id desc')->find();
			$order_num = $this->order_model->field('order_num')->where('users_id='.$userid." and order_id = '".$order_id."'")->order('order_id desc')->find();
                }else{
			$lastorder = $this->order_model->field('order_createtime')->where('users_id='.$userid)->order('order_id desc')->find();
			$order_num = $this->order_model->field('order_num')->where('users_id='.$userid." ")->order('order_id desc')->find();
		}
		if($lastorder){
                    
                }
                $limitbuy = 0;
                $serve_id = $_POST['serve_id'];
                if($serve_id){
                    $where['where']['serve_id'] = array('eq',$serve_id);
                    $where['where']['users_id'] = array('eq',$userid);
                    //$where['where']['status'] = array('eq',2);
                    $where['where']['order_createtime'] = array('gt',date('Y-m-d 00:00:00'));
                    //$where['where']['order_createtime'] = array('lt',date('Y-m-d 23:59:59'));
                    $count = $this->order_model->where($where['where'])->count();
                    $serveInfo = $this->serve_model->field('serve_name,serve_count')->where('serve_id='.$serve_id)->find();
                    $today_left = intval($serveInfo['serve_count'])- intval($count);
                }
                
                $datas = array(
                    "today_buy"=>$count,
		    "order_num"=>$order_num["order_num"],
                    "serve_name"=>$serveInfo['serve_name'],
                    "limitbuy"=>$serveInfo['serve_count'],
                    "today_left"=>$today_left
                );
                
                $data = array("status"=>$status,"msg"=>$msg,"data"=>$datas);
                echo json_encode($data);
                exit;
        }
        
        //打赏
        public function tippay(){
            $user_id = $_REQUEST['user_id']?$_REQUEST['user_id']:0;
            $openId = $_REQUEST['usersession'];
            $tipnum = floatval($_REQUEST['tipnum']?$_REQUEST['tipnum']:0);
            $datas = array();
            if(empty($user_id) && empty($openId)){
                $status = 5000;
                $msg = '未认证,请先登录';
            }else{
                if($tipnum){
                        //插入打赏数据,返回打赏id
						//{{{
                        $datas['tipid'] = 0;
						$reward_data = array();
						$reward_data['users_id'] = $user_id;
						$reward_data['reward_createtime'] = date("Y-m-d H:i:s",time());
						$reward_data['reward_money'] = $tipnum;
						$reward_data['status'] = '0';
						
                        $datas['tipid'] = M('reward')->data($reward_data)->add();
						//}}}
						
                        //{{{ 统一下单			
                        $input = new \WxPayUnifiedOrder();
                        $input->SetBody('打赏我们');
                        $input->SetAttach('');
                        $input->SetOut_trade_no(\WxPayConfig::MCHID.date("YmdHis"));
                        $input->SetTotal_fee($tipnum*100);
                        $input->SetTime_start(date("YmdHis"));
                        $input->SetTime_expire(date("YmdHis", time() + 600));
                        $input->SetGoods_tag('');
                        $input->SetNotify_url("https://helizixun.cn/notify.php");
                        $input->SetTrade_type("JSAPI");
                        $input->SetOpenid($openId);

                        $order = \WxPayApi::unifiedOrder($input);
                        //echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
                        //printf_info($order);
                        //}}}
                        $status = 1001;
                        $msg = '执行成功';
                }else{
                    $status = 1002;
                    $msg = '参数错误';
                    $datas = array();
                }
            }
            $data = array("status"=>$status,"msg"=>$msg,"data"=>$datas,"wxorder"=>$order);
            echo json_encode($data);
            exit;
            
        }
        
        
    /*订单支付状态接口*/

    public function payTipOrder(){
        $tid = $_POST['tid'];
        if(IS_POST){
            $data['status'] = 1;
            
			if($reuslt = M('reward')->data($data)->where("reward_id = '$tid'")->save()){
				$status=1003;
				$msg = '操作成功';
			}else{
				$status=1004;
				$msg = '操作失败';
			}
            
            
            
        }else{
            $status = 0;
            $msg = '参数错误';
        }
        $data = array('status'=>$status,'msg'=>$msg);
        //return json_encode($data);
        echo json_encode($data);
        exit;
    }
	//上传文件
	public function uploadfiles(){
		$filetype = $_REQUEST['filetype']?$_REQUEST['filetype']:'image';
		$config=array(
				'rootPath' => './'. C("UPLOADPATH"),
				'savePath' => "$filetype/",
				'maxSize' => 11048576,
				'saveName'   =>    array('uniqid',''),
				'exts'       =>    array('jpg', 'gif', 'png', 'jpeg','silk'),
				'autoSub'    =>    false,
		);
		$url = '';
		//file_put_contents("aaa.log",json_encode($_FILES));
		
			$upload = new \Think\Upload($config);// 
			$info=$upload->upload();
		//file_put_contents("bbb.log",json_encode($info));	
            //开始上传
            if ($info) {
                //上传成功
                //写入附件数据库信息
                $first=array_shift($info);
                if(!empty($first['url'])){
                	$url=$first['url'];
                }else{
                	$url=C("TMPL_PARSE_STRING.__UPLOAD__").$filetype."/".$first['savename'];
                }
                //return $info['savepath'];
				$status = 1001;
				$msg = '上传成功';
				//echo "1," . $url.",".'1,'.$first['name'];
				
            } else {
                //上传失败，返回错误
				$status = 1002;
				$msg = '上传失败';
               
            }
		echo $url;
		exit;
		//$data = array('status'=>$status,'msg'=>$msg,"url"=>$url);
		//$data = array("status"=>$status,"msg"=>$msg,"url"=>$url);
        	//echo json_encode($data);
	}
}
