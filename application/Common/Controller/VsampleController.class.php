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
        if(IS_POST){
            if ($this->vsample_model->create()){
                if ($this->vsample_model->add()!==false) {
                    $this->success("��ӳɹ���", U("vsample/index"));
                } else {
                    $this->error("���ʧ�ܣ�");
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
        if (IS_POST) {
            if ($this->vsample_model->create()) {
                if ($this->vsample_model->save()!==false) {
                    $this->success("����ɹ���", U("vsample/index"));
                } else {
                    $this->error("����ʧ�ܣ�");
                }
            } else {
                $this->error($this->vsample_model->getError());
            }
        }
    }

    /**
     *  ɾ��
     */
    function delete(){
        $id = I("get.id",0,"intval");
        if ($this->vsample_model->delete($id)!==false) {
            $this->success("ɾ���ɹ���");
        } else {
            $this->error("ɾ��ʧ�ܣ�");
        }
    }



    /*�ϴ�����ʾ��*/
    function upload_vsample(){
        $file = $_FILES['files'];
//formData�������Ĳ���param1��param2
        $param1 = $_POST['param1'];
        $param2 = $_POST['param2'];
//ajax��������
        $data = array('sta'=>TRUE,'msg'=>'�ϴ��ɹ���');
//����Ƿ�ΪͼƬ
        $ext = getExt($file['name']);
        $arrExt = array('3gp','rmvb','flv','wmv','avi','mkv','mp4','mp3','wav');
        if(!in_array($ext,$arrExt)) {
            $data['sta'] = FALSE;
            $data['msg'] = '��֧�ִ������ļ����ϴ���';
        }
//����Ԥ��Ŀ¼
        $previewPath = 'data/upload/vsample/';
        creatDir($previewPath);

        if($file['error'] == 0) {
            if(isset($param1) && isset($param2)) {
                //��Ҫ�õ�$param1��$param2��һЩ��������...

                //�ļ��ϴ���Ԥ��Ŀ¼
                $previewName = 'pre_'.md5(mt_rand(1000,9999)).time().'.'.$ext;
                $previewSrc = $previewPath.$previewName;
                if(!move_uploaded_file($file['tmp_name'],$previewSrc)) {
                    $data['sta'] = FALSE;
                    $data['msg'] = '�ϴ�ʧ�ܣ�';
                } else {
                    $data['previewSrc'] = $previewSrc;
                }

            }
        }
        echo json_encode($data);
    }
    //��ȡ�ļ���չ��
    function getExt($filename) {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        return $ext;
    }
//����Ŀ¼����Ȩ��
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