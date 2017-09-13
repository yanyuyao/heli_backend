<?php
/**
 * Created by PhpStorm.
 * User: 108496
 * Date: 2017/7/27
 * Time: 16:11
 */
/**
 * Created by PhpStorm.
 * User: 108496
 * Date: 2017/7/25
 * Time: 17:17
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class AddusController extends AdminbaseController{
    protected $addus_model;

    function _initialize() {
        parent::_initialize();
        $this->addus_model = D("Common/Addus");
    }

    function index(){
        $this->_lists();
        $this->display();
    }

    function edit(){
        $id=I("get.id");
        $addus=$this->addus_model->where("addus_id=$id")->find();
        $this->assign($addus);
        $this->display();
    }

    function edit_post(){
        if (IS_POST) {
            if ($this->addus_model->create()) {
                if ($this->addus_model->save()!==false) {
                    $this->success("保存成功！", U("addus/index"));
                } else {
                    $this->error("保存失败！");
                }
            } else {
                $this->error($this->addus_model->getError());
            }
        }
    }

    /**
     *  删除
     */
    function delete(){
        $id = I("get.id",0,"intval");
        if ($this->addus_model->delete($id)!==false) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }


    /*列表*/
    private  function _lists($status=1){

        $fields=array(
            'addus_name'  => array("field"=>"addus_name","operator"=>"like"),
            'addus_tel'  => array("field"=>"addus_tel","operator"=>"like"),
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

        $count=$this->addus_model->where($where)->count();

        $page = $this->page($count,20);


        //$posts=$this->addus_model->whorder("addus_id DESC")->select();

        $posts=$this->addus_model
            ->where($where)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->order("addus_id DESC")->select();

        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page",$page->GetCurrentPage());
        unset($_GET[C('VAR_URL_PARAMS')]);
        $this->assign("formget",$_GET);
        $this->assign("addus",$posts);
    }





}
