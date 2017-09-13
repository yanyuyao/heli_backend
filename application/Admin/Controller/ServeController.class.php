<?php
/**
 * Created by PhpStorm.
 * User: 108496
 * Date: 2017/7/25
 * Time: 17:17
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class ServeController extends AdminbaseController{
    protected $serve_model;

    function _initialize() {
        parent::_initialize();
        $this->serve_model = D("Common/Serve");
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
            $serve = $_POST;
            $serve['serve_content']=htmlspecialchars_decode($_POST['serve_content']);
            if ($this->serve_model->create($serve)){
                if ($this->serve_model->add($serve)!==false) {
                    $this->success("添加成功！", U("Serve/index"));
                } else {
                    $this->error("添加失败！");
                }
            } else {
                $this->error($this->serve_model->getError());
            }

        }
    }

    function edit(){
        $id=I("get.id");
        $serve=$this->serve_model->where("serve_id=$id")->find();
        $this->assign($serve);
        $this->display();
    }

    function edit_post(){
        if (IS_POST) {
            $serve = $_POST;
            $serve['serve_content']=htmlspecialchars_decode($_POST['serve_content']);
            if ($this->serve_model->create($serve)) {
                if ($this->serve_model->save($serve)!==false) {
                    $this->success("保存成功！", U("serve/index"));
                } else {
                    $this->error("保存失败！");
                }
            } else {
                $this->error($this->serve_model->getError());
            }
        }
    }

    /**
     *  删除
     */
    function delete(){
        $id = I("get.id",0,"intval");
        if ($this->serve_model->delete($id)!==false) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }

    function toggle(){
        if(isset($_POST['ids']) && $_GET["display"]){
            $ids = implode(",", $_POST['ids']);
            $data['status']=1;
            if ($this->serve_model->where("serve_id in ($ids)")->save($data)!==false) {
                $this->success("显示成功！");
            } else {
                $this->error("显示失败！");
            }
        }
        if(isset($_POST['ids']) && $_GET["hide"]){
            $ids = implode(",", $_POST['ids']);
            $data['status']=0;
            if ($this->serve_model->where("serve_id in ($ids)")->save($data)!==false) {
                $this->success("隐藏成功！");
            } else {
                $this->error("隐藏失败！");
            }
        }
    }

    /*状态改变*/
    function changestatus(){
        $serve['serve_id'] = $_GET['id'];
        $status=$_GET['status']==1?0:1;
        $submit = $_GET['status']==1?'已下架':'已上架';
        $serve['status'] = $status;

            if ($this->serve_model->save($serve)!==false) {
                $this->success($submit, U("serve/index"));
            } else {
                $this->error("失败！");
            }


    }


    /*列表*/
    private  function _lists($status=1){

        $fields=array(
            'serve_name'  => array("field"=>"serve_name","operator"=>"like"),
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

        $count=$this->serve_model->where($where)->count();

        $page = $this->page($count,20);


        //$posts=$this->serve_model->whorder("serve_id DESC")->select();

        $posts=$this->serve_model
            ->where($where)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->order("serve_id DESC")->select();

        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page",$page->GetCurrentPage());
        unset($_GET[C('VAR_URL_PARAMS')]);
        $this->assign("formget",$_GET);
        $this->assign("serves",$posts);
    }





}
