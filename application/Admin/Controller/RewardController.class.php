<?php
/**
 * Created by PhpStorm.
 * User: 108496
 * Date: 2017/7/26
 * Time: 16:09
 */
/**
 * Created by PhpStorm.
 * User: 108496
 * Date: 2017/7/25
 * Time: 17:17
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class RewardController extends AdminbaseController{
    protected $reward_model;

    function _initialize() {
        parent::_initialize();
        $this->reward_model = D("Common/reward");
    }

    function index(){
        $this->_lists();
        $this->display();
    }

    function add(){
        $this->display();
    }

    function add_post(){
        if(IS_POST){
            $reward = $_POST;
            $reward['reward_content']=htmlspecialchars_decode($_POST['reward_content']);
            if ($this->reward_model->create($reward)){
                if ($this->reward_model->add($reward)!==false) {
                    $this->success("添加成功！", U("reward/index"));
                } else {
                    $this->error("添加失败！");
                }
            } else {
                $this->error($this->reward_model->getError());
            }

        }
    }

    function edit(){
        $id=I("get.id");
        $reward=$this->reward_model->where("reward_id=$id")->find();
        $this->assign($reward);
        $this->display();
    }

    function edit_post(){
        if (IS_POST) {
            $reward = $_POST;
            $reward['reward_content']=htmlspecialchars_decode($_POST['reward_content']);
            if ($this->reward_model->create($reward)) {
                if ($this->reward_model->save($reward)!==false) {
                    $this->success("保存成功！", U("reward/index"));
                } else {
                    $this->error("保存失败！");
                }
            } else {
                $this->error($this->reward_model->getError());
            }
        }
    }

    /**
     *  删除
     */
    function delete(){
        $id = I("get.id",0,"intval");
        if ($this->reward_model->delete($id)!==false) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }

    /*列表*/
    private  function _lists($status=1){

        $fields=array(
            'start_time'=> array("field"=>"reward_createtime","operator"=>">"),
            'end_time'  => array("field"=>"reward_createtime","operator"=>"<"),
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

        $count=$this->reward_model->where($where)->count();

        $page = $this->page($count,20);


        $posts=$this->reward_model->field('a.*,b.openId,b.avatar,b.user_nicename')->alias("a")->join(C ( 'DB_PREFIX' )."users b ON a.users_id = b.id")->where($where)->limit($page->firstRow . ',' . $page->listRows)->order("a.reward_id desc")->select();
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page",$page->GetCurrentPage());
        unset($_GET[C('VAR_URL_PARAMS')]);
        $this->assign("formget",$_GET);
        $this->assign("rewards",$posts);
    }


}
