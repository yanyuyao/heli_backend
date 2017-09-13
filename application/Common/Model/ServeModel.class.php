<?php
/**
 * Created by PhpStorm.
 * User: 108496
 * Date: 2017/7/25
 * Time: 17:21
 */
namespace Common\Model;
use Common\Model\CommonModel;
class ServeModel extends CommonModel{
    //自动验证
    protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('serve_name', 'require', '服务名称不能为空！', 1, 'regex', 3),
        array('serve_content', 'require', '服务内容不能为空！', 1, 'regex', 3),
        array('serve_title', 'require', '服务简介不能为空！', 1, 'regex', 3),
        array('serve_price', 'require', '价格不能为空！', 1, 'regex', 3),
        array('serve_count', 'require', '每天购买次数不能为空！', 1, 'regex', 3),
        array('serve_url', 'require', '必须上传服务图片！', 1, 'regex', 3),
    );

    protected function _before_write(&$data) {
        parent::_before_write($data);
    }
}