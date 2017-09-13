<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class VsampleController extends AdminbaseController{
    protected $vsample_model;

    function _initialize() {
        parent::_initialize();
        $this->vsample_model = D("Common/Vsample");
    }

    function index(){
        $vsamples=$this->vsample_model->select();
        $this->assign("vsamples",$vsamples);
        $this->display();
    }

    function add(){
        $this->display();
    }

    function add_post(){

        $img = $this->upload_vsample($_FILES);
        $vsample['createtime'] = $_POST['createtime'];
        $vsample['title'] = $_POST['title'];
        $vsample['vsample_url'] = $img['previewSrc'];
        $vsample['vs_status'] = $_POST['vs_status'];
        if(IS_POST){
            if ($this->vsample_model->create()){
                if ($this->vsample_model->add($vsample)!==false) {
                    $this->success("添加成功！", U("vsample/index"));
                } else {
                    $this->error("添加失败！");
                }
            } else {
                $this->error($this->vsample_model->getError());
            }

        }
    }

    function edit(){
        $id=I("get.id");
        $vsample=$this->vsample_model->where("vsample_id=$id")->find();
        $this->assign($vsample);
        $this->display();
    }

    function edit_post(){
        if(!empty($_FILES['vsample_url']['name'])){
            $img = $this->upload_vsample($_FILES);
            $vsample['vsample_url'] = $img['previewSrc'];
        }
        $vsample['title'] = $_POST['title'];
        $vsample['vs_status'] = $_POST['vs_status'];
        $vsample['vsample_id'] = $_POST['vsample_id'];
        if (IS_POST) {
            if ($this->vsample_model->create()) {
                if ($this->vsample_model->save($vsample)!==false) {
                    $this->success("保存成功！", U("vsample/index"));
                } else {
                    $this->error("保存失败！");
                }
            } else {
                $this->error($this->vsample_model->getError());
            }
        }
    }

    /**
     *  删除
     */
    function delete(){
        $id = I("get.id",0,"intval");
        if ($this->vsample_model->delete($id)!==false) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }



    /*上传语音示例*/
    function upload_vsample($files){
        $file = $files['vsample_url'];
//检查是否为图片
        $ext = $this->getExt($file['name']);
        $arrExt = array('3gp','rmvb','flv','wmv','avi','mkv','mp4','mp3','wav');
        if(!in_array($ext,$arrExt)) {
            $data['sta'] = FALSE;
            $data['msg'] = '不支持此类型文件的上传！';
        }
//设置预览目录
        $previewPath = 'data/upload/vsample/';
        $this->creatDir($previewPath);
        if($file['error'] == 0) {
                //文件上传到预览目录
                $previewName = 'pre_'.md5(mt_rand(1000,9999)).time().'.'.$ext;
                $previewSrc = $previewPath.$previewName;
                if(!move_uploaded_file($file['tmp_name'],$previewSrc)) {
                    $data['sta'] = FALSE;
                    $data['msg'] = '上传失败！';
                } else {
                    $data['previewSrc'] = $previewSrc;
                }


        }
        return $data;
    }
    //获取文件扩展名
    function getExt($filename) {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        return $ext;
    }
//创建目录并赋权限
    function creatDir($path) {
        $arr = explode('/',$path);
        $dirAll = '';
        $result = FALSE;
        if(count($arr) > 0) {
            foreach($arr as $key=>$value) {
                $tmp = trim($value);
                if($tmp != '') {
                    $dirAll .= $tmp.'/';
                    if(!file_exists($dirAll)) {
                        mkdir($dirAll,0777,true);
                    }
                }
            }
        }
    }


}